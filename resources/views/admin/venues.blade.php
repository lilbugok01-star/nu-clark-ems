@extends('layouts.app')
@section('title', 'Venue Management')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-800 mb-0" style="color:var(--nu-blue)"><i class="bi bi-building me-2" style="color:var(--nu-gold)"></i>Venue Reservation Management</h4>
            <p class="text-muted small mb-0">Review and approve organizer venue requests</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge py-2 px-3" style="background:rgba(255,193,7,.15);color:#856404;font-size:.85rem">
                {{ $pending }} Pending
            </span>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card"><div class="stat-value">{{ $total }}</div><div class="stat-label">Total Requests</div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="border-color:#ffc107"><div class="stat-value" style="color:#856404">{{ $pending }}</div><div class="stat-label">Pending</div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="border-color:#28a745"><div class="stat-value" style="color:#28a745">{{ $approved }}</div><div class="stat-label">Approved</div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="border-color:#dc3545"><div class="stat-value" style="color:#dc3545">{{ $rejected }}</div><div class="stat-label">Rejected</div></div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-4">
        <div class="d-flex gap-2 flex-wrap">
            @foreach(['all' => 'All Requests','pending' => 'Pending','approved' => 'Approved','rejected' => 'Rejected'] as $val => $lbl)
            <a href="{{ route('admin.venues', ['status' => $val]) }}"
               class="btn btn-sm {{ request('status', 'all') === $val ? 'btn-nu-blue' : 'btn-outline-secondary' }}">{{ $lbl }}</a>
            @endforeach
        </div>
    </div>

    <!-- Reservations Table -->
    <div class="nu-card">
        <div class="table-responsive">
            <table class="table nu-table mb-0">
                <thead>
                    <tr><th>Requested By</th><th>Event</th><th>Venue</th><th>Date & Time</th><th>Attendees</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($reservations as $res)
                    <tr>
                        <td>
                            <div class="fw-600 small">{{ $res->reservedBy?->full_name ?? '-' }}</div>
                            <div class="text-muted" style="font-size:.72rem">{{ $res->reservedBy?->email ?? '' }}</div>
                        </td>
                        <td class="small">{{ $res->event?->title ?? $res->event_title ?? 'N/A' }}</td>
                        <td><span class="venue-badge"><i class="bi bi-building me-1"></i>{{ $res->venue_name }}</span></td>
                        <td class="small">
                            <div class="fw-600">{{ $res->reserved_date->format('M d, Y') }}</div>
                            <div class="text-muted">{{ substr($res->start_time,0,5) }} – {{ substr($res->end_time,0,5) }}</div>
                        </td>
                        <td class="small text-center">{{ $res->expected_attendees }}</td>
                        <td>
                            @if($res->status === 'approved')
                                <span class="badge-status-published">Approved</span>
                            @elseif($res->status === 'rejected')
                                <span class="badge-status-cancelled">Rejected</span>
                            @else
                                <span class="badge-status-draft">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($res->status === 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.venues.status', $res->id) }}" method="POST" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-success btn-sm py-1 px-2 fw-600" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <!-- Reject with notes modal -->
                                <button class="btn btn-danger btn-sm py-1 px-2" title="Reject"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $res->id }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            @elseif($res->status === 'approved')
                                <span class="text-muted small">Approved</span>
                            @else
                                <span class="text-muted small">Done</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Reject Modal -->
                    @if($res->status === 'pending')
                    <div class="modal fade" id="rejectModal{{ $res->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-3">
                                <div class="modal-header"><h6 class="modal-title fw-700">Reject Reservation</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form action="{{ route('admin.venues.status', $res->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="rejected">
                                    <div class="modal-body">
                                        <label class="form-label fw-600">Reason for rejection <span class="text-muted">(optional)</span></label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Venue already booked for that date"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger btn-sm fw-700">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-building" style="font-size:2.5rem;opacity:.2"></i>
                        <p class="small mt-2 mb-0">No venue reservations found.</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $reservations->links() }}</div>
</div>
@endsection
