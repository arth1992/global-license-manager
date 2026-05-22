<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\ActivationRequest;
use App\Services\CryptoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LicenseApiController extends Controller
{
    public function __construct(
        private readonly CryptoService $cryptoService
    ) {}

    /**
     * POST /api/v1/handshake
     * Register a pending activation handshake from the client installer.
     */
    public function handshake(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_email' => 'required|email',
            'fingerprint'  => 'required|string|max:512',
            'domain'       => 'required|string|max:255',
        ]);

        // Find the license associated with this email
        $license = License::where('client_email', $validated['client_email'])->first();

        if (! $license) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No license found associated with this email address.',
            ], 404);
        }

        if (in_array($license->status, ['suspended', 'revoked'])) {
            return response()->json([
                'status'  => 'error',
                'message' => "This license is currently {$license->status}.",
            ], 403);
        }

        // Generate a human-readable request_id (format: GAM-REQ-XXXX)
        // Ensure uniqueness
        do {
            $requestId = 'GAM-REQ-' . Str::upper(Str::random(4));
        } while (ActivationRequest::where('request_id', $requestId)->exists());

        // Create the activation request
        $activationRequest = ActivationRequest::create([
            'request_id'  => $requestId,
            'license_id'  => $license->id,
            'fingerprint' => $validated['fingerprint'],
            'domain'      => $validated['domain'],
            'ip_address'  => $request->ip() ?? '127.0.0.1',
            'status'      => 'pending',
            'expires_at'  => Carbon::now()->addHours(48),
        ]);

        // Log the event
        $license->log(
            event: 'created',
            ip: $request->ip(),
            fingerprint: $validated['fingerprint'],
            success: true,
            notes: "Handshake requested. Generated Request ID: {$requestId}"
        );

        return response()->json([
            'status'     => 'success',
            'request_id' => $requestId,
            'expires_at' => $activationRequest->expires_at->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v1/verify
     * Handle the daily license sync ping from an active GAM client.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
            'fingerprint' => 'required|string|max:512',
        ]);

        // Verify key signature using the CryptoService
        $payload = $this->cryptoService->verifyAndDecode($validated['license_key']);

        if (! $payload) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cryptographic signature is invalid or tampered.',
            ], 422);
        }

        if (! isset($payload['uuid'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Malformed license key payload.',
            ], 422);
        }

        // Look up the license
        $license = License::where('uuid', $payload['uuid'])->first();

        if (! $license) {
            return response()->json([
                'status'  => 'error',
                'message' => 'License record not found in the database.',
            ], 404);
        }

        // Handle checks
        $ip = $request->ip();

        if (in_array($license->status, ['suspended', 'revoked'])) {
            $license->log(
                event: 'daily_ping',
                ip: $ip,
                fingerprint: $validated['fingerprint'],
                success: false,
                notes: "Ping rejected: License is {$license->status}."
            );

            return response()->json([
                'status'  => $license->status,
                'message' => "Your license has been {$license->status}.",
            ], 403);
        }

        if ($license->isExpired()) {
            $license->log(
                event: 'daily_ping',
                ip: $ip,
                fingerprint: $validated['fingerprint'],
                success: false,
                notes: 'Ping rejected: License has expired.'
            );

            return response()->json([
                'status'  => 'expired',
                'message' => 'Your license key has expired.',
            ], 403);
        }

        // Validate fingerprint binding
        if ($license->fingerprint && $license->fingerprint !== $validated['fingerprint']) {
            $license->log(
                event: 'daily_ping',
                ip: $ip,
                fingerprint: $validated['fingerprint'],
                success: false,
                notes: "Ping rejected: Fingerprint mismatch. Stored: {$license->fingerprint}, Incoming: {$validated['fingerprint']}"
            );

            return response()->json([
                'status'  => 'revoked',
                'message' => 'License hardware fingerprint mismatch. Dual-activation is prohibited.',
            ], 403);
        }

        // If the license is active but not bound to a fingerprint yet, bind it now!
        if (empty($license->fingerprint)) {
            $license->update([
                'fingerprint'  => $validated['fingerprint'],
                'activated_at' => Carbon::now(),
                'status'       => 'active',
            ]);

            $license->log(
                event: 'activated',
                ip: $ip,
                fingerprint: $validated['fingerprint'],
                success: true,
                notes: 'Hardware fingerprint automatically bound on verify request.'
            );
        } else {
            // Log successful ping
            $license->log(
                event: 'daily_ping',
                ip: $ip,
                fingerprint: $validated['fingerprint'],
                success: true,
                notes: 'License daily verification ping successful.'
            );
        }

        return response()->json([
            'status'     => 'active',
            'client'     => $license->client_name,
            'expires_at' => $license->expires_at->toIso8601String(),
            'features'   => $license->features,
        ]);
    }
}
