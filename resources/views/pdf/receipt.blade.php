<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #059669; font-size: 28px; }
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
        .footer { clear: both; margin-top: 50px; text-align: center; color: #64748b; font-size: 12px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PAYMENT RECEIPT</h1>
        <p>Global Admission Manager</p>
    </div>

    <div class="meta-info">
        <div class="col">
            <h3>Billed To:</h3>
            <strong>{{ $license->client_name }}</strong><br>
            {{ $license->client_email }}<br>
            Domain: {{ $license->domain }}
        </div>
        <div class="col text-right">
            <h3>Payment Details:</h3>
            <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
            <strong>Date Paid:</strong> {{ $date }}<br>
            <strong>Transaction ID:</strong> {{ $invoice->transaction_id ?? 'N/A' }}
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
                <td>Payment for Invoice {{ $invoice->invoice_number }}</td>
                <td class="text-right">₹{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row total-row">
            <div class="summary-label">Amount Paid:</div>
            <div class="summary-value">₹{{ number_format($invoice->total_amount, 2) }}</div>
        </div>
    </div>

    <div class="footer">
        Thank you for your payment!
    </div>
</body>
</html>
