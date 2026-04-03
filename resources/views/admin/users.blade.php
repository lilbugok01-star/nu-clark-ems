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
                <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus me-1"></i>Add User
                </button>
            </div>

            <!-- Filters -->
            <form class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin"     {{ request('role')=='admin'     ? 'selected' : '' }}>Admin</option>
                        <option value="organizer" {{ request('role')=='organizer' ? 'selected' : '' }}>Organizer</option>
                        <option value="student"   {{ request('role')=='student'   ? 'selected' : '' }}>Student</option>
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
                                <span class="badge {{ $user->role === 'admin' ? 'bg-nu-blue' : ($user->role === 'organizer' ? 'bg-warning text-dark' : 'bg-secondary') }}" style="font-size:0.7rem">
                                    {{ ucfirst($user->role) }}
                                </span>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--nu-blue)">
                <h5 class="modal-title text-white">Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required minlength="8"></div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="addUserRole" class="form-select" required onchange="toggleStudentFields(this.value)">
                            <option value="student">Student</option>
                            <option value="organizer">Organizer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    {{-- Student-only fields --}}
                    <div id="studentFields">
                        <div class="mb-3">
                            <label class="form-label">Student ID <span class="text-danger">*</span></label>
                            <input type="text" name="student_id" id="addStudentId" class="form-control" placeholder="e.g. 2022-00001">
                            <div class="form-text">Required for Student accounts.</div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4 col-12">
                                <label class="form-label">Course <span class="text-danger">*</span></label>
                                <select name="course_id" id="addCourseSelect" class="form-select" onchange="filterAdminSections()">
                                    <option value="">— Course —</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 col-6">
                                <label class="form-label">Year Level <span class="text-danger">*</span></label>
                                <select id="addYearSelect" class="form-select" onchange="filterAdminSections()">
                                    <option value="">— Year —</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-6">
                                <label class="form-label">Section <span class="text-danger">*</span></label>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-nu-blue">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--nu-blue)">
                <h5 class="modal-title text-white">Edit User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" id="editName" class="form-control"></div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="editRole" class="form-select">
                            <option value="student">Student</option>
                            <option value="organizer">Organizer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="editActive" value="1">
                        <label class="form-check-label" for="editActive">Active Account</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-nu-blue">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('editUserModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const id  = btn.dataset.id;
    document.getElementById('editName').value = btn.dataset.name;
    document.getElementById('editRole').value = btn.dataset.role;
    document.getElementById('editActive').checked = btn.dataset.active === '1';
    document.getElementById('editUserForm').action = `/admin/users/${id}`;
});

// Show/hide student-only fields based on role
function toggleStudentFields(role) {
    const sf = document.getElementById('studentFields');
    const sid = document.getElementById('addStudentId');
    const courseEl = document.getElementById('addCourseSelect');
    const yearEl = document.getElementById('addYearSelect');
    const sectEl = document.getElementById('addSectionSelect');
    if (role === 'student') {
        sf.style.display = '';
        sid.required = true;
        courseEl.required = true;
        yearEl.required = true;
        sectEl.required = true;
    } else {
        sf.style.display = 'none';
        sid.required = false;
        courseEl.required = false;
        yearEl.required = false;
        sectEl.required = false;
    }
}

// Filter sections by selected course AND year in Add User modal
function filterAdminSections() {
    const courseId = document.getElementById('addCourseSelect').value;
    const yearLvl  = document.getElementById('addYearSelect').value;
    const sel = document.getElementById('addSectionSelect');
    
    const currentVal = sel.value;
    let keepCurrent = false;

    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return; // Keep placeholder
        
        const matchCourse = !courseId || opt.dataset.course == courseId;
        const matchYear   = !yearLvl  || opt.dataset.year == yearLvl;
        const show = matchCourse && matchYear;
        
        opt.style.display = show ? '' : 'none';
        opt.hidden = !show;

        if (show && opt.value === currentVal) keepCurrent = true;
    });
    
    if (!keepCurrent) sel.value = '';
}

// Init on load
window.addEventListener('load', () => toggleStudentFields('student'));
</script>
@endpush
@endsection
