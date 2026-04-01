@extends('layouts.app')
@section('title', 'Import Students from CSV')
@section('content')
<div class="container py-5">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h4 class="fw-800 mb-0" style="color:var(--nu-blue)">
                <i class="bi bi-file-earmark-spreadsheet me-2" style="color:var(--nu-gold)"></i>Import NU Clark Students
            </h4>
            <p class="text-muted small mb-0">Bulk-import existing students from a CSV file — skips duplicates automatically</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert rounded-3 mb-4 d-flex gap-2" style="background:#dcfce7;border:1px solid #86efac;color:#166534">
        <i class="bi bi-check-circle-fill mt-1 flex-shrink-0"></i>
        <div>
            {{ session('success') }}
            @if(session('import_errors'))
            <ul class="small mt-2 mb-0 ps-3">
                @foreach(session('import_errors') as $ie)<li>{{ $ie }}</li>@endforeach
            </ul>
            @endif
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger rounded-3 mb-4">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <ul class="mb-0 mt-1 small list-unstyled">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="row g-4">
        <!-- Import Form -->
        <div class="col-lg-8">

            <!-- Step 1: Download Template -->
            <div class="nu-card mb-4">
                <div class="p-4 border-bottom">
                    <h6 class="fw-700 mb-0">
                        <span class="badge me-2" style="background:var(--nu-gold);color:var(--nu-blue)">1</span>
                        Download the CSV Template
                    </h6>
                </div>
                <div class="p-4">
                    <p class="text-muted small mb-3">
                        Download and fill in the CSV template with your student data. Follow the column format exactly.
                    </p>
                    <div class="upload-area mb-3" style="cursor:default">
                        <table class="table table-sm mb-0" style="font-size:.78rem">
                            <thead>
                                <tr style="background:var(--nu-blue);color:#fff">
                                    <th class="fw-600">name</th>
                                    <th class="fw-600">email</th>
                                    <th class="fw-600">student_id</th>
                                    <th class="fw-600">course_code</th>
                                    <th class="fw-600">section_name</th>
                                    <th class="fw-600">password (opt)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Juan dela Cruz</td>
                                    <td>juan@nu-clark.edu.ph</td>
                                    <td>2022-00001</td>
                                    <td>BSIT</td>
                                    <td>BSIT-3A</td>
                                    <td class="text-muted">(blank → default)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('admin.import.template') }}" class="btn btn-success btn-sm px-3 fw-600">
                        <i class="bi bi-download me-1"></i>Download Template (CSV)
                    </a>
                    <div class="mt-3 small text-muted">
                        <i class="bi bi-info-circle me-1 text-nu-blue"></i>
                        <strong>course_code</strong> must match an existing course code (e.g. BSIT, BSCS).
                        <strong>section_name</strong> must match an existing section name (e.g. BSIT-3A).
                        Mismatched values will be saved with no course/section assigned.
                    </div>
                </div>
            </div>

            <!-- Step 2: Upload & Import -->
            <div class="nu-card">
                <div class="p-4 border-bottom">
                    <h6 class="fw-700 mb-0">
                        <span class="badge me-2" style="background:var(--nu-gold);color:var(--nu-blue)">2</span>
                        Upload Your CSV & Import
                    </h6>
                </div>
                <form action="{{ route('admin.import.run') }}" method="POST" enctype="multipart/form-data" class="p-4">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Select CSV File <span class="text-danger">*</span></label>
                        <div class="upload-area" id="dropZone" onclick="document.getElementById('csvFile').click()">
                            <i class="bi bi-file-earmark-spreadsheet mb-2" style="font-size:2.5rem;color:var(--nu-blue);opacity:.6"></i>
                            <p class="text-muted small mb-1">Click to choose or drag and drop your CSV file here</p>
                            <p class="text-muted" style="font-size:.72rem">Accepted: .csv · Max 5 MB</p>
                            <div id="fileNameDisplay" class="mt-2 fw-600 small d-none" style="color:var(--nu-blue)"></div>
                        </div>
                        <input type="file" id="csvFile" name="csv_file" accept=".csv,text/csv" class="d-none" onchange="showFileName(this)" required>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Default Password <span class="text-danger">*</span></label>
                            <input type="text" name="default_password" class="form-control" value="NuClark2024!" required>
                            <div class="form-text text-muted">Used when no password column in CSV.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="default_role" class="form-select">
                                <option value="student" selected>Student</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="skip_errors" id="skipErrors" value="1" checked>
                            <label class="form-check-label small fw-600" for="skipErrors">
                                Skip rows with errors / duplicates (recommended)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-nu-blue px-4 fw-700">
                            <i class="bi bi-upload me-2"></i>Import Students
                        </button>
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Stats -->
            <div class="nu-card p-4 mb-4">
                <h6 class="fw-700 mb-3" style="color:var(--nu-blue)"><i class="bi bi-bar-chart me-2" style="color:var(--nu-gold)"></i>Current Stats</h6>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:rgba(0,48,135,.1)">
                        <i class="bi bi-people" style="color:var(--nu-blue)"></i>
                    </div>
                    <div>
                        <div class="fw-900 lh-1" style="font-size:1.8rem;color:var(--nu-blue)">{{ $stats['total_students'] }}</div>
                        <div class="text-muted small">Students in DB</div>
                    </div>
                </div>
            </div>

            <!-- Rules -->
            <div class="nu-card p-4 mb-4">
                <h6 class="fw-700 mb-3" style="color:var(--nu-blue)"><i class="bi bi-shield-check me-2" style="color:var(--nu-gold)"></i>Import Rules</h6>
                <ul class="list-unstyled small text-muted mb-0">
                    @foreach(['Duplicate emails are skipped automatically','Duplicate student IDs are skipped automatically','Unrecognized course/section codes are allowed (no course assigned)','Passwords in CSV override the default password','name and email columns are required','All imported users are set to role: student','Maximum file size: 5 MB','All passwords are hashed — never stored as plain text'] as $rule)
                    <li class="mb-2 d-flex gap-2">
                        <i class="bi bi-check2 text-success mt-1 flex-shrink-0"></i>
                        {{ $rule }}
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Available Courses -->
            <div class="nu-card p-4">
                <h6 class="fw-700 mb-3" style="color:var(--nu-blue)"><i class="bi bi-mortarboard me-2" style="color:var(--nu-gold)"></i>Available Course Codes</h6>
                @if($courses->count())
                <div class="d-flex flex-wrap gap-1">
                    @foreach($courses as $c)
                    <span class="badge-category">{{ $c->code }}</span>
                    @endforeach
                </div>
                @else
                <p class="text-muted small mb-0">No courses found. Add courses in <a href="{{ route('admin.courses') }}">Course Management</a>.</p>
                @endif
                @if($sections->count())
                <p class="small text-muted mt-3 mb-1 fw-600">Available Sections:</p>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($sections->take(20) as $s)
                    <span class="venue-badge">{{ $s->name }}</span>
                    @endforeach
                    @if($sections->count() > 20)
                    <span class="text-muted small">+{{ $sections->count() - 20 }} more</span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function showFileName(input) {
    const el = document.getElementById('fileNameDisplay');
    if (input.files.length) {
        const size = (input.files[0].size / 1024).toFixed(0);
        el.textContent = `📄 ${input.files[0].name} (${size} KB)`;
        el.classList.remove('d-none');
    }
}

// Drag and drop
const dropZone = document.getElementById('dropZone');
['dragenter','dragover'].forEach(e => dropZone.addEventListener(e, ev => {
    ev.preventDefault(); dropZone.classList.add('dragover');
}));
['dragleave','drop'].forEach(e => dropZone.addEventListener(e, ev => {
    dropZone.classList.remove('dragover');
}));
dropZone.addEventListener('drop', ev => {
    ev.preventDefault();
    const csvInput = document.getElementById('csvFile');
    csvInput.files = ev.dataTransfer.files;
    showFileName(csvInput);
});
</script>
@endpush
