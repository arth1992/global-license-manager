<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #0f172a; font-size: 28px; }
        .meta-info { margin-bottom: 40px; display: table; width: 100%; }
        .meta-info .col { display: table-cell; width: 50%; }
        .meta-info .col h3 { margin-top: 0; color: #475569; font-size: 14px; text-transform: uppercase; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .table th { background-color: #f8fafc; color: #475569; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        .text-right { text-align: right !important; }
        .summary { width: 50%; float: right; }
        .summary-row { display: table; width: 100%; margin-bottom: 10px; }
        .summary-label { display: table-cell; text-align: left; font-weight: bold; color: #475569; }
        .summary-value { display: table-cell; text-align: right; }
        .total-row { font-size: 18px; color: #0f172a; border-top: 2px solid #e2e8f0; padding-top: 10px; }
        .status { padding: 5px 10px; border-radius: 4px; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        .status-Paid { background-color: #dcfce7; color: #166534; }
        .status-Unpaid { background-color: #fee2e2; color: #991b1b; }
        .status-Waived { background-color: #e0e7ff; color: #3730a3; }
        .footer { clear: both; margin-top: 50px; text-align: center; color: #64748b; font-size: 12px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Global Admission Manager</h1>
        <p>License Billing Invoice</p>
    </div>

    <div class="meta-info">
        <div class="col">
            <h3>Billed To:</h3>
            <strong>{{ $license->client_name }}</strong><br>
            {{ $license->client_email }}<br>
            Domain: {{ $license->domain }}
        </div>
        <div class="col text-right">
            <h3>Invoice Details:</h3>
            <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
            <strong>Date:</strong> {{ $date }}<br>
            <strong>Due Date:</strong> {{ $dueDate }}<br>
            <strong>Status:</strong> <span class="status status-{{ $invoice->status }}">{{ $invoice->status }}</span>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Monthly Base Platform Fee ({{ date('F Y', mktime(0, 0, 0, $invoice->invoice_month, 1, $invoice->invoice_year)) }})</td>
                <td class="text-right">₹{{ number_format($invoice->base_fee, 2) }}</td>
            </tr>
            @if($invoice->applicant_count > 0)
            <tr>
                <td>Active Applicant Usage ({{ $invoice->applicant_count }} applicants @ ₹200)</td>
                <td class="text-right">₹{{ number_format($invoice->applicant_fee, 2) }}</td>
            </tr>
            @endif
            @if($invoice->discount_applied > 0)
            <tr>
                <td>Discount / Waiver Applied</td>
                <td class="text-right">-₹{{ number_format($invoice->discount_applied, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-label">Subtotal:</div>
            <div class="summary-value">₹{{ number_format($invoice->base_fee + $invoice->applicant_fee, 2) }}</div>
        </div>
        @if($invoice->discount_applied > 0)
        <div class="summary-row">
            <div class="summary-label">Discount:</div>
            <div class="summary-value">-₹{{ number_format($invoice->discount_applied, 2) }}</div>
        </div>
        @endif
        <div class="summary-row total-row">
            <div class="summary-label">Total Due:</div>
            <div class="summary-value">₹{{ number_format($invoice->total_amount, 2) }}</div>
        </div>
    </div>

    <div class="footer">
        Please make checks payable to Global Admission Manager, or pay online via the dashboard.<br>
        Thank you for your business!
    </div>
</body>
</html>
