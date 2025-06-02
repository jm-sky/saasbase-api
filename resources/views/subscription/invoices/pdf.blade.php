<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .invoice-details {
            margin-bottom: 20px;
        }
        .customer-info {
            margin-bottom: 30px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .invoice-table th,
        .invoice-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .invoice-table th {
            background-color: #f5f5f5;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
    </div>

    <div class="company-info">
        <h2>{{ config('app.name') }}</h2>
        <p>123 Business Street</p>
        <p>City, State 12345</p>
        <p>Email: billing@example.com</p>
    </div>

    <div class="invoice-details">
        <p><strong>Invoice Number:</strong> {{ $invoice->number }}</p>
        <p><strong>Date:</strong> {{ $invoice->created_at->format('Y-m-d') }}</p>
        <p><strong>Due Date:</strong> {{ $invoice->due_date?->format('Y-m-d') ?? 'N/A' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
    </div>

    <div class="customer-info">
        <h3>Bill To:</h3>
        <p>{{ $billingInfo->name }}</p>
        <p>{{ $billingInfo->address_line1 }}</p>
        @if($billingInfo->address_line2)
            <p>{{ $billingInfo->address_line2 }}</p>
        @endif
        <p>{{ $billingInfo->city }}, {{ $billingInfo->state }} {{ $billingInfo->postal_code }}</p>
        <p>{{ $billingInfo->country }}</p>
        @if($billingInfo->tax_id)
            <p><strong>Tax ID:</strong> {{ $billingInfo->tax_id }}</p>
        @endif
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->description }}</td>
                <td>{{ number_format($invoice->amount, 2) }} {{ strtoupper($invoice->currency) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="total">Total:</td>
                <td class="total">{{ number_format($invoice->amount, 2) }} {{ strtoupper($invoice->currency) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This is a computer-generated invoice, no signature required.</p>
    </div>
</body>
</html>
