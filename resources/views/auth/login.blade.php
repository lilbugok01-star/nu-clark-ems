@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')
<div class="text-center mb-4">
    <h4 class="fw-800 mb-1" style="color:var(--nu-blue)">Welcome back!</h4>
    <p class="text-muted small mb-0">Sign in to your NU Clark EMS account</p>
</div>

@if($errors->any())
<div class="alert alert-danger rounded-3 py-2 px-3 mb-3 small">
    <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
</div>
@endif

<form action="{{ route('login') }}" method="POST" autocomplete="on" novalidate>
    @csrf

    <!-- Email -->
    <div class="mb-3">
        <label class="form-label fw-600">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   placeholder="your@email.com" value="{{ old('email') }}" autocomplete="email" required>
        </div>
    </div>

    <!-- Password -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0 fw-600">Password</label>
            <a href="{{ route('password.request') }}" class="small" style="color:var(--nu-blue)">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   id="passwordInput" placeholder="••••••••" autocomplete="current-password" required>
            <button type="button" class="input-group-text" onclick="togglePw()" style="cursor:pointer" title="Show/Hide">
                <i class="bi bi-eye" id="pwEye" style="color:var(--gray-400)"></i>
            </button>
        </div>
    </div>

    <!-- Remember + Submit -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
            <label class="form-check-label small" for="rememberMe">Keep me signed in</label>
        </div>
    </div>

    <button type="submit" class="btn btn-nu-blue w-100 py-2 fw-700 mb-3">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
    </button>

    <p class="text-center text-muted small mb-0">
        Don't have an account?
        <a href="{{ route('register') }}" class="fw-600" style="color:var(--nu-blue)">Create one →</a>
    </p>
</form>
@endsection
@push('scripts')
<script>
function togglePw() {
    const i = document.getElementById('passwordInput');
    const e = document.getElementById('pwEye');
    i.type = i.type === 'password' ? 'text' : 'password';
    e.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
@endpush
