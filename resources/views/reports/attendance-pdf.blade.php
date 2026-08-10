<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance — {{ $event->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #003087; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; color: #003087; margin: 4px 0; }
        .header h2 { font-size: 13px; color: #555; margin: 0; font-weight: normal; }
        .nu-gold { color: #FFB800; }
        .info-grid { display: table; width: 100%; margin-bottom: 16px; }
        .info-col { display: table-cell; width: 50%; padding: 4px 0; }
        .stats { background: #f0f4ff; padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; }
        .stats strong { color: #003087; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #003087; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #e9ecef; }
        tr:nth-child(even) { background: #f8f9fa; }
        .badge { padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .verified { background: #d4edda; color: #155724; }
        .pending  { background: #fff3cd; color: #856404; }
        .rejected { background: #f8d7da; color: #721c24; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <div style="font-size:22px;font-weight:900;color:#003087;letter-spacing:-1px">NU <span class="nu-gold">Clark</span></div>
        <h1>Event Attendance Report</h1>
        <h2>{{ $event->title }}</h2>
    </div>

    <div class="info-grid">
        <div class="info-col"><strong>Venue:</strong> {{ $event->venue }}</div>
        <div class="info-col"><strong>Date:</strong> {{ $event->event_date->format('F d, Y') }}</div>
        <div class="info-col"><strong>Time:</strong> {{ substr($event->start_time,0,5) }} – {{ substr($event->end_time,0,5) }}</div>
        <div class="info-col"><strong>Organizer:</strong> {{ $event->organizer->full_name ?? '-' }}</div>
    </div>

    <div class="stats">
        <strong>Total Attended:</strong> {{ $attendances->count() }} &nbsp;|&nbsp;
        <strong>Verified:</strong> {{ $attendances->where('status','verified')->count() }} &nbsp;|&nbsp;
        <strong>Pending:</strong> {{ $attendances->where('status','pending')->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Student ID</th>
                <th>Course / Section</th>
                <th>Check-in Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $i => $att)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $att->registration?->user?->full_name ?? '-' }}</td>
                <td>{{ $att->registration?->user?->student_id ?? '-' }}</td>
                <td>{{ $att->registration?->user?->course?->code ?? '' }} {{ $att->registration?->user?->section?->name ?? '' }}</td>
                <td>{{ $att->checked_in_at?->format('H:i') ?? '-' }}</td>
                <td><span class="badge {{ $att->status }}">{{ ucfirst($att->status) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#999">No attendance records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('F d, Y \a\t H:i') }} — National University Clark Event Management System
    </div>
</body>
</html>
