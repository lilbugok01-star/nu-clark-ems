@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3">
            <div class="dashboard-sidebar rounded-xl mb-4">
                <div class="text-white-50 small text-uppercase fw-semibold mb-3 ps-2" style="letter-spacing:1px">Admin Panel</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link @if(request()->routeIs('admin.dashboard')) active @endif"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="{{ route('admin.users') }}" class="sidebar-link @if(request()->routeIs('admin.users')) active @endif"><i class="bi bi-people"></i> Users</a>
                <a href="{{ route('admin.courses') }}" class="sidebar-link @if(request()->routeIs('admin.courses')) active @endif"><i class="bi bi-book"></i> Courses</a>
                <a href="{{ route('admin.reports') }}" class="sidebar-link @if(request()->routeIs('admin.reports')) active @endif"><i class="bi bi-bar-chart"></i> Reports</a>
                <a href="{{ route('admin.notifications') }}" class="sidebar-link @if(request()->routeIs('admin.notifications')) active @endif"><i class="bi bi-megaphone"></i> Notifications</a>
                <a href="{{ route('events') }}" class="sidebar-link"><i class="bi bi-calendar3"></i> View Events</a>
                <hr style="border-color:rgba(255,255,255,0.1)">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-link w-100 border-0" style="background:none;text-align:left"><i class="bi bi-box-arrow-right"></i> Logout</button>
                </form>
            </div>
        </div>

        <!-- Content -->
        <div class="col-lg-10 col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0" style="color:var(--nu-blue)"><i class="bi bi-speedometer2 me-2"></i>System Overview</h4>
                <span class="badge bg-nu-blue px-3 py-2">Administrator</span>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                @php
                $statItems = [
                    ['label'=>'Total Students',      'value'=>$stats['total_students'],      'icon'=>'people',          'color'=>'var(--nu-blue)'],
                    ['label'=>'Total Organizers',    'value'=>$stats['total_organizers'],    'icon'=>'person-badge',    'color'=>'var(--nu-gold)'],
                    ['label'=>'Total Events',        'value'=>$stats['total_events'],        'icon'=>'calendar3',       'color'=>'#28a745'],
                    ['label'=>'Upcoming Events',     'value'=>$stats['upcoming_events'],     'icon'=>'calendar-check',  'color'=>'#17a2b8'],
                    ['label'=>'Registrations',       'value'=>$stats['total_registrations'], 'icon'=>'ticket-perforated','color'=>'#6f42c1'],
                    ['label'=>'Verified Attendances','value'=>$stats['total_attendances'],   'icon'=>'patch-check',     'color'=>'#fd7e14'],
                ];
                @endphp
                @foreach($statItems as $i => $s)
                <div class="col-md-4 col-6 fade-in-up" style="animation-delay:{{ $i*0.07 }}s">
                    <div class="stat-card" style="border-color:{{ $s['color'] }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-value" style="color:{{ $s['color'] }}">{{ $s['value'] }}</div>
                                <div class="stat-label">{{ $s['label'] }}</div>
                            </div>
                            <div class="stat-icon" style="background:{{ $s['color'] }}20">
                                <i class="bi bi-{{ $s['icon'] }}" style="color:{{ $s['color'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row g-4">
                <!-- Monthly Chart -->
                <div class="col-lg-7">
                    <div class="nu-card p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart text-gold me-2"></i>Monthly Registrations</h6>
                        <canvas id="registrationsChart" height="200"></canvas>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="col-lg-5">
                    <div class="nu-card p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-people text-gold me-2"></i>Recent Users</h6>
                        @foreach($recentUsers as $u)
                        <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" style="background:var(--gray-100)">
                            <div class="stat-icon" style="background:rgba(0,48,135,0.1);min-width:36px;height:36px">
                                <i class="bi bi-person-circle" style="color:var(--nu-blue);font-size:0.9rem"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">{{ $u->name }}</div>
                                <div class="text-muted" style="font-size:0.72rem">{{ ucfirst($u->role) }} · {{ $u->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endforeach
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-gold btn-sm w-100 mt-2">Manage Users</a>
                    </div>
                </div>

                <!-- Recent Events -->
                <div class="col-12">
                    <div class="nu-card p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-calendar3 text-gold me-2"></i>Recent Events</h6>
                            <a href="{{ route('admin.reports') }}" class="btn btn-outline-gold btn-sm">View Reports</a>
                        </div>
                        <div class="table-responsive">
                        <table class="table nu-table">
                            <thead><tr><th>Title</th><th>Date</th><th>Venue</th><th>Organizer</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($recentEvents as $e)
                                <tr>
                                    <td class="fw-semibold">{{ $e->title }}</td>
                                    <td>{{ $e->event_date->format('M d, Y') }}</td>
                                    <td>{{ $e->venue }}</td>
                                    <td>{{ $e->organizer->name ?? '-' }}</td>
                                    <td><span class="badge-status-{{ $e->status }}">{{ ucfirst($e->status) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels = @json(array_column($monthlyData, 'month'));
const data   = @json(array_column($monthlyData, 'count'));

new Chart(document.getElementById('registrationsChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Registrations',
            data,
            backgroundColor: 'rgba(0,48,135,0.8)',
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
@endsection
