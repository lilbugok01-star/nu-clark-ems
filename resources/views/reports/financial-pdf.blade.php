<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report - {{ $event->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #003087; /* nu-blue */
            color: white;
            padding: 20px;
            text-align: center;
            border-bottom: 5px solid #FFB800; /* nu-gold */
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: normal;
        }
        .section-title {
            color: #003087;
            font-size: 16px;
            border-bottom: 2px solid #FFB800;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #003087;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .summary-box td {
            padding: 10px;
            border: none;
            background-color: #f8f9fa;
            border-left: 4px solid #003087;
            margin-bottom: 5px;
            display: block;
        }
        .summary-box .label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 150px;
        }
        .summary-box .value {
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .summary-table {
            width: 50%;
            margin-left: auto;
        }
        .summary-table td, .summary-table th {
            border: 1px solid #ddd;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            color: white;
            text-transform: uppercase;
        }
        .bg-income { background-color: #198754; }
        .bg-expense { background-color: #dc3545; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Financial Report</h1>
        <h2>{{ $event->title }}</h2>
        <p>Event Date: {{ \Carbon\Carbon::parse($event->start_date)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('F d, Y') }}</p>
    </div>

    <div class="section-title">1. Financial Summary</div>
    <table class="summary-table">
        <tr>
            <th>Total Budget Estimated</th>
            <td class="text-right">₱{{ number_format($totals['total_estimated'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <th>Total Budget Actual</th>
            <td class="text-right">₱{{ number_format($totals['total_actual'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <th>Total Income</th>
            <td class="text-right text-success">₱{{ number_format($totals['total_income'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <th>Total Expenses</th>
            <td class="text-right text-danger">₱{{ number_format($totals['total_expenses'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <th>Net Profit / Loss</th>
            @php
                $net = $totals['net'] ?? 0;
                $netClass = $net >= 0 ? 'text-success' : 'text-danger';
            @endphp
            <td class="text-right {{ $netClass }}" style="font-weight:bold; font-size: 14px;">₱{{ number_format($net, 2) }}</td>
        </tr>
    </table>

    <div class="section-title">2. Budget Breakdown</div>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Description</th>
                <th class="text-right">Estimated</th>
                <th class="text-right">Actual</th>
                <th class="text-right">Variance</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($budgetItems as $item)
                @php
                    $itemVar = $item->estimated_amount - $item->actual_amount;
                    $itemVarColor = $itemVar >= 0 ? 'text-success' : 'text-danger';
                @endphp
                <tr>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">₱{{ number_format($item->estimated_amount, 2) }}</td>
                    <td class="text-right">₱{{ number_format($item->actual_amount, 2) }}</td>
                    <td class="text-right {{ $itemVarColor }}">₱{{ number_format($itemVar, 2) }}</td>
                    <td class="text-center">{{ ucfirst($item->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No budget items recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">3. Transactions (Payments & Expenses)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                @php
                    $isIncome = $payment->type === 'income';
                    $amountColor = $isIncome ? 'text-success' : 'text-danger';
                    $sign = $isIncome ? '+' : '-';
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $isIncome ? 'bg-income' : 'bg-expense' }}">{{ ucfirst($payment->type) }}</span>
                    </td>
                    <td>{{ $payment->description }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->reference_number ?? '-' }}</td>
                    <td class="text-right {{ $amountColor }}">{{ $sign }} ₱{{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No transactions recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by NU Clark Event Management System on {{ date('F d, Y h:i A') }}<br>
        Confidential Financial Document
    </div>

</body>
</html>
