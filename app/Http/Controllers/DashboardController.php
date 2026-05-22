<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\LicenseLog;
use App\Services\CryptoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private readonly CryptoService $cryptoService
    ) {}

    /**
     * Display the dashboard overview page (stats + recent logs).
     */
    public function index(Request $request): Response
    {
        $now = Carbon::now();

        // Calculate statistics
        $totalLicenses = License::count();
        
        $activeLicenses = License::where('status', 'active')
            ->where('expires_at', '>', $now)
            ->count();
            
        $suspendedLicenses = License::where('status', 'suspended')->count();
        
        $expiringSoon = License::where('status', 'active')
            ->where('expires_at', '>', $now)
            ->where('expires_at', '<=', $now->copy()->addDays(30))
            ->count();

        // Get recent activity logs with associated client names
        $recentLogs = LicenseLog::with('license')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id'          => $log->id,
                    'client_name' => $log->license->client_name ?? 'Unknown',
                    'uuid'        => $log->license->uuid ?? '',
                    'event'       => $log->event,
                    'ip_address'  => $log->ip_address,
                    'fingerprint' => $log->fingerprint ? substr($log->fingerprint, 0, 12) . '...' : null,
                    'is_success'  => $log->is_success,
                    'notes'       => $log->notes,
                    'created_at'  => $log->created_at ? $log->created_at->diffForHumans() : 'Just now',
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'total'     => $totalLicenses,
                'active'    => $activeLicenses,
                'suspended' => $suspendedLicenses,
                'expiring'  => $expiringSoon,
            ],
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * Display the searchable, filterable license list page.
     */
    public function list(Request $request): Response
    {
        $query = License::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%")
                  ->orWhere('uuid', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $licenses = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($license) {
                return [
                    'uuid'         => $license->uuid,
                    'client_name'  => $license->client_name,
                    'client_email' => $license->client_email,
                    'max_tenants'  => $license->max_tenants,
                    'status'       => $license->status,
                    'expires_at'   => $license->expires_at ? $license->expires_at->format('Y-m-d H:i') : null,
                    'is_expired'   => $license->isExpired(),
                ];
            });

        return Inertia::render('Licenses/Index', [
            'licenses' => $licenses,
            'filters'  => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Display a specific license's details with activations and audit logs.
     */
    public function show(License $license): Response
    {
        $license->load([
            'activationRequests' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'logs' => function ($q) {
                $q->orderBy('id', 'desc');
            }
        ]);

        $formattedLicense = [
            'uuid'         => $license->uuid,
            'client_name'  => $license->client_name,
            'client_email' => $license->client_email,
            'max_tenants'  => $license->max_tenants,
            'features'     => $license->features ?? [],
            'status'       => $license->status,
            'fingerprint'  => $license->fingerprint,
            'domain'       => $license->domain,
            'license_key'  => $license->license_key,
            'expires_at'   => $license->expires_at ? $license->expires_at->format('Y-m-d H:i') : null,
            'activated_at' => $license->activated_at ? $license->activated_at->format('Y-m-d H:i') : null,
            'is_expired'   => $license->isExpired(),
        ];

        $activations = $license->activationRequests->map(function ($req) {
            return [
                'id'          => $req->id,
                'request_id'  => $req->request_id,
                'fingerprint' => $req->fingerprint,
                'domain'      => $req->domain,
                'ip_address'  => $req->ip_address,
                'status'      => $req->status,
                'expires_at'  => $req->expires_at ? $req->expires_at->format('Y-m-d H:i') : null,
                'created_at'  => $req->created_at ? $req->created_at->format('Y-m-d H:i') : null,
            ];
        });

        $logs = $license->logs->map(function ($log) {
            return [
                'id'          => $log->id,
                'event'       => $log->event,
                'ip_address'  => $log->ip_address,
                'fingerprint' => $log->fingerprint ? substr($log->fingerprint, 0, 16) . '...' : null,
                'is_success'  => $log->is_success,
                'notes'       => $log->notes,
                'created_at'  => $log->created_at ? $log->created_at->format('Y-m-d H:i') : 'Just now',
            ];
        });

        return Inertia::render('Licenses/Show', [
            'license'     => $formattedLicense,
            'activations' => $activations,
            'logs'        => $logs,
        ]);
    }

    /**
     * Create and store a new client license.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name'  => 'required|string|max:255',
            'client_email' => 'required|email|unique:licenses,client_email',
            'max_tenants'  => 'required|integer|min:1',
            'expires_at'   => 'required|date|after:today',
            'features'     => 'nullable|array',
        ]);

        $license = License::create([
            'client_name'  => $validated['client_name'],
            'client_email' => $validated['client_email'],
            'max_tenants'  => $validated['max_tenants'],
            'expires_at'   => Carbon::parse($validated['expires_at']),
            'features'     => $validated['features'] ?? [],
            'status'       => 'pending',
        ]);

        $license->log(
            event: 'created',
            ip: $request->ip(),
            fingerprint: null,
            success: true,
            notes: 'License created via administrator dashboard.'
        );

        return redirect()->route('licenses.show', $license->uuid)
            ->with('success', 'License record created successfully.');
    }

    /**
     * Generate or re-generate the cryptographic signed License Key.
     */
    public function generateKey(License $license, Request $request): RedirectResponse
    {
        $payload = [
            'uuid'         => $license->uuid,
            'client_name'  => $license->client_name,
            'client_email' => $license->client_email,
            'max_tenants'  => $license->max_tenants,
            'expires_at'   => $license->expires_at->toISOString(),
        ];

        try {
            $licenseKey = $this->cryptoService->signLicensePayload($payload);
            
            $license->update([
                'license_key' => $licenseKey,
            ]);

            $license->log(
                event: 'key_generated',
                ip: $request->ip(),
                fingerprint: null,
                success: true,
                notes: 'Cryptographic license key payload signed and generated.'
            );

            return redirect()->back()
                ->with('success', 'Cryptographic license key generated successfully.');
        } catch (\Exception $e) {
            $license->log(
                event: 'key_generated',
                ip: $request->ip(),
                fingerprint: null,
                success: false,
                notes: 'Failed to generate key: ' . $e->getMessage()
            );

            return redirect()->back()
                ->with('error', 'Error generating cryptographic license key: ' . $e->getMessage());
        }
    }

    /**
     * Update the status of a license (e.g. suspend or revoke it).
     */
    public function updateStatus(License $license, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,active,suspended,revoked',
        ]);

        $oldStatus = $license->status;
        $newStatus = $validated['status'];

        $license->update([
            'status' => $newStatus,
        ]);

        $eventMap = [
            'active'    => 'reactivated',
            'suspended' => 'suspended',
            'revoked'   => 'revoked',
            'pending'   => 'reactivated',
        ];
        $event = $eventMap[$newStatus] ?? 'status_updated';

        $license->log(
            event: $event,
            ip: $request->ip(),
            fingerprint: null,
            success: true,
            notes: "Status updated from '{$oldStatus}' to '{$newStatus}' by administrator."
        );

        return redirect()->back()
            ->with('success', "License status updated to '{$newStatus}' successfully.");
    }
}
