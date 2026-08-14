<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Predictive Analytics Report — NU Clark</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.4; font-size: 11px; margin: 0; padding: 20px; }
        .header { border-bottom: 3px solid #003087; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { font-size: 20px; font-weight: bold; color: #003087; text-transform: uppercase; }
        .gold-subtitle { color: #FFB800; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .report-title { font-size: 16px; font-weight: bold; margin-top: 5px; color: #111; }
        .meta-table { width: 100%; margin-bottom: 20px; font-size: 10px; }
        .meta-table td { padding: 4px 0; }
        .kpi-row { margin-bottom: 20px; }
        .kpi-card { float: left; width: 23%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; margin-right: 2%; box-sizing: border-box; }
        .kpi-title { font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: bold; }
        .kpi-value { font-size: 16px; font-weight: bold; color: #003087; margin-top: 4px; }
        .clear { clear: both; }
        .section-title { font-size: 13px; font-weight: bold; color: #003087; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 4px; margin-top: 20px; margin-bottom: 10px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; page-break-inside: avoid; }
        table.data-table th, table.data-table td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        table.data-table th { background: #003087; color: #ffffff; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        table.data-table tr:nth-child(even) { background: #f8fafc; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef9c3; color: #a16207; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .footer { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; text-align: center; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">National University Clark</div>
        <div class="gold-subtitle">Student Event Management System (EMS)</div>
        <div class="report-title">Predictive Analytics & Forecasting Report</div>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Generated On:</strong> {{ $generatedAt ?? now()->format('F d, Y — h:i A') }}</td>
        </tr>
    </table>

    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-title">Past Events Analyzed</div>
            <div class="kpi-value">{{ number_format($dataSummary['total_completed_events'] ?? 0) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Total Registrations</div>
            <div class="kpi-value">{{ number_format($dataSummary['total_registrations'] ?? 0) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Verified Attendances</div>
            <div class="kpi-value">{{ number_format($dataSummary['total_attendances'] ?? 0) }}</div>
        </div>
        <div class="kpi-card" style="margin-right:0;">
            <div class="kpi-title">Overall Rate</div>
            <div class="kpi-value" style="color:#FFB800;">{{ number_format($dataSummary['overall_rate'] ?? 0, 1) }}%</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section-title">1. Upcoming Events Predictions</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Event Title</th>
                <th>Date</th>
                <th>Category</th>
                <th style="text-align:center;">Predicted Att.</th>
                <th style="text-align:center;">Pred. Rate</th>
                <th style="text-align:center;">Confidence</th>
            </tr>
        </thead>
        <tbody>
            @forelse($predictions ?? [] as $pred)
            <tr>
                <td><strong>{{ $pred['title'] ?? 'N/A' }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($pred['date'] ?? now())->format('M d, Y') }}</td>
                <td>{{ $pred['category'] ?? 'General' }}</td>
                <td style="text-align:center; font-weight:bold; color:#003087;">{{ $pred['predicted_attendance'] ?? 0 }}</td>
                <td style="text-align:center;">{{ number_format($pred['predicted_rate'] ?? 0, 1) }}%</td>
                <td style="text-align:center;">
                    @php $conf = $pred['confidence'] ?? 'medium'; @endphp
                    <span class="badge {{ $conf == 'high' ? 'badge-success' : ($conf == 'medium' ? 'badge-warning' : 'badge-danger') }}">
                        {{ ucfirst($conf) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;">No prediction data available</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">2. Category Attendance Rate Forecasts</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Event Category</th>
                <th style="text-align:center;">Historical Rate</th>
                <th style="text-align:center;">Predicted Rate</th>
                <th style="text-align:center;">Trend Variance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categoryRates ?? [] as $cat)
            <tr>
                <td><strong>{{ $cat['category'] ?? 'N/A' }}</strong></td>
                <td style="text-align:center;">{{ number_format($cat['historical'] ?? 0, 1) }}%</td>
                <td style="text-align:center; font-weight:bold; color:#003087;">{{ number_format($cat['predicted'] ?? 0, 1) }}%</td>
                <td style="text-align:center;">
                    @php $diff = ($cat['predicted'] ?? 0) - ($cat['historical'] ?? 0); @endphp
                    @if($diff > 0)
                        <span style="color:#15803d; font-weight:bold;">+{{ number_format($diff, 1) }}%</span>
                    @elseif($diff < 0)
                        <span style="color:#b91c1c; font-weight:bold;">{{ number_format($diff, 1) }}%</span>
                    @else
                        <span style="color:#64748b;">0.0%</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;">No category forecast data available</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Confidential — National University Clark Capstone Predictive Analytics System
    </div>
</body>
</html>
