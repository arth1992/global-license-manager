<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.5; font-size: 13px; }
        .header { margin-bottom: 25px; border-bottom: 2px solid {{ $settings->brand_color ?? '#059669' }}; padding-bottom: 20px; display: table; width: 100%; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; text-align: right; vertical-align: top; }
        .header h1 { margin: 0; color: {{ $settings->brand_color ?? '#059669' }}; font-size: 24px; text-transform: uppercase; }
        .meta-info { margin-bottom: 30px; display: table; width: 100%; }
        .meta-info .col { display: table-cell; width: 50%; vertical-align: top; }
        .meta-info .col h3 { margin-top: 0; color: #475569; font-size: 13px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 8px; width: 90%; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th, .table td { padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .table th { background-color: {{ $settings->brand_color ?? '#f8fafc' }}; color: {{ $settings->brand_color ? '#ffffff' : '#475569' }}; font-weight: bold; text-transform: uppercase; font-size: 11px; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .summary { width: 50%; float: right; margin-bottom: 30px; }
        .summary-row { display: table; width: 100%; margin-bottom: 6px; }
        .summary-label { display: table-cell; text-align: left; font-weight: bold; color: #475569; }
        .summary-value { display: table-cell; text-align: right; }
        .total-row { font-size: 16px; color: #0f172a; border-top: 2px solid #e2e8f0; padding-top: 8px; font-weight: bold; }
        .footer { clear: both; margin-top: 40px; text-align: center; color: #64748b; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if(isset($settings->logo_url) && $settings->logo_url)
                <img src="{{ storage_path('app/public/' . $settings->logo_url) }}" alt="Logo" style="max-height: 60px; margin-bottom: 10px;">
            @else
                <h1>IntakeX</h1>
            @endif
            <div style="font-size: 11px; color: #475569; margin-top: 5px;">
                <strong>Supplier:</strong> {{ $settings->smtp_from_name ?: 'IntakeX' }}<br>
                {!! nl2br(e($settings->company_address ?: "123 Tech Park, Indiranagar\nBangalore, Karnataka - 560038")) !!}<br>
                <strong>GSTIN:</strong> {{ $settings->gstin ?: '29AAAAA1111A1Z1' }} | 
                <strong>State:</strong> {{ $settings->state_name ?: 'Karnataka' }} (Code: {{ $settings->state_code ?: '29' }})
            </div>
        </div>
        <div class="header-right">
            <h2 style="margin: 0; color: {{ $settings->brand_color ?? '#059669' }}; font-size: 20px; text-transform: uppercase;">PAYMENT RECEIPT</h2>
            <div style="margin-top: 15px; font-size: 12px; line-height: 1.6;">
                <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Date Paid:</strong> {{ $date }}<br>
                <strong>Transaction ID:</strong> {{ $invoice->transaction_id ?? 'N/A' }}
            </div>
        </div>
    </div>

    <div class="meta-info">
        <div class="col">
            <h3>Billed To (Recipient):</h3>
            <strong>{{ $license->client_name }}</strong><br>
            @if($license->billing_address)
                {!! nl2br(e($license->billing_address)) !!}<br>
            @else
                {{ $license->client_email }}<br>
            @endif
            <strong>GSTIN:</strong> {{ $license->gstin ?: 'URP (Unregistered Person)' }}<br>
            <strong>State:</strong> {{ $license->state_name ?: 'N/A' }} @if($license->state_code)(Code: {{ $license->state_code }})@endif
        </div>
        <div class="col" style="padding-left: 20px;">
            <h3>Place of Supply:</h3>
            <strong>State:</strong> {{ $license->state_name ?: 'N/A' }} @if($license->state_code)(Code: {{ $license->state_code }})@endif<br>
            <strong>Domain Locked:</strong> {{ $license->domain ?: 'N/A' }}<br>
            <strong>License Key:</strong> {{ substr($license->license_key, 0, 15) }}...
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 55%;">Description</th>
                <th class="text-center" style="width: 15%;">SAC Code</th>
                <th class="text-right" style="width: 30%;">Taxable Value (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Payment for platform fees for Invoice {{ $invoice->invoice_number }}</td>
                <td class="text-center">997331</td>
                <td class="text-right">Rs. {{ number_format($invoice->taxable_amount ?: ($invoice->total_amount / (1 + ($invoice->gst_rate ?? 18) / 100)), 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-label">Taxable Value:</div>
            <div class="summary-value">Rs. {{ number_format($invoice->taxable_amount ?: ($invoice->total_amount / (1 + ($invoice->gst_rate ?? 18) / 100)), 2) }}</div>
        </div>

        @if(($invoice->cgst_amount ?? 0) > 0)
        <div class="summary-row">
            <div class="summary-label">CGST ({{ number_format(($invoice->gst_rate ?? 18) / 2, 1) }}%):</div>
            <div class="summary-value">Rs. {{ number_format($invoice->cgst_amount, 2) }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">SGST ({{ number_format(($invoice->gst_rate ?? 18) / 2, 1) }}%):</div>
            <div class="summary-value">Rs. {{ number_format($invoice->sgst_amount, 2) }}</div>
        </div>
        @elseif(($invoice->igst_amount ?? 0) > 0)
        <div class="summary-row">
            <div class="summary-label">IGST ({{ number_format($invoice->gst_rate ?? 18, 1) }}%):</div>
            <div class="summary-value">Rs. {{ number_format($invoice->igst_amount, 2) }}</div>
        </div>
        @endif

        <div class="summary-row total-row" style="border-top: 2px solid #e2e8f0; padding-top: 8px;">
            <div class="summary-label">Amount Paid (INR):</div>
            <div class="summary-value">Rs. {{ number_format($invoice->total_amount, 2) }}</div>
        </div>
    </div>

    <div class="footer">
        This is a computer-generated payment receipt and does not require signature.<br>
        Thank you for your payment!
    </div>
</body>
</html>
