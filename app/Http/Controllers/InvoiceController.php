<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\License;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('license')->latest()->paginate(15);
        $totalRevenue = Invoice::where('status', 'Paid')->sum('total_amount');
        $totalUnpaid = Invoice::where('status', 'Unpaid')->sum('total_amount');

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'metrics' => [
                'revenue' => $totalRevenue,
                'unpaid' => $totalUnpaid,
            ]
        ]);
    }

    public function markPaid(Request $request, Invoice $invoice, InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string|max:255',
        ]);

        $invoiceService->generateReceipt($invoice, $validated['transaction_id']);

        return back()->with('success', 'Invoice manually marked as paid and receipt generated.');
    }

    public function updateBilling(Request $request, License $license)
    {
        $validated = $request->validate([
            'billing_discount_type' => 'nullable|string|in:fixed,percentage',
            'billing_discount_amount' => 'nullable|numeric|min:0',
            'is_billing_waived' => 'boolean',
            'base_fee' => 'nullable|numeric|min:0',
            'per_applicant_fee' => 'nullable|numeric|min:0',
        ]);

        $license->update([
            'billing_discount_type' => $validated['billing_discount_type'] ?? null,
            'billing_discount_amount' => $validated['billing_discount_amount'] ?? null,
            'is_billing_waived' => $validated['is_billing_waived'] ?? false,
            'base_fee' => $validated['base_fee'] ?? null,
            'per_applicant_fee' => $validated['per_applicant_fee'] ?? null,
        ]);

        return back()->with('success', 'Billing settings updated successfully.');
    }

    public function manualGenerate(Request $request, License $license, InvoiceService $invoiceService)
    {
        try {
            $month = now()->subMonth()->month;
            $year = now()->subMonth()->year;

            // Generate invoice with 0 active applicants (Base fee only)
            $invoiceService->generateInvoice($license, 0, $month, $year);

            \App\Models\BillingLog::create([
                'license_id' => $license->id,
                'status' => 'manual',
                'notes' => 'Manually generated base fee invoice.',
                'sync_month' => $month,
                'sync_year' => $year,
            ]);

            return back()->with('success', 'Base fee invoice generated successfully.');
        } catch (\Exception $e) {
            \App\Models\BillingLog::create([
                'license_id' => $license->id,
                'status' => 'failed',
                'notes' => 'Manual generation failed: ' . $e->getMessage(),
                'sync_month' => now()->subMonth()->month,
                'sync_year' => now()->subMonth()->year,
            ]);

            return back()->with('error', 'Failed to generate invoice: ' . $e->getMessage());
        }
    }
}
