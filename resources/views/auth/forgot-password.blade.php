@extends('layouts.auth')
@section('title', 'Forgot Password')

@section('auth-content')
<h4 class="fw-bold text-center mb-2" style="color:var(--nu-blue)">Reset Password</h4>
<p class="text-center text-muted small mb-4">Enter your email and we'll send a reset link.</p>

@if(session('success'))
    <div class="alert alert-success small">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               placeholder="your@nu-clark.edu.ph" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-nu-blue w-100 fw-bold py-2">Send Reset Link</button>
</form>
<hr class="my-3">
<p class="text-center small"><a href="{{ route('login') }}" class="text-gold text-decoration-none">← Back to login</a></p>
@endsection
