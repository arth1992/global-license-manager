<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\License;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BillingApiController extends Controller
{
    private function authenticateLicense(Request $request)
    {
        $domain = $request->input('domain');
        $fingerprint = $request->input('fingerprint');

        return License::where('domain', $domain)
            ->where('fingerprint', $fingerprint)
            ->first();
    }

    public function syncUsage(Request $request, InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'domain' => 'required|string',
            'fingerprint' => 'required|string',
            'active_applicant_count' => 'required|integer|min:0',
            'school_breakdown' => 'nullable|array',
            'sync_month' => 'required|integer',
            'sync_year' => 'required|integer',
        ]);

        $license = $this->authenticateLicense($request);

        if (!$license) {
            return response()->json(['message' => 'Unauthorized or license not found.'], 401);
        }

        try {
            $invoice = $invoiceService->generateInvoice(
                $license,
                $validated['active_applicant_count'],
                $validated['sync_month'],
                $validated['sync_year'],
                $validated['school_breakdown'] ?? null
            );

            \App\Models\BillingLog::create([
                'license_id' => $license->id,
                'status' => 'success',
                'notes' => "Auto-synced {$validated['active_applicant_count']} applicants",
                'sync_month' => $validated['sync_month'],
                'sync_year' => $validated['sync_year'],
            ]);

            return response()->json([
                'message' => 'Billing usage synced and invoice generated.',
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            \App\Models\BillingLog::create([
                'license_id' => $license->id,
                'status' => 'failed',
                'notes' => "Auto-sync failed: " . $e->getMessage(),
                'sync_month' => $validated['sync_month'],
                'sync_year' => $validated['sync_year'],
            ]);

            Log::error('Billing Sync Error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error while syncing billing.'], 500);
        }
    }

    public function getInvoices(Request $request, InvoiceService $invoiceService)
    {
        $license = $this->authenticateLicense($request);

        if (!$license) {
            return response()->json(['message' => 'Unauthorized or license not found.'], 401);
        }

        $invoices = Invoice::where('license_id', $license->id)
            ->latest()
            ->paginate(15);

        // Map relative paths to absolute URLs
        $invoices->getCollection()->transform(function ($invoice) {
            $invoice->pdf_url = $invoice->pdf_path ? asset('storage/' . $invoice->pdf_path) : null;
            $invoice->receipt_pdf_url = $invoice->receipt_pdf_path ? asset('storage/' . $invoice->receipt_pdf_path) : null;
            return $invoice;
        });

        // Current active applicants (if we were to estimate right now)
        // Since we don't have real-time applicant count from the DB (it's in the tenant DB),
        // we will just show the base fee + 0 applicants until the sync happens, 
        // OR we just show what the base fee is.
        $estimatedBill = [
            'base_fee' => InvoiceService::BASE_FEE,
            'applicant_count' => 'Calculated at end of month',
            'applicant_fee' => 0,
            'subtotal' => InvoiceService::BASE_FEE,
            'discount' => 0,
            'total' => InvoiceService::BASE_FEE,
        ];

        return response()->json([
            'invoices' => $invoices,
            'estimated_bill' => $estimatedBill,
            'razorpay_key' => \App\Models\SystemSetting::getActive()->razorpay_key_id, // Setup Razorpay centrally
            'is_billing_waived' => $license->is_billing_waived,
        ]);
    }

    public function payRazorpay(Request $request, Invoice $invoice, InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'domain' => 'required|string',
            'fingerprint' => 'required|string',
            'razorpay_payment_id' => 'required|string',
        ]);

        $license = $this->authenticateLicense($request);

        if (!$license || $invoice->license_id !== $license->id) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ($invoice->status === 'Paid') {
            return response()->json(['message' => 'Invoice is already paid.'], 400);
        }

        try {
            $invoiceService->generateReceipt($invoice, $validated['razorpay_payment_id']);
            return response()->json(['message' => 'Payment successful.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error generating receipt: ' . $e->getMessage()], 500);
        }
    }
}
