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

@if(session('error'))
    <div class="alert alert-warning alert-dismissible fade show small rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
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
                   placeholder="your@students.nu-clark.edu.ph" value="{{ old('email') }}" required autocomplete="email"
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

@if(session('success'))
<div class="text-center mt-3">
    <p class="text-muted small mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Check your <strong>Outlook inbox</strong> and <strong>spam/junk folder</strong> for the reset email from <em>NU Clark Events</em>.
    </p>
</div>
@endif

@endsection
