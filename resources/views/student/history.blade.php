@extends('layouts.app')
@section('title', 'Attendance History')
@section('content')
<div class="container py-5">
    <h4 class="fw-bold mb-4" style="color:var(--nu-blue)"><i class="bi bi-clock-history me-2"></i>Attendance History</h4>
    @if($registrations->count() > 0)
    <div class="row g-4">
        @foreach($registrations as $reg)
        <div class="col-md-6">
            <div class="nu-card p-4">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="fw-bold mb-0">{{ $reg->event->title }}</h6>
                    @if($reg->attendance)
                        @if($reg->attendance->status === 'verified')
                            <span class="badge bg-success">✓ Attended</span>
                        @else
                            <span class="badge bg-secondary">Unverified</span>
                        @endif
                    @else
                        <span class="badge bg-warning text-dark">No Check-in</span>
                    @endif
                </div>
                <p class="text-muted small mb-1"><i class="bi bi-calendar me-1"></i>{{ $reg->event->event_date->format('F d, Y') }}</p>
                <p class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $reg->event->venue }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-5 nu-card">
        <div style="font-size:4rem">📋</div>
        <h5 class="text-muted mt-3">No past events yet.</h5>
        <a href="{{ route('student.events') }}" class="btn btn-nu-blue mt-2">Browse Events</a>
    </div>
    @endif
</div>
@endsection
