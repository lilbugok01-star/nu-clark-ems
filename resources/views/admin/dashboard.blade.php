@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3">
            @include('layouts.partials.sidebar-admin')
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
                                <div class="small fw-semibold">{{ $u->full_name }}</div>
                                <div class="text-muted" style="font-size:0.72rem">{{ ucfirst($u->role) }} · {{ $u->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endforeach
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-gold btn-sm w-100 mt-2">Manage Users</a>
                    </div>
                </div>

                <!-- Upcoming & Recent Events -->
                <div class="col-12">
                    <div class="nu-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-calendar-check text-gold me-2"></i>Upcoming & Active Events</h6>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.events') }}" class="btn btn-nu-blue btn-sm"><i class="bi bi-calendar-event me-1"></i>Manage Events</a>
                                <a href="{{ route('admin.reports') }}" class="btn btn-outline-gold btn-sm">View Reports</a>
                            </div>
                        </div>
                        <div class="table-responsive">
                        <table class="table nu-table align-middle">
                            <thead><tr><th>Title</th><th>Date</th><th>Venue</th><th>Organizer</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                            <tbody>
                                @forelse($recentEvents as $e)
                                <tr>
                                    <td class="fw-semibold">{{ $e->title }}</td>
                                    <td>{{ $e->event_date ? $e->event_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $e->venue }}</td>
                                    <td>{{ $e->organizer?->full_name ?? '-' }}</td>
                                    <td>@php $sl=match($e->status ?? ''){ 'pending_adviser','pending_dept_head','pending_dean','pending_director'=>'Pending Approval','published'=>'Published','draft'=>'Draft','cancelled'=>'Cancelled','completed'=>'Completed','rejected'=>'Rejected',default=>ucfirst($e->status ?? 'Unknown')};@endphp<span class="badge-status-{{ $e->status ?? 'draft' }}">{{ $sl }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('event.show', $e->id) }}" class="btn btn-outline-secondary btn-sm" title="View"><i class="bi bi-eye"></i></a>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delEventModal{{ $e->id }}" title="Fast Delete"><i class="bi bi-trash"></i></button>
                                        </div>

                                        <!-- Fast Delete Modal -->
                                        <div class="modal fade text-start" id="delEventModal{{ $e->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-3">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-700 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Remove Event</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.events.delete', $e->id) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <div class="modal-body py-3">
                                                            <p class="mb-2">Are you sure you want to remove <strong>"{{ $e->title }}"</strong>?</p>
                                                            <div class="alert alert-warning small py-2 mb-3">
                                                                <i class="bi bi-info-circle me-1"></i>
                                                                This will cancel all student registrations, notify registered students, and remove the event immediately.
                                                            </div>
                                                            <label class="form-label small fw-600">Reason (Optional):</label>
                                                            <input type="text" name="reason" class="form-control form-control-sm" placeholder="e.g. Professor resigned, cancelled">
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger btn-sm fw-700"><i class="bi bi-trash me-1"></i>Confirm Removal</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">No upcoming events found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Events Calendar -->
                <div class="col-12 mt-4">
                    <div class="nu-card p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-calendar3 text-gold me-2"></i>Events Calendar</h6>
                        <x-event-calendar calendarId="adminCalendar" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"
    integrity="sha384-e6nUZLBkQ86NJ6TVVKAeSaK8jWa3NhkYWZFomE39AvDbQWeie9PlQqM3pmYW5d1g" crossorigin="anonymous"></script>
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
