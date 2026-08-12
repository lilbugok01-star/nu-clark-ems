<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Participation Pattern Analytics Report — NU Clark</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.4; font-size: 11px; margin: 0; padding: 20px; }
        .header { border-bottom: 3px solid #003087; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { font-size: 20px; font-weight: bold; color: #003087; text-transform: uppercase; }
        .gold-subtitle { color: #FFB800; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .report-title { font-size: 16px; font-weight: bold; margin-top: 5px; color: #111; }
        .meta-table { width: 100%; margin-bottom: 20px; font-size: 10px; }
        .meta-table td { padding: 4px 0; }
        .kpi-row { margin-bottom: 20px; }
        .kpi-card { float: left; width: 31%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; margin-right: 2%; box-sizing: border-box; }
        .kpi-title { font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: bold; }
        .kpi-value { font-size: 16px; font-weight: bold; color: #003087; margin-top: 4px; }
        .clear { clear: both; }
        .section-title { font-size: 13px; font-weight: bold; color: #003087; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 4px; margin-top: 20px; margin-bottom: 10px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; }
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
        <div class="report-title">Student Event Participation Pattern & Engagement Analytics</div>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Generated On:</strong> {{ now()->format('F d, Y — h:i A') }}</td>
            <td style="text-align:right;"><strong>Filter Range:</strong> {{ $dateFrom ?? 'All Time' }} to {{ $dateTo ?? 'Present' }}</td>
        </tr>
    </table>

    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-title">Total Published Events</div>
            <div class="kpi-value">{{ $overview['total_events'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Total Registrations</div>
            <div class="kpi-value">{{ number_format($overview['total_registrations']) }}</div>
        </div>
        <div class="kpi-card" style="margin-right:0;">
            <div class="kpi-title">Overall Attendance Rate</div>
            <div class="kpi-value" style="color:#FFB800;">{{ $overview['overall_attendance_rate'] }}%</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="section-title">1. Event Category Popularity & Conversion Rates</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Category Name</th>
                <th style="text-align:center;">Total Registrations</th>
                <th style="text-align:center;">Verified Attendances</th>
                <th style="text-align:center;">Attendance Conversion Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryPopularity as $cat)
            <tr>
                <td><strong>{{ $cat['category'] }}</strong></td>
                <td style="text-align:center;">{{ $cat['registrations'] }}</td>
                <td style="text-align:center;">{{ $cat['attendances'] }}</td>
                <td style="text-align:center;">
                    <span class="badge {{ $cat['rate'] >= 70 ? 'badge-success' : ($cat['rate'] >= 50 ? 'badge-warning' : 'badge-danger') }}">
                        {{ $cat['rate'] }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">2. Course vs. Event Category Participation Breakdown</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Academic Course / Program</th>
                <th>Event Category</th>
                <th style="text-align:center;">Registrations</th>
                <th style="text-align:center;">Verified Attendances</th>
            </tr>
        </thead>
        <tbody>
            @forelse(array_slice($courseVsCategory, 0, 15) as $row)
            <tr>
                <td><strong>{{ $row['course'] }}</strong></td>
                <td>{{ $row['category'] }}</td>
                <td style="text-align:center;">{{ $row['registrations'] }}</td>
                <td style="text-align:center;">{{ $row['attendances'] }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;">No data available</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">3. Event Engagement Index</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Event Title</th>
                <th style="text-align:center;">Capacity Fill Rate</th>
                <th style="text-align:center;">Attendance Turnout Rate</th>
                <th style="text-align:center;">Engagement Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($engagementScores, 0, 10) as $score)
            <tr>
                <td><strong>{{ $score['title'] }}</strong></td>
                <td style="text-align:center;">{{ $score['fill_rate'] }}%</td>
                <td style="text-align:center;">{{ $score['attendance_rate'] }}%</td>
                <td style="text-align:center;">
                    <span class="badge {{ $score['engagement_score'] >= 70 ? 'badge-success' : ($score['engagement_score'] >= 40 ? 'badge-warning' : 'badge-danger') }}">
                        {{ $score['engagement_score'] }} / 100
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential — National University Clark Capstone Telemetry & Event Analytics System Page 1
    </div>
</body>
</html>
