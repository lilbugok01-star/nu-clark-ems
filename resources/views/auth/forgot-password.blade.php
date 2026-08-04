@extends('layouts.auth')
@section('title', 'Forgot Password — National University Clark')
@section('content')

<div class="text-center mb-4">
    <h4 class="fw-900 text-nu-blue mb-2">Forgot Password</h4>
    <p class="text-muted small">Enter your official student email to receive a password reset link.</p>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show small rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show small rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" novalidate>
    @csrf

    <!-- Email -->
    <div class="mb-4">
        <label for="email" class="form-label fw-600 text-nu-blue small">Email Address <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white" style="border-right: none; border-radius: 8px 0 0 8px;"><i class="bi bi-envelope text-muted"></i></span>
            <input type="email" name="email" id="email" class="form-control py-2 @error('email') is-invalid @enderror"
                   placeholder="your@nu-clark.edu.ph" value="{{ old('email') }}" required autocomplete="email"
                   style="border-left: none; border-radius: 0 8px 8px 0; border: 1px solid var(--gray-200);">
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-nu-blue w-100 py-2.5 fw-700 mb-3" style="border-radius: 8px;">
            <i class="bi bi-send-fill me-2"></i>Send Reset Link
        </button>
        <p class="text-center text-muted small mb-0">
            Remember your password?
            <a href="{{ route('login') }}" class="fw-600 text-gold text-decoration-none">Sign in →</a>
        </p>
    </div>
</form>

<!-- Sandboxed Local Mailbox Mockup (For Demo/Capstone Presentations Only) -->
@if(session('reset_link'))
<div class="position-fixed bottom-0 end-0 p-4" style="z-index: 1100;">
    <div class="toast show border-0 rounded-3 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="background: #e8eeff; border-left: 5px solid var(--nu-blue) !important; width: 350px;">
        <div class="toast-header border-0 bg-transparent text-nu-blue pt-3">
            <i class="bi bi-mailbox2 me-2 fs-5"></i>
            <strong class="me-auto">NU Clark Mock Mail Server</strong>
            <span class="badge bg-primary small">Sandbox Mode</span>
        </div>
        <div class="toast-body pb-3">
            <p class="small text-muted mb-2">Since this is running locally, we intercepted the password reset email.</p>
            <div class="p-3 bg-white rounded-3 border">
                <span class="small text-muted d-block mb-1 text-center">PASSWORD RESET LINK</span>
                <a href="{{ session('reset_link') }}" class="btn btn-sm btn-nu-blue w-100 mb-2">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Open Reset Link
                </a>
                <button class="btn btn-sm btn-outline-secondary w-100" onclick="navigator.clipboard.writeText('{{ session('reset_link') }}'); this.innerText='Link Copied!'; setTimeout(() => this.innerText='Copy Reset URL', 2000)">
                    <i class="bi bi-clipboard me-1"></i>Copy Reset URL
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
