<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\License;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService
{
    public const BASE_FEE = 2000.00; // INR
    public const FEE_PER_APPLICANT = 200.00; // INR

    public function generateInvoice(License $license, int $activeApplicantCount, int $month, int $year, ?array $schoolBreakdown = null)
    {
        // Check if an invoice for this month/year already exists for this license
        $existingInvoice = Invoice::where('license_id', $license->id)
            ->where('invoice_month', $month)
            ->where('invoice_year', $year)
            ->first();

        if ($existingInvoice) {
            return $existingInvoice;
        }

        $baseFee = self::BASE_FEE;
        $applicantFee = $activeApplicantCount * self::FEE_PER_APPLICANT;
        $subtotal = $baseFee + $applicantFee;
        $discountAmount = 0;
        $status = 'Unpaid';

        if ($license->is_billing_waived) {
            $discountAmount = $subtotal;
            $status = 'Waived';
        } elseif ($license->billing_discount_type === 'fixed') {
            $discountAmount = min($license->billing_discount_amount ?? 0, $subtotal);
        } elseif ($license->billing_discount_type === 'percentage') {
            $discountPercent = min($license->billing_discount_amount ?? 0, 100);
            $discountAmount = $subtotal * ($discountPercent / 100);
        }

        $totalAmount = max(0, $subtotal - $discountAmount);
        
        if ($totalAmount == 0 && !$license->is_billing_waived) {
            $status = 'Paid'; // auto-paid if discount covers everything
        }

        $invoiceNumber = 'INV-' . strtoupper(Str::random(8)) . '-' . $month . $year;

        $invoice = Invoice::create([
            'license_id' => $license->id,
            'invoice_number' => $invoiceNumber,
            'invoice_month' => $month,
            'invoice_year' => $year,
            'base_fee' => $baseFee,
            'applicant_count' => $activeApplicantCount,
            'school_breakdown' => $schoolBreakdown,
            'applicant_fee' => $applicantFee,
            'discount_applied' => $discountAmount,
            'total_amount' => $totalAmount,
            'status' => $status,
        ]);

        $this->generateInvoicePdf($invoice);

        return $invoice;
    }

    public function generateInvoicePdf(Invoice $invoice)
    {
        $license = $invoice->license;
        $settings = \App\Models\SystemSetting::getActive();
        
        $data = [
            'invoice' => $invoice,
            'license' => $license,
            'date' => now()->format('F j, Y'),
            'dueDate' => now()->addDays(15)->format('F j, Y'),
            'bankDetails' => $settings->bank_details,
        ];

        // We assume pdf/invoice.blade.php exists
        $pdf = Pdf::loadView('pdf.invoice', $data);
        $fileName = "invoices/{$invoice->invoice_number}.pdf";
        
        Storage::disk('public')->put($fileName, $pdf->output());
        
        $invoice->update(['pdf_path' => $fileName]);
        
        return $fileName;
    }

    public function generateReceipt(Invoice $invoice, string $transactionId)
    {
        $invoice->update([
            'status' => 'Paid',
            'transaction_id' => $transactionId,
        ]);

        $settings = \App\Models\SystemSetting::getActive();

        $data = [
            'invoice' => $invoice,
            'license' => $invoice->license,
            'date' => now()->format('F j, Y'),
            'bankDetails' => $settings->bank_details,
        ];

        // We assume pdf/receipt.blade.php exists
        $pdf = Pdf::loadView('pdf.receipt', $data);
        $fileName = "receipts/{$invoice->invoice_number}_receipt.pdf";
        
        Storage::disk('public')->put($fileName, $pdf->output());
        
        $invoice->update(['receipt_pdf_path' => $fileName]);

        return $fileName;
    }
}
