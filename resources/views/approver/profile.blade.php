@extends('layouts.app')
@section('title', 'E-Signature Profile')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h4 class="mb-4" style="color:var(--nu-blue);font-weight:800"><i class="bi bi-pen"></i> E-Signature Profile</h4>
            
            <div class="card border-0 shadow-sm" style="border-radius:12px">
                <div class="card-body p-4">
                    <form action="{{ route('approver.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Upload E-Signature</label>
                            @if($user->e_signature_path)
                                <div class="mb-2">
                                    <div class="border p-2 rounded text-center bg-light">
                                        <img src="{{ asset('storage/' . $user->e_signature_path) }}" alt="Current Signature" style="max-height:80px">
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning mb-2 py-2">
                                    <i class="bi bi-exclamation-triangle-fill"></i> You have not uploaded a signature yet. You cannot approve events.
                                </div>
                            @endif
                            <input type="file" name="e_signature" class="form-control" accept="image/png, image/jpeg" required>
                            <small class="text-muted">Clear image with white or transparent background recommended.</small>
                        </div>
                        <button class="btn btn-gold w-100 fw-bold">Save Signature</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
