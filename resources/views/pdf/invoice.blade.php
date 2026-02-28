<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .container {
            padding: 40px;
        }
        .header {
            margin-bottom: 40px;
        }
        .header-row {
            width: 100%;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 50%;
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        .company-details {
            color: #666;
            font-size: 11px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .invoice-date {
            color: #666;
            font-size: 11px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .status-draft { background: #e5e7eb; color: #4b5563; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-overdue { background: #fee2e2; color: #dc2626; }
        .status-cancelled { background: #e5e7eb; color: #6b7280; }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .billing-section {
            margin-bottom: 40px;
        }
        .billing-row {
            width: 100%;
        }
        .bill-to {
            float: left;
            width: 50%;
        }
        .bill-info {
            float: right;
            width: 50%;
            text-align: right;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 8px;
        }
        .client-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        .client-details {
            color: #666;
            font-size: 11px;
        }
        .date-info {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #f9fafb;
            padding: 12px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }
        th.text-right {
            text-align: right;
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        td.text-right {
            text-align: right;
        }
        .item-description {
            color: #1a1a1a;
        }
        .item-details {
            color: #9ca3af;
            font-size: 11px;
        }
        .totals {
            width: 100%;
            margin-bottom: 40px;
        }
        .totals-row {
            width: 100%;
        }
        .totals-spacer {
            float: left;
            width: 50%;
        }
        .totals-table {
            float: right;
            width: 50%;
        }
        .totals-table table {
            margin-bottom: 0;
        }
        .totals-table td {
            border: none;
            padding: 8px 10px;
        }
        .totals-label {
            text-align: left;
            color: #666;
        }
        .totals-value {
            text-align: right;
            font-weight: 500;
        }
        .total-row {
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
        }
        .total-row .totals-label {
            font-weight: bold;
            color: #1a1a1a;
            font-size: 14px;
        }
        .total-row .totals-value {
            font-weight: bold;
            color: #4f46e5;
            font-size: 14px;
        }
        .notes-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .notes-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 8px;
        }
        .notes-content {
            color: #666;
            font-size: 11px;
            white-space: pre-line;
        }
        .footer {
            position: fixed;
            bottom: 40px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <div class="header-row">
                <div class="company-info">
                    <div class="company-name">{{ $tenant->name }}</div>
                    <div class="company-details">
                        @if($tenant->address ?? false)
                            {{ $tenant->address }}<br>
                        @endif
                        @if($tenant->email ?? false)
                            {{ $tenant->email }}<br>
                        @endif
                        @if($tenant->tax_id ?? false)
                            Tax ID: {{ $tenant->tax_id }}
                        @endif
                    </div>
                </div>
                <div class="invoice-info">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    <div class="status-badge status-{{ $invoice->status->value }}">
                        {{ $invoice->status->label() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="billing-section clearfix">
            <div class="billing-row">
                <div class="bill-to">
                    <div class="section-title">Bill To</div>
                    <div class="client-name">{{ $invoice->client->name }}</div>
                    <div class="client-details">
                        @if($invoice->client->email)
                            {{ $invoice->client->email }}<br>
                        @endif
                        @if($invoice->client->phone)
                            {{ $invoice->client->phone }}<br>
                        @endif
                        @if($invoice->client->address)
                            {{ $invoice->client->address }}<br>
                        @endif
                        @if($invoice->client->tax_id)
                            Tax ID: {{ $invoice->client->tax_id }}
                        @endif
                    </div>
                </div>
                <div class="bill-info">
                    <div class="date-info">
                        <strong>Issue Date:</strong> {{ $invoice->issue_date->format('M j, Y') }}
                    </div>
                    <div class="date-info">
                        <strong>Due Date:</strong> {{ $invoice->due_date->format('M j, Y') }}
                    </div>
                    @if($invoice->paid_at)
                        <div class="date-info">
                            <strong>Paid On:</strong> {{ $invoice->paid_at->format('M j, Y') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 45%;">Description</th>
                    <th class="text-right" style="width: 12%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    <th class="text-right" style="width: 12%;">Tax</th>
                    <th class="text-right" style="width: 16%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td class="item-description">{{ $item->description }}</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->tax_rate, 1) }}%</td>
                        <td class="text-right">${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals clearfix">
            <div class="totals-row">
                <div class="totals-spacer"></div>
                <div class="totals-table">
                    <table>
                        <tr>
                            <td class="totals-label">Subtotal</td>
                            <td class="totals-value">${{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="totals-label">Tax</td>
                            <td class="totals-value">${{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td class="totals-label">Total</td>
                            <td class="totals-value">${{ number_format($invoice->total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        @if($invoice->notes || $invoice->terms)
            <div class="notes-section">
                @if($invoice->notes)
                    <div style="margin-bottom: 20px;">
                        <div class="notes-title">Notes</div>
                        <div class="notes-content">{{ $invoice->notes }}</div>
                    </div>
                @endif
                @if($invoice->terms)
                    <div>
                        <div class="notes-title">Terms & Conditions</div>
                        <div class="notes-content">{{ $invoice->terms }}</div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="footer">
        Thank you for your business!
    </div>
</body>
</html>
