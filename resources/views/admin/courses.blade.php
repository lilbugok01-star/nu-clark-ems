@extends('layouts.app')
@section('title', 'Courses & Sections')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 col-md-3">
            @include('layouts.partials.sidebar-admin')
        </div>
        <div class="col-lg-10 col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0" style="color:var(--nu-blue)"><i class="bi bi-book me-2"></i>Courses & Sections</h4>
                <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                    <i class="bi bi-plus me-1"></i>Add Course
                </button>
            </div>

            <div class="row g-4">
                @foreach($courses as $course)
                <div class="col-md-6 col-lg-4">
                    <div class="nu-card p-4">
                        <h6 class="fw-bold mb-1" style="color:var(--nu-blue)">{{ $course->code }}</h6>
                        <p class="text-muted small mb-3">{{ $course->name }}</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($course->sections as $sec)
                                <span class="badge bg-light text-dark border">{{ $sec->name }}</span>
                            @endforeach
                            @if($course->sections->isEmpty())
                                <span class="text-muted small">No sections yet.</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--nu-blue)">
                <h5 class="modal-title text-white">Add Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.courses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Course Code</label><input type="text" name="code" class="form-control" required placeholder="e.g. BSIT"></div>
                    <div class="mb-3"><label class="form-label">Course Name</label><input type="text" name="name" class="form-control" required placeholder="Full course name..."></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-nu-blue">Add Course</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
