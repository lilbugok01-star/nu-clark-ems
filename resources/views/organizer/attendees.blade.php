@extends('layouts.app')
@section('title', 'Attendees — ' . $event->title)
@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-800 mb-1" style="color:var(--nu-blue)">{{ $event->title }}</h4>
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar me-1"></i>{{ $event->event_date->format('F d, Y') }}
                &nbsp;·&nbsp;<i class="bi bi-clock me-1"></i>{{ substr($event->start_time,0,5) }} – {{ substr($event->end_time,0,5) }}
                &nbsp;·&nbsp;<i class="bi bi-geo-alt me-1"></i>{{ $event->venue }}
                @if($event->isLive()) &nbsp;·&nbsp; <span class="live-badge">● LIVE NOW</span>@endif
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('organizer.attendance.excel', $event->id) }}" class="btn btn-success btn-sm"><i class="bi bi-file-spreadsheet me-1"></i>Excel</a>
            <a href="{{ route('organizer.attendance.pdf', $event->id) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf me-1"></i>PDF</a>
            <a href="{{ route('organizer.events') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card"><div class="stat-value">{{ $registrations->count() }}</div><div class="stat-label">Registered</div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="border-color:#28a745"><div class="stat-value" style="color:#28a745">{{ $attendances->count() }}</div><div class="stat-label">Checked In</div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="border-color:var(--nu-gold)"><div class="stat-value text-gold">{{ $attendances->where('status','verified')->count() }}</div><div class="stat-label">Verified</div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="border-color:#dc3545"><div class="stat-value" style="color:#dc3545">{{ $attendances->where('status','pending')->count() }}</div><div class="stat-label">Pending</div></div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="nu-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-700 mb-0"><i class="bi bi-people me-2" style="color:var(--nu-gold)"></i>Attendance Records</h6>
            <span class="badge" style="background:var(--gray-100);color:var(--gray-600)">{{ $attendances->count() }} records</span>
        </div>
        <div class="table-responsive">
            <table class="table nu-table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Course / Section</th>
                        <th>Check-in Time</th>
                        <th>Photo</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $att)
                    <tr>
                        <td class="fw-600 small">{{ $att->registration?->user?->name ?? '-' }}</td>
                        <td class="text-muted small">{{ $att->registration?->user?->student_id ?? '-' }}</td>
                        <td class="small">
                            {{ $att->registration?->user?->course?->code ?? '' }}
                            {{ $att->registration?->user?->section?->name ?? '' }}
                        </td>
                        <td class="small">{{ $att->checked_in_at?->format('M d, H:i') ?? '-' }}</td>
                        <td>
                            @if($att->photo_path)
                                <a href="{{ asset('storage/' . $att->photo_path) }}" target="_blank" data-bs-toggle="modal" data-bs-target="#photoModal{{ $att->id }}">
                                    <img src="{{ asset('storage/' . $att->photo_path) }}" class="attendance-photo" alt="photo">
                                </a>
                                <!-- Photo Modal -->
                                <div class="modal fade" id="photoModal{{ $att->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-3 overflow-hidden">
                                            <div class="modal-header border-0 pb-0">
                                                <strong class="small" style="color:var(--nu-blue)">{{ $att->registration?->user?->name ?? 'Student' }}</strong>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-2">
                                                <img src="{{ asset('storage/' . $att->photo_path) }}" class="w-100 rounded-2 bg-dark" style="object-fit:contain;max-height:450px" alt="Attendance Photo">
                                            </div>
                                            @if($att->status === 'pending')
                                            <div class="modal-footer d-flex gap-2 border-0 pt-0">
                                                <form action="{{ route('organizer.attendance.verify', $att->id) }}" method="POST" class="d-flex gap-2 w-100" style="margin:0">
                                                    @csrf @method('PUT')
                                                    <button name="status" value="verified" class="btn btn-success flex-grow-1 fw-700"><i class="bi bi-check-lg me-1"></i>Verify</button>
                                                    <button name="status" value="rejected" class="btn btn-danger flex-grow-1 fw-700"><i class="bi bi-x-lg me-1"></i>Reject</button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small"><i class="bi bi-image-alt me-1"></i>No photo</span>
                            @endif
                        </td>
                        <td>
                            @if($att->status === 'verified')
                                <span class="badge-status-published"><i class="bi bi-check-circle me-1"></i>Verified</span>
                            @elseif($att->status === 'rejected')
                                <span class="badge-status-cancelled"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                            @else
                                <span class="badge-status-draft"><i class="bi bi-clock me-1"></i>Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($att->status === 'pending')
                            <form action="{{ route('organizer.attendance.verify', $att->id) }}" method="POST" class="d-flex gap-1">
                                @csrf @method('PUT')
                                <button name="status" value="verified" class="btn btn-success btn-sm py-1 px-2" title="Verify"><i class="bi bi-check-lg"></i></button>
                                <button name="status" value="rejected" class="btn btn-danger btn-sm py-1 px-2" title="Reject"><i class="bi bi-x-lg"></i></button>
                            </form>
                            @else
                                <span class="text-muted small">Done</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @if($attendances->count() === 0)
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-person-x" style="font-size:2rem;opacity:.3"></i>
                        <p class="small mt-2 mb-0">No check-ins recorded yet.</p>
                    </td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
