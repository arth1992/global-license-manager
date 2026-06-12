<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Release;
use App\Services\CryptoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UpdateApiController extends Controller
{
    public function __construct(
        private readonly CryptoService $cryptoService
    ) {}

    /**
     * POST /api/v1/update/check
     * Check if there is a newer release package available for the authenticated client.
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key'     => 'required|string',
            'fingerprint'     => 'required|string|max:512',
            'current_version' => 'required|string|max:50',
        ]);

        // 1. Verify license key signature
        $payload = $this->cryptoService->verifyAndDecode($validated['license_key']);
        if (! $payload || ! isset($payload['uuid'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cryptographic signature is invalid or tampered.',
            ], 422);
        }

        // 2. Look up license record
        $license = License::where('uuid', $payload['uuid'])->first();
        if (! $license) {
            return response()->json([
                'status'  => 'error',
                'message' => 'License record not found.',
            ], 404);
        }

        // 3. Enforce license status checks (active only)
        if (in_array($license->status, ['suspended', 'revoked'])) {
            return response()->json([
                'status'  => $license->status,
                'message' => "Your license has been {$license->status}.",
            ], 403);
        }

        if ($license->isExpired()) {
            return response()->json([
                'status'  => 'expired',
                'message' => 'Your license key has expired.',
            ], 403);
        }

        // 4. Validate fingerprint binding
        if ($license->fingerprint && $license->fingerprint !== $validated['fingerprint']) {
            return response()->json([
                'status'  => 'revoked',
                'message' => 'License hardware fingerprint mismatch.',
            ], 403);
        }

        // 5. Query the database for the latest release version
        $releases = Release::orderBy('version', 'desc')->get();
        $latestRelease = null;

        foreach ($releases as $release) {
            if (version_compare($release->version, $validated['current_version'], '>')) {
                if ($latestRelease === null || version_compare($release->version, $latestRelease->version, '>')) {
                    $latestRelease = $release;
                }
            }
        }

        if ($latestRelease) {
            return response()->json([
                'status'           => 'success',
                'update_available' => true,
                'latest_version'   => $latestRelease->version,
                'changelog'        => $latestRelease->changelog,
                'size'             => $latestRelease->size,
                'zip_url'          => url('storage/' . $latestRelease->zip_path),
                'signature'        => $latestRelease->signature,
            ]);
        }

        return response()->json([
            'status'           => 'success',
            'update_available' => false,
            'message'          => 'You are running the latest version.',
        ]);
    }
}
