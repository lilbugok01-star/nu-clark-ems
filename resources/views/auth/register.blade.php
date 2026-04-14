@extends('layouts.auth')
@section('title', 'Create Account')
@section('content')
<div class="text-center mb-4">
    <h4 class="fw-800 mb-1" style="color:var(--nu-blue)">Create Account</h4>
    <p class="text-muted small mb-0">Join the NU Clark Event Management System</p>
</div>

@if($errors->any())
<div class="alert alert-danger rounded-3 py-2 px-3 mb-3 small">
    <i class="bi bi-exclamation-circle me-1"></i>
    <ul class="mb-0 list-unstyled">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('register') }}" method="POST" novalidate>
    @csrf

    <div class="row g-3 mb-1">
        <!-- Full Name -->
        <div class="col-12">
            <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                <input type="text" name="name" class="form-control" placeholder="e.g. Juan dela Cruz"
                       value="{{ old('name') }}" required autocomplete="name">
            </div>
        </div>

        <!-- Email -->
        <div class="col-12">
            <label class="form-label fw-600">Email Address <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control"
                       placeholder="yourname@student.nu-clark.edu.ph"
                       value="{{ old('email') }}" required autocomplete="email"
                       pattern=".+@student\.nu-clark\.edu\.ph"
                       title="Must be an official NU Clark student email ending in @student.nu-clark.edu.ph">
            </div>
            <div class="form-text" style="color:var(--nu-blue);font-size:.75rem">
                <i class="bi bi-info-circle me-1"></i>Official school email required: <strong>@student.nu-clark.edu.ph</strong>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label fw-600">Student ID <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-badge text-muted"></i></span>
                <input type="text" name="student_id" class="form-control" placeholder="YYYY-NNNNNN (e.g. 2023-190866)"
                       value="{{ old('student_id') }}" required 
                       pattern="\d{4}-\d{6}" 
                       title="Format: YYYY-NNNNNN (e.g. 2023-190866)">
            </div>
            <div class="form-text" style="font-size:.7rem"><i class="bi bi-info-circle me-1"></i>Format: <strong>YYYY-NNNNNN</strong></div>
        </div>

        <!-- Course, Year & Section -->
        <div class="col-md-4 col-12">
            <label class="form-label fw-600">Course <span class="text-danger">*</span></label>
            <select name="course_id" class="form-select" id="courseSelect" onchange="filterSections()" required>
                <option value="">— Course —</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ old('course_id') == $c->id ? 'selected' : '' }}>{{ $c->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-6">
            <label class="form-label fw-600">Year Level <span class="text-danger">*</span></label>
            <select id="yearSelect" class="form-select" onchange="filterSections()" required>
                <option value="">— Year —</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
            </select>
        </div>
        <div class="col-md-4 col-6">
            <label class="form-label fw-600">Section <span class="text-danger">*</span></label>
            <select name="section_id" class="form-select" id="sectionSelect" required>
                <option value="">— Section —</option>
                @foreach($sections as $s)
                    <option value="{{ $s->id }}" data-course="{{ $s->course_id }}" data-year="{{ $s->year_level }}" {{ old('section_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Password -->
        <div class="col-12">
            <label class="form-label fw-600">Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" id="pw1" class="form-control" placeholder="Min. 8 characters" required>
                <button type="button" class="input-group-text" onclick="togglePw('pw1','eye1')" style="cursor:pointer">
                    <i class="bi bi-eye" id="eye1" style="color:var(--gray-400)"></i>
                </button>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="col-12">
            <label class="form-label fw-600">Confirm Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
                <input type="password" name="password_confirmation" id="pw2" class="form-control" placeholder="Repeat password" required>
                <button type="button" class="input-group-text" onclick="togglePw('pw2','eye2')" style="cursor:pointer">
                    <i class="bi bi-eye" id="eye2" style="color:var(--gray-400)"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-nu-blue w-100 py-2 fw-700 mb-3">
            <i class="bi bi-person-plus me-2"></i>Create Account
        </button>
        <p class="text-center text-muted small mb-0">
            Already have an account?
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
function filterSections() {
    const courseId = document.getElementById('courseSelect').value;
    const yearLvl  = document.getElementById('yearSelect').value;
    const sel = document.getElementById('sectionSelect');
    
    // Store current value to retain it if valid
    const currentVal = sel.value;
    let keepCurrent = false;

    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return; // keep the placeholder
        
        // Show only if BOTH course and year match (if selected)
        const matchCourse = !courseId || opt.dataset.course == courseId;
        const matchYear   = !yearLvl  || opt.dataset.year == yearLvl;
        
        const show = matchCourse && matchYear;
        
        // Use inline style since hidden attribute is sometimes buggy in older browsers
        opt.style.display = show ? '' : 'none';
        opt.hidden = !show;

        if (show && opt.value === currentVal) keepCurrent = true;
    });

    if (!keepCurrent) sel.value = '';
}

// Init on load – restore old selection
window.addEventListener('load', () => {
    // Attempt to select year based on currently selected section (if any)
    const oldSecId = '{{ old('section_id') }}';
    if (oldSecId) {
        const secOpt = document.querySelector(`#sectionSelect option[value="${oldSecId}"]`);
        if (secOpt && secOpt.dataset.year) {
            document.getElementById('yearSelect').value = secOpt.dataset.year;
        }
    }
    filterSections();
});
</script>
@endpush
