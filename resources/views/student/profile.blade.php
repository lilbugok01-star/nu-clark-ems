@extends('layouts.app')
@section('title', 'My Profile')
@section('content')
<div class="container py-5" style="max-width:700px">
    <h4 class="fw-bold mb-4" style="color:var(--nu-blue)"><i class="bi bi-person-circle me-2"></i>My Profile</h4>

    <div class="nu-card p-4 mb-4">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="stat-icon" style="background:rgba(0,48,135,0.1);width:64px;height:64px">
                <i class="bi bi-person-circle" style="color:var(--nu-blue);font-size:2rem"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                <div class="text-muted small">{{ $user->email }}</div>
                <span class="badge bg-nu-blue mt-1">{{ ucfirst($user->role) }}</span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 rounded-xl" style="background:var(--gray-100)">
                    <div class="small text-muted">Student ID</div>
                    <div class="fw-semibold">{{ $user->student_id ?? 'Not set' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded-xl" style="background:var(--gray-100)">
                    <div class="small text-muted">Course</div>
                    <div class="fw-semibold">{{ $user->course->code ?? 'Not set' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded-xl" style="background:var(--gray-100)">
                    <div class="small text-muted">Section</div>
                    <div class="fw-semibold">{{ $user->section->name ?? 'Not set' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded-xl" style="background:var(--gray-100)">
                    <div class="small text-muted">Phone</div>
                    <div class="fw-semibold">{{ $user->phone ?? 'Not set' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3">
        <div class="col-6">
            <div class="stat-card text-center">
                <div class="stat-value">{{ $stats['registered'] }}</div>
                <div class="stat-label">Events Registered</div>
            </div>
        </div>
        <div class="col-6">
            <div class="stat-card text-center" style="border-color:var(--nu-gold)">
                <div class="stat-value text-gold">{{ $stats['attended'] }}</div>
                <div class="stat-label">Events Attended</div>
            </div>
        </div>
    </div>
</div>
@endsection
