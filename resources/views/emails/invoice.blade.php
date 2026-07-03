<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Invoice Generated</h2>
    <p>Dear {{ $invoice->license->client_name }},</p>
    <p>Your invoice for the period of {{ str_pad($invoice->invoice_month, 2, '0', STR_PAD_LEFT) }}/{{ $invoice->invoice_year }} has been generated.</p>
    
    <p><strong>Invoice Details:</strong></p>
    <ul>
        <li><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</li>
        <li><strong>Total Amount:</strong> ₹{{ number_format($invoice->total_amount, 2) }}</li>
        <li><strong>Status:</strong> {{ $invoice->status }}</li>
    </ul>

    <p>Please find the invoice PDF attached to this email.</p>
    
    @if($invoice->status !== 'Paid' && $invoice->status !== 'Waived')
    <p>Please arrange for the payment at your earliest convenience.</p>
    @endif

    <br>
    <p>Best regards,<br>
    Global Admissions Manager Team</p>
</body>
</html>
