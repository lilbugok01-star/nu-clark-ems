@extends('layouts.app')
@section('title', 'My Registered Events')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-800 mb-0" style="color:var(--nu-blue)"><i class="bi bi-ticket-perforated me-2" style="color:var(--nu-gold)"></i>My Registered Events</h4>
            <p class="text-muted small mb-0">All events you have signed up for</p>
        </div>
        <a href="{{ route('student.events') }}" class="btn btn-nu-blue btn-sm"><i class="bi bi-plus me-1"></i>Browse More Events</a>
    </div>

    @if($registrations->count() > 0)
    <div class="nu-card">
        <div class="table-responsive">
            <table class="table nu-table mb-0">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date & Time</th>
                        <th>Venue</th>
                        <th>Registration</th>
                        <th>Attendance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $reg)
                    <tr>
                        <td>
                            <div class="fw-700 small" style="color:var(--nu-blue)">{{ $reg->event->title }}</div>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @if($reg->event->category)<span class="badge-category">{{ $reg->event->category }}</span>@endif
                                @if($reg->event->isLive())<span class="live-badge ms-1">● LIVE</span>@endif
                            </div>
                        </td>
                        <td class="small">
                            <div class="fw-600">{{ $reg->event->event_date->format('M d, Y') }}</div>
                            <div class="text-muted">{{ substr($reg->event->start_time,0,5) }} – {{ substr($reg->event->end_time,0,5) }}</div>
                        </td>
                        <td class="small">
                            <div>{{ $reg->event->venue }}</div>
                            @if($reg->event->venue_type)<span class="venue-badge mt-1">{{ $reg->event->venue_type }}</span>@endif
                        </td>
                        <td>
                            @if($reg->status === 'confirmed')
                                <span class="badge-status-published">✓ Confirmed</span>
                            @elseif($reg->status === 'cancelled')
                                <span class="badge-status-cancelled">Cancelled</span>
                            @else
                                <span class="badge-status-draft">{{ ucfirst($reg->status) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($reg->attendance)
                                <div class="d-flex flex-column gap-1">
                                    @if($reg->attendance->status === 'verified')
                                        <span class="badge-status-published"><i class="bi bi-check-circle me-1"></i>Verified</span>
                                    @elseif($reg->attendance->status === 'rejected')
                                        <span class="badge-status-cancelled"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                    @else
                                        <span class="badge-status-draft"><i class="bi bi-clock me-1"></i>Pending</span>
                                    @endif
                                    <div class="d-flex gap-2 small">
                                        <span class="text-success"><i class="bi bi-box-arrow-in-right me-1"></i>{{ $reg->attendance->checked_in_at?->format('h:i A') ?? '--:--' }}</span>
                                        <span class="{{ $reg->attendance->checked_out_at ? 'text-primary' : 'text-muted' }}"><i class="bi bi-box-arrow-right me-1"></i>{{ $reg->attendance->checked_out_at?->format('h:i A') ?? '--:--' }}</span>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">Not checked in</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($reg->status === 'confirmed')
                                    @if($reg->attendance && !$reg->attendance->checked_out_at)
                                        <a href="{{ route('student.qr', $reg->id) }}"
                                           class="btn btn-gold btn-sm fw-600"
                                           title="Submit Time Out Selfie">
                                            <i class="bi bi-box-arrow-right me-1"></i>Time Out
                                        </a>
                                    @else
                                        <a href="{{ route('student.qr', $reg->id) }}"
                                           class="btn {{ $reg->event->isLive() ? 'btn-gold' : 'btn-outline-gold' }} btn-sm"
                                           title="{{ $reg->event->isLive() ? 'Attend Now!' : 'View QR Code' }}">
                                            <i class="bi bi-qr-code"></i>
                                            @if($reg->event->isLive()) <span class="ms-1">Now!</span>@endif
                                        </a>
                                    @endif
                                    @if(!$reg->attendance && $reg->event->event_date >= now()->toDateString())
                                    <form action="{{ route('student.cancel', $reg->id) }}" method="POST" onsubmit="return confirm('Cancel your registration for this event?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Cancel Registration"><i class="bi bi-x"></i></button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-5 nu-card">
        <i class="bi bi-ticket-perforated" style="font-size:4rem;color:var(--gray-200)"></i>
        <h5 class="text-muted mt-3">No registrations yet</h5>
        <p class="text-muted small">Browse upcoming events and sign up!</p>
        <a href="{{ route('student.events') }}" class="btn btn-nu-blue mt-2"><i class="bi bi-calendar3 me-1"></i>Browse Events</a>
    </div>
    @endif
</div>
@endsection
