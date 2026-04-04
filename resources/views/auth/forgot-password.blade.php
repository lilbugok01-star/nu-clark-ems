@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')
<div class="text-center mb-4">
    <h4 class="fw-800 mb-1" style="color:var(--nu-blue)">Reset Password</h4>
    <p class="text-muted small mb-0">Verify your identity to set a new password</p>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3 py-2 px-3 mb-3 small">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
    </div>
@endif

@if($errors->any())
<div class="alert alert-danger rounded-3 py-2 px-3 mb-3 small">
    <i class="bi bi-exclamation-circle me-1"></i>
    <ul class="mb-0 list-unstyled">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('password.email') }}" novalidate>
    @csrf

    <!-- Email -->
    <div class="mb-3">
        <label class="form-label fw-600">Email Address <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
            <input type="email" name="email" class="form-control"
                   placeholder="your@nu-clark.edu.ph" value="{{ old('email') }}" required autocomplete="email">
        </div>
    </div>

    <!-- Student ID -->
    <div class="mb-3">
        <label class="form-label fw-600">Student ID <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-badge text-muted"></i></span>
            <input type="text" name="student_id" class="form-control"
                   placeholder="e.g. 2022-00001" value="{{ old('student_id') }}" required>
        </div>
        <small class="text-muted">Enter the Student ID linked to your account for verification.</small>
    </div>

    <!-- New Password -->
    <div class="mb-3">
        <label class="form-label fw-600">New Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
            <input type="password" name="password" id="pw1" class="form-control" placeholder="Min. 8 characters" required>
            <button type="button" class="input-group-text" onclick="togglePw('pw1','eye1')" style="cursor:pointer">
                <i class="bi bi-eye" id="eye1" style="color:var(--gray-400)"></i>
            </button>
        </div>
    </div>

    <!-- Confirm New Password -->
    <div class="mb-3">
        <label class="form-label fw-600">Confirm New Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
            <input type="password" name="password_confirmation" id="pw2" class="form-control" placeholder="Repeat new password" required>
            <button type="button" class="input-group-text" onclick="togglePw('pw2','eye2')" style="cursor:pointer">
                <i class="bi bi-eye" id="eye2" style="color:var(--gray-400)"></i>
            </button>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-nu-blue w-100 py-2 fw-700 mb-3">
            <i class="bi bi-shield-check me-2"></i>Reset Password
        </button>
        <p class="text-center text-muted small mb-0">
            Remember your password?
            <a href="{{ route('login') }}" class="fw-600" style="color:var(--nu-blue)">Sign in →</a>
        </p>
    </div>
</form>
@endsection
@push('scripts')
<script>
function togglePw(id, eyeId) {
    const i = document.getElementById(id);
    const e = document.getElementById(eyeId);
    i.type = i.type === 'password' ? 'text' : 'password';
    e.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
@endpush
