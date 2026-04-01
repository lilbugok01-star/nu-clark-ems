@extends('layouts.app')
@section('title', 'Notifications — Admin')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 col-md-3">
            <div class="dashboard-sidebar rounded-xl mb-4">
                <div class="text-white-50 small text-uppercase fw-semibold mb-3 ps-2" style="letter-spacing:1px">Admin Panel</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="{{ route('admin.users') }}" class="sidebar-link"><i class="bi bi-people"></i> Users</a>
                <a href="{{ route('admin.courses') }}" class="sidebar-link"><i class="bi bi-book"></i> Courses</a>
                <a href="{{ route('admin.reports') }}" class="sidebar-link"><i class="bi bi-bar-chart"></i> Reports</a>
                <a href="{{ route('admin.notifications') }}" class="sidebar-link active"><i class="bi bi-megaphone"></i> Notifications</a>
            </div>
        </div>
        <div class="col-lg-10 col-md-9">
            <h4 class="fw-bold mb-4" style="color:var(--nu-blue)"><i class="bi bi-megaphone me-2"></i>Send Notification</h4>

            <div class="nu-card p-4">
                <form action="{{ route('admin.notifications.send') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Send to</label>
                        <select name="role" class="form-select">
                            <option value="">All Users</option>
                            <option value="student">Students Only</option>
                            <option value="organizer">Organizers Only</option>
                            <option value="admin">Admins Only</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="Notification title...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required placeholder="Type your announcement..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-nu-blue">
                        <i class="bi bi-send me-1"></i>Send Notification
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
