@extends('layouts.app')
@section('title', 'Reports — Admin')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 col-md-3">
            <div class="dashboard-sidebar rounded-xl mb-4">
                <div class="text-white-50 small text-uppercase fw-semibold mb-3 ps-2" style="letter-spacing:1px">Admin Panel</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="{{ route('admin.users') }}" class="sidebar-link"><i class="bi bi-people"></i> Users</a>
                <a href="{{ route('admin.courses') }}" class="sidebar-link"><i class="bi bi-book"></i> Courses</a>
                <a href="{{ route('admin.reports') }}" class="sidebar-link active"><i class="bi bi-bar-chart"></i> Reports</a>
                <a href="{{ route('admin.notifications') }}" class="sidebar-link"><i class="bi bi-megaphone"></i> Notifications</a>
            </div>
        </div>
        <div class="col-lg-10 col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0" style="color:var(--nu-blue)"><i class="bi bi-bar-chart me-2"></i>Event Reports</h4>
                <a href="{{ route('admin.reports.events.pdf') }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf me-1"></i>Export All PDF</a>
            </div>

            <div class="nu-card">
                <div class="table-responsive">
                <table class="table nu-table mb-0">
                    <thead><tr><th>Event</th><th>Date</th><th>Organizer</th><th>Capacity</th><th>Registered</th><th>Verified</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($events as $e)
                        <tr>
                            <td>
                                <div class="fw-semibold small">{{ $e->title }}</div>
                                <span class="badge-category">{{ $e->category ?? 'General' }}</span>
                            </td>
                            <td class="small">{{ $e->event_date->format('M d, Y') }}</td>
                            <td class="small">{{ $e->organizer->name ?? '-' }}</td>
                            <td class="small">{{ $e->capacity }}</td>
                            <td class="small">{{ $e->registrations_count }}</td>
                            <td class="small">{{ $e->verified_count }}</td>
                            <td><span class="badge-status-{{ $e->status }}">{{ ucfirst($e->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            <div class="mt-3">{{ $events->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>
@endsection
