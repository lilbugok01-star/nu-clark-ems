@extends('layouts.auth')
@section('title', 'Verify Your Email — National University Clark')
@section('content')

<div class="text-center mb-4">
    <h4 class="fw-900 text-nu-blue mb-2">Email Verification</h4>
    <p class="text-muted small">We've sent a 6-digit verification code to <strong>{{ $email }}</strong>. Please check your inbox (and spam folder) and enter the code below.</p>
</div>

@if(isset($codeHint) && $codeHint)
    <div class="alert alert-info border-info text-dark small rounded-3 mb-4 text-center">
        <i class="bi bi-info-circle-fill text-info me-1"></i>
        <strong>Verification Code (Mail Log Mode):</strong>
        <div class="fs-4 fw-bold text-nu-blue my-1" style="letter-spacing:0.25em">{{ $codeHint }}</div>
        <div class="text-muted" style="font-size:0.75rem">Enter this code below to complete your registration.</div>
    </div>
@endif

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
        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('verification.verify') }}" method="POST" id="verifyForm">
    @csrf
    
    <div class="mb-4">
        <label for="code" class="form-label fw-600 text-nu-blue small">6-Digit Verification Code</label>
        <input type="text" 
               class="form-control text-center fw-bold fs-4 py-2 @error('code') is-invalid @enderror" 
               id="code" 
               name="code" 
               maxlength="6" 
               placeholder="000000" 
               required 
               autocomplete="off"
               style="letter-spacing: 0.3em; border-radius: 8px; border: 2px solid var(--gray-200);">
        @error('code')
            <div class="invalid-feedback text-start">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" id="verifyBtn" class="btn btn-nu-blue w-100 fw-700 py-2.5 mb-3" style="border-radius: 8px;">
        <span id="verifyText">Verify Account</span>
        <span id="verifySpinner" class="d-none"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Verifying...</span>
    </button>
</form>

<form action="{{ route('verification.resend') }}" method="POST" class="text-center" id="resendForm">
    @csrf
    <span class="text-muted small">Didn't receive the code?</span>
    <button type="submit" id="resendBtn" class="btn btn-link text-decoration-none small fw-600 p-0 ms-1 text-gold" style="font-size: 0.875rem;">
        <span id="resendText">Resend Code</span>
        <span id="resendSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-1" role="status"></span>Sending email...</span>
    </button>
</form>

<script>
document.getElementById('verifyForm').addEventListener('submit', function() {
    var btn = document.getElementById('verifyBtn');
    document.getElementById('verifyText').classList.add('d-none');
    document.getElementById('verifySpinner').classList.remove('d-none');
    btn.style.pointerEvents = 'none';
    setTimeout(function() { btn.disabled = true; }, 50);
});
document.getElementById('resendForm').addEventListener('submit', function() {
    var btn = document.getElementById('resendBtn');
    document.getElementById('resendText').classList.add('d-none');
    document.getElementById('resendSpinner').classList.remove('d-none');
    btn.style.pointerEvents = 'none';
    setTimeout(function() { btn.disabled = true; }, 50);
});
</script>

<div class="text-center mt-3">
    <p class="text-muted small mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Check your <strong>Outlook inbox</strong> and <strong>spam/junk folder</strong> for the verification email from <em>NU Clark Events</em>.
    </p>
</div>

@endsection
