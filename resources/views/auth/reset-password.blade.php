@extends('layouts.auth')
@section('title', 'Set New Password — National University Clark')
@section('content')

<div class="text-center mb-4">
    <h4 class="fw-900 text-nu-blue mb-2">Set New Password</h4>
    <p class="text-muted small">Provide your student ID and enter a strong new password.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show small rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}" novalidate>
    @csrf
    
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <!-- Email (Read-Only Info) -->
    <div class="mb-3">
        <label class="form-label fw-600 text-nu-blue small">Email Address</label>
        <input type="text" class="form-control bg-light" value="{{ $email }}" readonly style="border-radius: 8px;">
    </div>

    <!-- Student ID -->
    <div class="mb-3">
        <label for="student_id" class="form-label fw-600 text-nu-blue small">Student ID <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white" style="border-right: none; border-radius: 8px 0 0 8px;"><i class="bi bi-person-badge text-muted"></i></span>
            <input type="text" name="student_id" id="student_id" class="form-control py-2 @error('student_id') is-invalid @enderror"
                   placeholder="e.g. 2022-00001" value="{{ old('student_id') }}" required style="border-left: none; border-radius: 0 8px 8px 0; border: 1px solid var(--gray-200);">
        </div>
        @error('student_id')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <!-- New Password -->
    <div class="mb-3">
        <label for="password" class="form-label fw-600 text-nu-blue small">New Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white" style="border-right: none; border-radius: 8px 0 0 8px;"><i class="bi bi-lock text-muted"></i></span>
            <input type="password" name="password" id="password" class="form-control py-2 @error('password') is-invalid @enderror" 
                   placeholder="Min. 8 chars, 1 uppercase, 1 number, 1 symbol" required style="border-left: none; border-radius: 0; border: 1px solid var(--gray-200);">
            <button type="button" class="input-group-text bg-white" onclick="togglePw('password','eye1')" style="cursor:pointer; border-radius: 0 8px 8px 0;">
                <i class="bi bi-eye text-muted" id="eye1"></i>
            </button>
        </div>
        <div class="form-text text-muted small">Must contain at least 1 uppercase letter, 1 number, and 1 special character.</div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <!-- Confirm New Password -->
    <div class="mb-4">
        <label for="password_confirmation" class="form-label fw-600 text-nu-blue small">Confirm New Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white" style="border-right: none; border-radius: 8px 0 0 8px;"><i class="bi bi-lock-fill text-muted"></i></span>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control py-2" 
                   placeholder="Repeat new password" required style="border-left: none; border-radius: 0; border: 1px solid var(--gray-200);">
            <button type="button" class="input-group-text bg-white" onclick="togglePw('password_confirmation','eye2')" style="cursor:pointer; border-radius: 0 8px 8px 0;">
                <i class="bi bi-eye text-muted" id="eye2"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-nu-blue w-100 py-2.5 fw-700 mb-3" style="border-radius: 8px;">
        <i class="bi bi-shield-check me-2"></i>Update Password
    </button>
</form>

@endsection
@push('scripts')
<script>
function togglePw(id, eyeId) {
    const i = document.getElementById(id);
    const e = document.getElementById(eyeId);
    i.type = i.type === 'password' ? 'text' : 'password';
    e.className = i.type === 'password' ? 'bi bi-eye text-muted' : 'bi bi-eye-slash text-muted';
}
</script>
@endpush
