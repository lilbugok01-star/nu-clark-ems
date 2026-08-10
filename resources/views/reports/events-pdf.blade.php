<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Events Report — NU Clark</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #003087; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; color: #003087; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #003087; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #e9ecef; }
        tr:nth-child(even) { background: #f8f9fa; }
        .badge { padding: 2px 8px; border-radius: 20px; font-size: 10px; }
        .published { background: #d4edda; color: #155724; }
        .cancelled { background: #f8d7da; color: #721c24; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <div style="font-size:22px;font-weight:900;color:#003087">NU <span style="color:#FFB800">Clark</span></div>
        <h1>Events Summary Report</h1>
        <p style="margin:0;color:#555">Generated: {{ now()->format('F d, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Event Title</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Capacity</th>
                <th>Registrations</th>
                <th>Verified</th>
                <th>Organizer</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $i => $e)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $e->title }}</td>
                <td>{{ $e->event_date->format('M d, Y') }}</td>
                <td>{{ $e->venue }}</td>
                <td>{{ $e->capacity }}</td>
                <td>{{ $e->registrations_count }}</td>
                <td>{{ $e->verified_count }}</td>
                <td>{{ $e->organizer->full_name ?? '-' }}</td>
                <td><span class="badge {{ $e->status }}">{{ ucfirst($e->status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">NU Clark Event Management System — Confidential</div>
</body>
</html>
