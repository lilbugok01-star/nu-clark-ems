@extends('layouts.auth')
@section('title', 'Verify Your Email — National University Clark')
@section('content')

<div class="text-center mb-4">
    <h4 class="fw-900 text-nu-blue mb-2">Email Verification</h4>
    <p class="text-muted small">We've generated a 6-digit verification code to activate your account.</p>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show small rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show small rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('verification.verify') }}" method="POST">
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

    <button type="submit" class="btn btn-nu-blue w-100 fw-700 py-2.5 mb-3" style="border-radius: 8px;">
        Verify Account
    </button>
</form>

<form action="{{ route('verification.resend') }}" method="POST" class="text-center">
    @csrf
    <span class="text-muted small">Didn't receive the code?</span>
    <button type="submit" class="btn btn-link text-decoration-none small fw-600 p-0 ms-1 text-gold" style="font-size: 0.875rem;">
        Resend Code
    </button>
</form>

<!-- Sandboxed Local Mailbox Mockup (For Demo/Capstone Presentations Only) -->
<div class="position-fixed bottom-0 end-0 p-4" style="z-index: 1100;">
    <div class="toast show border-0 rounded-3 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="background: #e8eeff; border-left: 5px solid var(--nu-blue) !important; width: 340px;">
        <div class="toast-header border-0 bg-transparent text-nu-blue pt-3">
            <i class="bi bi-mailbox2 me-2 fs-5"></i>
            <strong class="me-auto">NU Clark Mock Mail Server</strong>
            <span class="badge bg-primary small">Sandbox Mode</span>
        </div>
        <div class="toast-body pb-3">
            <p class="small text-muted mb-2">Since this is running locally, we intercepted the verification email sent to <strong>{{ $email }}</strong>.</p>
            <div class="p-3 bg-white rounded-3 border text-center">
                <span class="small text-muted d-block mb-1">YOUR VERIFICATION CODE</span>
                <span class="fs-4 fw-bold text-nu-blue" style="letter-spacing: 0.1em;" id="sandboxCode">{{ $code }}</span>
                <button class="btn btn-sm btn-outline-nu-blue w-100 mt-2" onclick="navigator.clipboard.writeText('{{ $code }}'); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy to Clipboard', 2000)">
                    <i class="bi bi-clipboard me-1"></i>Copy to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
