@extends('layouts.app')
@section('title', 'Reports — Admin')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 col-md-3">
            @include('layouts.partials.sidebar-admin')
        </div>
        <div class="col-lg-10 col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-0" style="color:var(--nu-blue)"><i class="bi bi-bar-chart me-2"></i>Event Reports</h4>
                    <p class="text-muted small mb-0">Track and filter registrations & attendance telemetry</p>
                </div>
                <a href="{{ route('admin.reports.events.pdf', request()->query()) }}" class="btn btn-danger btn-sm px-3 fw-bold rounded-pill">
                    <i class="bi bi-file-pdf-fill me-1"></i>Export PDF (Filtered)
                </a>
            </div>

            <!-- Filters -->
            <div class="nu-card p-4 mb-4">
                <h6 class="fw-700 mb-3" style="color:var(--nu-blue);"><i class="bi bi-funnel me-2" style="color:var(--nu-gold)"></i>Filter Events Report</h6>
                <form action="{{ route('admin.reports') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Organizer</label>
                        <select name="organizer_id" class="form-select form-select-sm">
                            <option value="">-- All Organizers --</option>
                            @foreach($organizers as $org)
                                <option value="{{ $org->id }}" @if(request('organizer_id') == $org->id) selected @endif>{{ $org->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Course</label>
                        <select name="course_id" class="form-select form-select-sm">
                            <option value="">-- All Courses --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @if(request('course_id') == $course->id) selected @endif>{{ $course->code }} — {{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Section</label>
                        <select name="section_id" class="form-select form-select-sm">
                            <option value="">-- All Sections --</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec->id }}" @if(request('section_id') == $sec->id) selected @endif>{{ $sec->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Date From</label>
                        <input type="date" name="date_start" class="form-control form-control-sm" value="{{ request('date_start') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Date To</label>
                        <input type="date" name="date_end" class="form-control form-control-sm" value="{{ request('date_end') }}">
                    </div>
                    <div class="col-12 d-flex gap-2 justify-content-end align-items-end mt-3">
                        <a href="{{ route('admin.reports') }}" class="btn btn-sm btn-outline-secondary px-3">Reset</a>
                        <button type="submit" class="btn btn-sm btn-nu-blue px-3"><i class="bi bi-search me-1"></i>Filter Report</button>
                    </div>
                </form>
            </div>

            <!-- Report Table -->
            <div class="nu-card">
                <div class="table-responsive">
                    <table class="table nu-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Event Details</th>
                                <th>Date & Venue</th>
                                <th>Organizer</th>
                                <th class="text-center">Capacity</th>
                                <th class="text-center">Registrations</th>
                                <th class="text-center">Verified Attendance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $e)
                            <tr>
                                <td>
                                    <div class="fw-semibold small" style="color:var(--nu-blue)">{{ $e->title }}</div>
                                    <span class="badge-category">{{ $e->category ?? 'General' }}</span>
                                </td>
                                <td>
                                    <div class="small fw-500">{{ $e->event_date ? $e->event_date->format('M d, Y') : 'N/A' }}</div>
                                    <div class="text-muted small" style="font-size:0.75rem;"><i class="bi bi-geo-alt me-1"></i>{{ $e->venue }}</div>
                                </td>
                                <td class="small">{{ $e->organizer?->full_name ?? '-' }}</td>
                                <td class="text-center small fw-600">{{ $e->capacity }}</td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-2.5 py-1.5 bg-primary-subtle text-primary border" style="font-size:0.75rem;">
                                        {{ $e->registrations_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-2.5 py-1.5 bg-success-subtle text-success border border-success" style="font-size:0.75rem;">
                                        {{ $e->verified_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $sl = match($e->status ?? '') {
                                            'pending_adviser', 'pending_dept_head', 'pending_dean', 'pending_director' => 'Pending Approval',
                                            'published' => 'Published',
                                            'draft' => 'Draft',
                                            'cancelled' => 'Cancelled',
                                            'completed' => 'Completed',
                                            'rejected' => 'Rejected',
                                            default => ucfirst($e->status ?? 'Unknown')
                                        };
                                    @endphp
                                    <span class="badge-status-{{ $e->status ?? 'draft' }}">{{ $sl }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No events matching the criteria found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $events->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>
@endsection
