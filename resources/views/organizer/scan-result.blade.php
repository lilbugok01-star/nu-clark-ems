@extends('layouts.app')
@section('title', 'QR Scan Check-In')

@section('content')
<div class="container py-5 d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="nu-card p-4 text-center" style="max-width: 400px; width:100%; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        
        @if($status === 'success')
            <!-- Success Status -->
            <div class="mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
            </div>
            <h3 class="fw-800 text-success mb-2">Checked In!</h3>
            <p class="text-muted mb-4">{{ $message }}</p>

            <div class="p-3 mb-4 text-start" style="background: rgba(0,48,135,0.05); border-left: 4px solid var(--nu-blue); border-radius: 8px;">
                <p class="mb-1 fw-700" style="color:var(--nu-blue)">
                    <i class="bi bi-person me-2"></i> {{ $registration->user->name ?? 'Student' }}
                </p>
                <p class="mb-1 text-muted small">
                    <i class="bi bi-card-text me-2"></i> {{ $registration->user->student_id ?? 'N/A' }}
                </p>
                <p class="mb-0 text-muted small">
                    <i class="bi bi-calendar-event me-2"></i> {{ $registration->event->title ?? '' }}
                </p>
            </div>

        @elseif($status === 'warning')
            <!-- Warning/Already Scanned -->
            <div class="mb-4">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 5rem;"></i>
            </div>
            <h3 class="fw-800 text-warning mb-2">Duplicate Scan</h3>
            <p class="text-muted mb-4">{{ $message }}</p>

            <div class="p-3 mb-4 text-start" style="background: rgba(255,193,7,0.1); border-left: 4px solid var(--bs-warning); border-radius: 8px;">
                <p class="mb-1 fw-700">
                    <i class="bi bi-person me-2"></i> {{ $registration->user->name ?? 'Student' }}
                </p>
                <p class="mb-0 text-muted small">
                    <i class="bi bi-calendar-event me-2"></i> {{ $registration->event->title ?? '' }}
                </p>
            </div>

        @else
            <!-- Error Status -->
            <div class="mb-4">
                <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
            </div>
            <h3 class="fw-800 text-danger mb-2">Scan Failed</h3>
            <p class="text-muted mb-4">{{ $message }}</p>
        @endif

        <a href="{{ route('organizer.dashboard') }}" class="btn btn-nu-blue w-100 fw-700 py-2">
            <i class="bi bi-house me-2"></i>Return to Dashboard
        </a>
    </div>
</div>

<style>
body { background: #f8f9fa; }
</style>
@endsection
