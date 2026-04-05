@extends('layouts.app')
@section('title', 'User Management')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 col-md-3">
            <div class="dashboard-sidebar rounded-xl mb-4">
                <div class="text-white-50 small text-uppercase fw-semibold mb-3 ps-2" style="letter-spacing:1px">Admin Panel</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="{{ route('admin.users') }}" class="sidebar-link active"><i class="bi bi-people"></i> Users</a>
                <a href="{{ route('admin.courses') }}" class="sidebar-link"><i class="bi bi-book"></i> Courses</a>
                <a href="{{ route('admin.reports') }}" class="sidebar-link"><i class="bi bi-bar-chart"></i> Reports</a>
                <a href="{{ route('admin.notifications') }}" class="sidebar-link"><i class="bi bi-megaphone"></i> Notifications</a>
            </div>
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
                            <td class="fw-semibold small">{{ $user->name }}</td>
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
                            <td><span class="status-dot {{ $user->is_active ? 'bg-success' : 'bg-danger' }}"></span> {{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal"
                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-role="{{ $user->role }}"
                                            data-active="{{ $user->is_active ? '1' : '0' }}">
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
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-3 overflow-hidden">
            <div class="modal-header" style="background:var(--nu-blue);border:none">
                <h5 class="modal-title text-white fw-700"><i class="bi bi-person-plus me-2" style="color:var(--nu-gold)"></i>Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Juan dela Cruz">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="user@nu-clark.edu.ph">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8" placeholder="Min. 8 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Role <span class="text-danger">*</span></label>
                            <select name="role" id="addUserRole" class="form-select" required onchange="handleRoleChange(this.value, 'add')">
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

                        {{-- Student-only fields --}}
                        <div id="addStudentFields" class="col-12">
                            <div class="p-3 rounded-3" style="background:rgba(0,48,135,.04);border:1px solid rgba(0,48,135,.12)">
                                <div class="fw-600 small mb-3" style="color:var(--nu-blue)"><i class="bi bi-mortarboard me-1"></i>Student Information</div>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label fw-600">Student ID <span class="text-danger">*</span></label>
                                        <input type="text" name="student_id" id="addStudentId" class="form-control" placeholder="e.g. 2022-00001">
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <label class="form-label fw-600">Course <span class="text-danger">*</span></label>
                                        <select name="course_id" id="addCourseSelect" class="form-select" onchange="filterAdminSections()">
                                            <option value="">— Course —</option>
                                            @foreach($courses as $c)
                                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <label class="form-label fw-600">Year Level <span class="text-danger">*</span></label>
                                        <select id="addYearSelect" class="form-select" onchange="filterAdminSections()">
                                            <option value="">— Year —</option>
                                            <option value="1">1st Year</option>
                                            <option value="2">2nd Year</option>
                                            <option value="3">3rd Year</option>
                                            <option value="4">4th Year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <label class="form-label fw-600">Section <span class="text-danger">*</span></label>
                                        <select name="section_id" id="addSectionSelect" class="form-select">
                                            <option value="">— Section —</option>
                                            @foreach($sections as $s)
                                                <option value="{{ $s->id }}" data-course="{{ $s->course_id }}" data-year="{{ $s->year_level }}">{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Signature upload for approver roles --}}
                        <div id="addSignatureField" class="col-12" style="display:none">
                            <div class="p-3 rounded-3" style="background:rgba(255,184,0,.06);border:1px solid rgba(255,184,0,.25)">
                                <div class="fw-600 small mb-2" style="color:var(--nu-blue)"><i class="bi bi-pen me-1" style="color:var(--nu-gold)"></i>E-Signature (Optional)</div>
                                <input type="file" name="e_signature" class="form-control" accept="image/*">
                                <small class="text-muted">Upload a signature image (PNG/JPG). Used on permission forms.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:2px solid var(--nu-gold)">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-nu-blue fw-700"><i class="bi bi-person-plus me-1"></i>Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-3 overflow-hidden">
            <div class="modal-header" style="background:var(--nu-blue);border:none">
                <h5 class="modal-title text-white fw-700"><i class="bi bi-pencil-square me-2" style="color:var(--nu-gold)"></i>Edit User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-600">Full Name</label>
                        <input type="text" name="name" id="editName" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Role</label>
                        <select name="role" id="editRole" class="form-select">
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
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="editActive" value="1">
                        <label class="form-check-label fw-600" for="editActive">Active Account</label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:2px solid var(--nu-gold)">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-nu-blue fw-700"><i class="bi bi-save me-1"></i>Save Changes</button>
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
    document.getElementById('editName').value = btn.dataset.name;
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

// Init on load – start with student fields visible
window.addEventListener('load', () => handleRoleChange('student', 'add'));
</script>
@endpush
@endsection
