@extends('layouts.app')
@section('title', 'QR Attendance Scanner')

@section('content')
<div class="container py-5 d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="nu-card p-4 text-center" style="max-width: 440px; width:100%; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-radius: 16px;">
        
        @if($status === 'time_in' || $status === 'success')
            <!-- Time In Success -->
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background: rgba(25,135,84,0.12);">
                    <i class="bi bi-box-arrow-in-right text-success" style="font-size: 3rem;"></i>
                </div>
            </div>
            <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">1st Scan • Entry</span>
            <h3 class="fw-800 text-success mb-1">Time In Recorded!</h3>
            <p class="text-muted small mb-3">{{ $message }}</p>

            <div class="p-3 mb-4 text-start" style="background: rgba(0,48,135,0.04); border-left: 4px solid #198754; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-700" style="color:var(--nu-blue); font-size: 1.05rem;">
                        <i class="bi bi-person-fill me-1"></i> {{ $registration->user->full_name ?? 'Student' }}
                    </span>
                    <span class="badge bg-success text-white small">PRESENT</span>
                </div>
                <div class="text-muted small mb-1">
                    <i class="bi bi-card-text me-2"></i> ID: <strong>{{ $registration->user->student_id ?? 'N/A' }}</strong>
                </div>
                <div class="text-muted small mb-1">
                    <i class="bi bi-mortarboard me-2"></i> {{ $registration->user->course->code ?? '' }} {{ $registration->user->section->name ?? '' }}
                </div>
                <div class="text-muted small mb-2">
                    <i class="bi bi-calendar-event me-2"></i> {{ $registration->event->title ?? '' }}
                </div>
                <div class="pt-2 border-top d-flex justify-content-between align-items-center small">
                    <span class="text-muted"><i class="bi bi-clock me-1"></i> Time In:</span>
                    <strong class="text-success">{{ $attendance->checked_in_at ? $attendance->checked_in_at->format('h:i A') : now()->format('h:i A') }}</strong>
                </div>
            </div>

        @elseif($status === 'time_out')
            <!-- Time Out Success -->
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background: rgba(13,110,253,0.12);">
                    <i class="bi bi-box-arrow-right text-primary" style="font-size: 3rem;"></i>
                </div>
            </div>
            <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">2nd Scan • Exit</span>
            <h3 class="fw-800 text-primary mb-1">Time Out Recorded!</h3>
            <p class="text-muted small mb-3">{{ $message }}</p>

            <div class="p-3 mb-4 text-start" style="background: rgba(13,110,253,0.04); border-left: 4px solid #0d6efd; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-700" style="color:var(--nu-blue); font-size: 1.05rem;">
                        <i class="bi bi-person-fill me-1"></i> {{ $registration->user->full_name ?? 'Student' }}
                    </span>
                    <span class="badge bg-primary text-white small">COMPLETED</span>
                </div>
                <div class="text-muted small mb-1">
                    <i class="bi bi-card-text me-2"></i> ID: <strong>{{ $registration->user->student_id ?? 'N/A' }}</strong>
                </div>
                <div class="text-muted small mb-2">
                    <i class="bi bi-calendar-event me-2"></i> {{ $registration->event->title ?? '' }}
                </div>
                <div class="pt-2 border-top d-flex justify-content-between align-items-center small mb-1">
                    <span class="text-muted"><i class="bi bi-box-arrow-in-right text-success me-1"></i> Time In:</span>
                    <strong class="text-dark">{{ $attendance->checked_in_at ? $attendance->checked_in_at->format('h:i A') : '--:--' }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center small">
                    <span class="text-muted"><i class="bi bi-box-arrow-right text-primary me-1"></i> Time Out:</span>
                    <strong class="text-primary">{{ $attendance->checked_out_at ? $attendance->checked_out_at->format('h:i A') : now()->format('h:i A') }}</strong>
                </div>
            </div>

        @elseif($status === 'already_completed')
            <!-- Already Completed Both In and Out -->
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background: rgba(255,193,7,0.15);">
                    <i class="bi bi-check2-all text-warning" style="font-size: 3rem;"></i>
                </div>
            </div>
            <span class="badge bg-warning-subtle text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Already Completed</span>
            <h3 class="fw-800 text-dark mb-1">Attendance Complete</h3>
            <p class="text-muted small mb-3">{{ $message }}</p>

            <div class="p-3 mb-4 text-start" style="background: rgba(255,193,7,0.06); border-left: 4px solid #ffc107; border-radius: 10px;">
                <p class="mb-1 fw-700" style="color:var(--nu-blue)">
                    <i class="bi bi-person-fill me-2"></i> {{ $registration->user->full_name ?? 'Student' }}
                </p>
                <p class="mb-2 text-muted small">
                    <i class="bi bi-calendar-event me-2"></i> {{ $registration->event->title ?? '' }}
                </p>
                <div class="pt-2 border-top d-flex justify-content-between align-items-center small mb-1">
                    <span class="text-muted"><i class="bi bi-box-arrow-in-right text-success me-1"></i> Time In:</span>
                    <span class="fw-bold">{{ $attendance->checked_in_at ? $attendance->checked_in_at->format('h:i A') : '--:--' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center small">
                    <span class="text-muted"><i class="bi bi-box-arrow-right text-primary me-1"></i> Time Out:</span>
                    <span class="fw-bold">{{ $attendance->checked_out_at ? $attendance->checked_out_at->format('h:i A') : '--:--' }}</span>
                </div>
            </div>

        @elseif($status === 'warning')
            <!-- Warning -->
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background: rgba(255,193,7,0.15);">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                </div>
            </div>
            <h3 class="fw-800 text-warning mb-2">Scan Notice</h3>
            <p class="text-muted mb-4">{{ $message }}</p>

        @else
            <!-- Error Status -->
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background: rgba(220,53,69,0.12);">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                </div>
            </div>
            <h3 class="fw-800 text-danger mb-2">Scan Failed</h3>
            <p class="text-muted mb-4">{{ $message }}</p>
        @endif

        <div class="d-grid gap-2">
            <a href="{{ route('organizer.dashboard') }}" class="btn btn-nu-blue fw-700 py-2">
                <i class="bi bi-qr-code-scan me-2"></i>Scan Another Code
            </a>
            <a href="{{ route('organizer.events') }}" class="btn btn-outline-secondary btn-sm fw-600 py-2">
                <i class="bi bi-arrow-left me-1"></i>Back to Events
            </a>
        </div>
    </div>
</div>

<style>
body { background: #f4f6fa; }
</style>
@endsection
