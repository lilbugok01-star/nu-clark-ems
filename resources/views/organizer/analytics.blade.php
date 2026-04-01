@extends('layouts.app')
@section('title', 'Analytics')
@section('content')
<div class="container py-5">
    <h4 class="fw-800 mb-4" style="color:var(--nu-blue)"><i class="bi bi-bar-chart me-2" style="color:var(--nu-gold)"></i>Event Analytics</h4>

    <div class="nu-card p-4 mb-4">
        <canvas id="analyticsChart" height="100"></canvas>
    </div>

    <div class="nu-card">
        <div class="table-responsive">
        <table class="table nu-table mb-0">
            <thead><tr><th>Event</th><th>Date</th><th>Registered</th><th>Verified Attendance</th><th>Rate</th></tr></thead>
            <tbody>
                @foreach($events as $e)
                <tr>
                    <td class="fw-600 small">{{ $e->title }}</td>
                    <td class="small">{{ $e->event_date->format('M d, Y') }}</td>
                    <td>{{ $e->registrations_count }}</td>
                    <td>{{ $e->verified_count }}</td>
                    <td>
                        @php $rate = $e->registrations_count > 0 ? round($e->verified_count/$e->registrations_count*100) : 0; @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px">
                                <div class="progress-bar" style="width:{{ $rate }}%;background:var(--nu-blue)"></div>
                            </div>
                            <small>{{ $rate }}%</small>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@php
    $chartLabels = $events->pluck('title')->map(fn($t) => Str::limit($t, 20))->values()->toArray();
@endphp
const labels = @json($chartLabels);
new Chart(document.getElementById('analyticsChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'Registered',         data: @json($events->pluck('registrations_count')->values()), backgroundColor: 'rgba(0,48,135,0.7)', borderRadius: 6 },
            { label: 'Verified Attendance',data: @json($events->pluck('verified_count')->values()),       backgroundColor: 'rgba(255,184,0,0.8)', borderRadius: 6 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
    }
});
</script>
@endpush
@endsection
