@extends('layouts.app')
@section('title', 'User Management')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 col-md-3">
            @include('layouts.partials.sidebar-admin')
        </div>
        <div class="col-lg-10 col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0" style="color:var(--nu-blue)"><i class="bi bi-people me-2"></i>User Management</h4>
                <button class="btn btn-gold fw-700" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus me-1"></i>Add User
                </button>
            </div>

            <!-- Filters -->
            <form class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin"              {{ request('role')=='admin'              ? 'selected':'' }}>Admin</option>
                        <option value="organizer"          {{ request('role')=='organizer'          ? 'selected':'' }}>Organizer</option>
                        <option value="student"            {{ request('role')=='student'            ? 'selected':'' }}>Student</option>
                        <option value="student_department" {{ request('role')=='student_department' ? 'selected':'' }}>Student Department</option>
                        <option value="adviser"            {{ request('role')=='adviser'            ? 'selected':'' }}>Adviser</option>
                        <option value="program_chair"      {{ request('role')=='program_chair'      ? 'selected':'' }}>Program Chair</option>
                        <option value="department_head"    {{ request('role')=='department_head'    ? 'selected':'' }}>Department Head</option>
                        <option value="dean"               {{ request('role')=='dean'               ? 'selected':'' }}>Dean</option>
                        <option value="student_development"{{ request('role')=='student_development'? 'selected':'' }}>Student Development</option>
                        <option value="executive_director" {{ request('role')=='executive_director' ? 'selected':'' }}>Executive Director</option>
                    </select>
                </div>
                <div class="col-md-3"><button class="btn btn-nu-blue w-100">Filter</button></div>
            </form>

            <div class="nu-card">
                <div class="table-responsive">
                <table class="table nu-table mb-0">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Course</th><th>Student ID</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="fw-semibold small">{{ $user->full_name }}</td>
                            <td class="small text-muted">{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleBg = match($user->role) {
                                        'admin'              => 'background:var(--nu-blue);color:#fff',
                                        'organizer'         => 'background:var(--nu-gold);color:var(--nu-blue)',
                                        'student'           => 'background:#6c757d;color:#fff',
                                        'student_department'=> 'background:#0d6efd;color:#fff',
                                        'adviser'           => 'background:#198754;color:#fff',
                                        'program_chair'     => 'background:#0dcaf0;color:#000',
                                        'department_head'   => 'background:#6f42c1;color:#fff',
                                        'dean'              => 'background:#fd7e14;color:#fff',
                                        'student_development'=>'background:#20c997;color:#fff',
                                        'executive_director'=> 'background:#dc3545;color:#fff',
                                        default             => 'background:#6c757d;color:#fff',
                                    };
                                    $roleLabel = match($user->role) {
                                        'student_department' => 'Student Dept',
                                        'student_development'=> 'Student Dev',
                                        'executive_director' => 'Exec Director',
                                        'department_head'    => 'Dept Head',
                                        'program_chair'      => 'Program Chair',
                                        default              => ucfirst($user->role),
                                    };
                                @endphp
                                <span class="badge" style="{{ $roleBg }};font-size:0.68rem">{{ $roleLabel }}</span>
                            </td>
                            <td class="small">{{ $user->course->code ?? '-' }}</td>
                            <td class="small text-muted">{{ $user->student_id ?? '-' }}</td>
                            <td>
                                <span class="status-dot {{ $user->is_active ? 'bg-success' : 'bg-danger' }}"></span> {{ $user->is_active ? 'Active' : 'Inactive' }}
                                @if($user->isStudent())
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success-subtle text-success border border-success ms-1" style="font-size:0.65rem">Verified</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-dark border border-warning ms-1" style="font-size:0.65rem" title="Code: {{ $user->email_verification_code }}">Unverified</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if($user->isStudent() && !$user->email_verified_at)
                                    <form action="{{ route('admin.users.verify-email', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Manually verify email for {{ $user->full_name }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Verify Email (Code: {{ $user->email_verification_code ?? 'N/A' }})"><i class="bi bi-patch-check"></i></button>
                                    </form>
                                    @endif
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal"
                                            data-id="{{ $user->id }}" data-first-name="{{ $user->first_name }}" data-middle-name="{{ $user->middle_name }}" data-surname="{{ $user->surname }}" data-role="{{ $user->role }}"
                                            data-active="{{ $user->is_active ? '1' : '0' }}"
                                            data-student-id="{{ $user->student_id }}"
                                            data-course-id="{{ $user->course_id }}"
                                            data-section-id="{{ $user->section_id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            <div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden">
            <!-- Modal Header -->
            <div class="modal-header px-4 py-3 text-white border-0" style="background:linear-gradient(135deg, #001d50 0%, #003087 100%)">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:36px;height:36px;background:rgba(255,184,0,0.18)">
                        <i class="bi bi-person-plus-fill" style="color:var(--nu-gold);font-size:1.1rem"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 mb-0" id="addUserModalLabel" style="font-size:1.1rem;letter-spacing:-0.01em">Add New User</h5>
                        <div class="text-white-50" style="font-size:0.75rem">Create a new institutional user account</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" id="addUserForm" novalidate>
                @csrf
                <div class="modal-body p-4" style="background:#fafafa">
                    <div class="row g-3">
                        <!-- First Name -->
                        <div class="col-md-4">
                            <label class="form-label fw-700 small text-dark mb-1">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control form-control-lg fs-6" required placeholder="e.g. Juan" style="border-radius:10px;border:1px solid #cbd5e1">
                        </div>

                        <!-- Middle Name -->
                        <div class="col-md-4">
                            <label class="form-label fw-700 small text-dark mb-1">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control form-control-lg fs-6" placeholder="e.g. dela" style="border-radius:10px;border:1px solid #cbd5e1">
                        </div>

                        <!-- Surname -->
                        <div class="col-md-4">
                            <label class="form-label fw-700 small text-dark mb-1">Surname <span class="text-danger">*</span></label>
                            <input type="text" name="surname" class="form-control form-control-lg fs-6" required placeholder="e.g. Cruz" style="border-radius:10px;border:1px solid #cbd5e1">
                        </div>

                        <!-- Email Address -->
                        <div class="col-md-6">
                            <label class="form-label fw-700 small text-dark mb-1">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-lg fs-6" required placeholder="user@students.nu-clark.edu.ph" style="border-radius:10px;border:1px solid #cbd5e1">
                        </div>

                        <!-- Password (No static comment; dynamic feedback on typing) -->
                        <div class="col-md-6 position-relative">
                            <label class="form-label fw-700 small text-dark mb-1">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="addUserPassword" class="form-control form-control-lg fs-6" required placeholder="Enter password" style="border-top-left-radius:10px;border-bottom-left-radius:10px;border:1px solid #cbd5e1">
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" style="border-top-right-radius:10px;border-bottom-right-radius:10px;border:1px solid #cbd5e1;border-left:none">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>

                            <!-- Dynamic Password Validation Feedback Box (Hidden by default) -->
                            <div id="passwordValidationBox" class="mt-2 p-2.5 rounded-3 shadow-sm" style="display:none;background:#fff;border:1px solid #e2e8f0;font-size:0.78rem">
                                <div class="fw-700 mb-1 text-secondary" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.04em">Password Requirements:</div>
                                <div class="d-flex flex-column gap-1">
                                    <div id="reqMinChar" class="d-flex align-items-center gap-1.5 text-danger">
                                        <i class="bi bi-x-circle-fill"></i> At least 8 characters
                                    </div>
                                    <div id="reqUpper" class="d-flex align-items-center gap-1.5 text-danger">
                                        <i class="bi bi-x-circle-fill"></i> At least 1 uppercase letter (A-Z)
                                    </div>
                                    <div id="reqNumber" class="d-flex align-items-center gap-1.5 text-danger">
                                        <i class="bi bi-x-circle-fill"></i> At least 1 number (0-9)
                                    </div>
                                    <div id="reqSymbol" class="d-flex align-items-center gap-1.5 text-danger">
                                        <i class="bi bi-x-circle-fill"></i> At least 1 special character (!@#$%^&*)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Selection -->
                        <div class="col-md-12">
                            <label class="form-label fw-700 small text-dark mb-1">Role <span class="text-danger">*</span></label>
                            <select name="role" id="addUserRole" class="form-select form-select-lg fs-6" required onchange="handleRoleChange(this.value, 'add')" style="border-radius:10px;border:1px solid #cbd5e1">
                                <option value="student">Student</option>
                                <option value="organizer">Organizer</option>
                                <option value="student_department">Student Department</option>
                                <option value="adviser">Adviser</option>
                                <option value="program_chair">Program Chair</option>
                                <option value="department_head">Department Head</option>
                                <option value="dean">Dean</option>
                                <option value="student_development">Student Development Officer</option>
                                <option value="executive_director">Executive Director</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <!-- Student-only fields container -->
                        <div id="addStudentFields" class="col-12">
                            <div class="p-3.5 rounded-3 shadow-sm" style="background:#ffffff;border:1px solid #e2e8f0;border-left:4px solid var(--nu-blue)">
                                <div class="d-flex align-items-center gap-2 fw-700 small mb-3" style="color:var(--nu-blue);font-size:0.85rem">
                                    <i class="bi bi-mortarboard-fill" style="font-size:1rem"></i> Student Academic Information
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-700 small text-dark mb-1">Student ID <span class="text-danger">*</span></label>
                                        <input type="text" name="student_id" id="addStudentId" class="form-control fs-6" placeholder="e.g. 2022-00001" style="border-radius:8px;border:1px solid #cbd5e1">
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <label class="form-label fw-700 small text-dark mb-1">Course <span class="text-danger">*</span></label>
                                        <select name="course_id" id="addCourseSelect" class="form-select fs-6" onchange="filterAdminSections()" style="border-radius:8px;border:1px solid #cbd5e1">
                                            <option value="">— Course —</option>
                                            @foreach($courses as $c)
                                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <label class="form-label fw-700 small text-dark mb-1">Year Level <span class="text-danger">*</span></label>
                                        <select id="addYearSelect" class="form-select fs-6" onchange="filterAdminSections()" style="border-radius:8px;border:1px solid #cbd5e1">
                                            <option value="">— Year —</option>
                                            <option value="1">1st Year</option>
                                            <option value="2">2nd Year</option>
                                            <option value="3">3rd Year</option>
                                            <option value="4">4th Year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <label class="form-label fw-700 small text-dark mb-1">Section <span class="text-danger">*</span></label>
                                        <select name="section_id" id="addSectionSelect" class="form-select fs-6" style="border-radius:8px;border:1px solid #cbd5e1">
                                            <option value="">— Section —</option>
                                            @foreach($sections as $s)
                                                <option value="{{ $s->id }}" data-course="{{ $s->course_id }}" data-year="{{ $s->year_level }}">{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signature upload for approver roles -->
                        <div id="addSignatureField" class="col-12" style="display:none">
                            <div class="p-3.5 rounded-3 shadow-sm" style="background:#ffffff;border:1px solid #fef3c7;border-left:4px solid var(--nu-gold)">
                                <div class="d-flex align-items-center gap-2 fw-700 small mb-2" style="color:var(--nu-blue);font-size:0.85rem">
                                    <i class="bi bi-pen-fill" style="color:var(--nu-gold)"></i> E-Signature (Optional)
                                </div>
                                <input type="file" name="e_signature" class="form-control fs-6" accept="image/*" style="border-radius:8px">
                                <small class="text-muted" style="font-size:0.75rem">Upload a signature image (PNG/JPG). Used on permission forms.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer px-4 py-3 border-top" style="background:#ffffff;border-top:1px solid #e2e8f0 !important">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-600 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-nu-blue px-4 py-2 fw-800 rounded-pill shadow-sm" style="background:var(--nu-blue);color:#fff">
                        <i class="bi bi-person-plus-fill me-1" style="color:var(--nu-gold)"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden">
            <div class="modal-header px-4 py-3 text-white border-0" style="background:linear-gradient(135deg, #001d50 0%, #003087 100%)">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:36px;height:36px;background:rgba(255,184,0,0.18)">
                        <i class="bi bi-pencil-square" style="color:var(--nu-gold);font-size:1.1rem"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 mb-0" style="font-size:1.1rem">Edit User Account</h5>
                        <div class="text-white-50" style="font-size:0.75rem">Update user information and status</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4" style="background:#fafafa">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-700 small text-dark mb-1">First Name</label>
                            <input type="text" name="first_name" id="editFirstName" class="form-control fs-6" style="border-radius:8px">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-700 small text-dark mb-1">Middle Name</label>
                            <input type="text" name="middle_name" id="editMiddleName" class="form-control fs-6" style="border-radius:8px">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-700 small text-dark mb-1">Surname</label>
                            <input type="text" name="surname" id="editSurname" class="form-control fs-6" style="border-radius:8px">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700 small text-dark mb-1">Role</label>
                        <select name="role" id="editRole" class="form-select fs-6" style="border-radius:8px">
                            <option value="student">Student</option>
                            <option value="organizer">Organizer</option>
                            <option value="student_department">Student Department</option>
                            <option value="adviser">Adviser</option>
                            <option value="program_chair">Program Chair</option>
                            <option value="department_head">Department Head</option>
                            <option value="dean">Dean</option>
                            <option value="student_development">Student Development Officer</option>
                            <option value="executive_director">Executive Director</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="p-3 rounded-3 shadow-sm bg-white border d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-700 small text-dark">Account Active Status</div>
                            <div class="text-muted" style="font-size:0.75rem">Allow or prevent account access</div>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editActive" value="1" style="width:2.5em;height:1.25em;cursor:pointer">
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 border-top" style="background:#ffffff">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-600 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-nu-blue px-4 py-2 fw-800 rounded-pill shadow-sm" style="background:var(--nu-blue);color:#fff">
                        <i class="bi bi-save-fill me-1" style="color:var(--nu-gold)"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Student roles that require course/section fields
const STUDENT_ROLES = ['student'];
// Approver/staff roles that get a signature field
const APPROVER_ROLES = ['adviser','program_chair','department_head','dean','student_development','executive_director','student_department','organizer'];

function handleRoleChange(role, context) {
    if (context === 'add') {
        const sf = document.getElementById('addStudentFields');
        const sigField = document.getElementById('addSignatureField');
        const sid = document.getElementById('addStudentId');
        const courseEl = document.getElementById('addCourseSelect');
        const sectEl = document.getElementById('addSectionSelect');

        if (STUDENT_ROLES.includes(role)) {
            sf.style.display = '';
            sid.required = true;
            courseEl.required = true;
            sectEl.required = true;
            if (sigField) sigField.style.display = 'none';
        } else {
            sf.style.display = 'none';
            sid.required = false;
            courseEl.required = false;
            sectEl.required = false;
            if (sigField) sigField.style.display = APPROVER_ROLES.includes(role) ? '' : 'none';
        }
    }
}

document.getElementById('editUserModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editFirstName').value = btn.dataset.firstName;
    document.getElementById('editMiddleName').value = btn.dataset.middleName || '';
    document.getElementById('editSurname').value = btn.dataset.surname;
    document.getElementById('editRole').value = btn.dataset.role;
    document.getElementById('editActive').checked = btn.dataset.active === '1';
    document.getElementById('editUserForm').action = `/admin/users/${btn.dataset.id}`;
});

function filterAdminSections() {
    const courseId = document.getElementById('addCourseSelect').value;
    const yearLvl  = document.getElementById('addYearSelect').value;
    const sel = document.getElementById('addSectionSelect');
    const currentVal = sel.value;
    let keepCurrent = false;

    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        const matchCourse = !courseId || opt.dataset.course == courseId;
        const matchYear   = !yearLvl  || opt.dataset.year == yearLvl;
        const show = matchCourse && matchYear;
        opt.style.display = show ? '' : 'none';
        opt.hidden = !show;
        if (show && opt.value === currentVal) keepCurrent = true;
    });
    if (!keepCurrent) sel.value = '';
}

// ── Dynamic Password Requirement Validation On Typing ──────────────────────────────
const passInput = document.getElementById('addUserPassword');
const valBox = document.getElementById('passwordValidationBox');
const toggleBtn = document.getElementById('togglePasswordBtn');
const toggleIcon = document.getElementById('togglePasswordIcon');

if (toggleBtn && passInput) {
    toggleBtn.addEventListener('click', function() {
        const isPass = passInput.type === 'password';
        passInput.type = isPass ? 'text' : 'password';
        toggleIcon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
}

if (passInput && valBox) {
    passInput.addEventListener('input', function() {
        const val = this.value;
        if (!val || val.length === 0) {
            valBox.style.display = 'none';
            return;
        }

        const hasMinLen = val.length >= 8;
        const hasUpper  = /[A-Z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        const hasSymbol = /[^A-Za-z0-9]/.test(val);

        const allValid = hasMinLen && hasUpper && hasNumber && hasSymbol;

        if (allValid) {
            // Hide box when all requirements are met!
            valBox.style.display = 'none';
        } else {
            valBox.style.display = 'block';
            updateReqItem('reqMinChar', hasMinLen, 'At least 8 characters');
            updateReqItem('reqUpper',   hasUpper,  'At least 1 uppercase letter (A-Z)');
            updateReqItem('reqNumber',  hasNumber, 'At least 1 number (0-9)');
            updateReqItem('reqSymbol',  hasSymbol, 'At least 1 special character (!@#$%^&*)');
        }
    });

    function updateReqItem(id, isValid, labelText) {
        const el = document.getElementById(id);
        if (!el) return;
        if (isValid) {
            el.className = 'd-flex align-items-center gap-1.5 text-success fw-600';
            el.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i> ${labelText}`;
        } else {
            el.className = 'd-flex align-items-center gap-1.5 text-danger';
            el.innerHTML = `<i class="bi bi-x-circle-fill text-danger"></i> ${labelText}`;
        }
    }
}

// Reset password box when modal closes
document.getElementById('addUserModal')?.addEventListener('hidden.bs.modal', function() {
    if (valBox) valBox.style.display = 'none';
    if (passInput) passInput.value = '';
});

// Init on load – start with student fields visible
window.addEventListener('load', () => handleRoleChange('student', 'add'));
</script>
@endpush
@endsection

