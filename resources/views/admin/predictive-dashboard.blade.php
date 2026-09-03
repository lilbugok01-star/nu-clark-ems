@extends('layouts.app')

@section('title', 'Predictive Analytics')

@push('styles')
<style>
    .predictive-dashboard {
        padding-bottom: 2rem;
    }
    .stat-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        padding: 1.25rem;
        border: 1px solid rgba(0,0,0,0.05);
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.03);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .chart-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        padding: 1.5rem;
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
    }
    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    .chart-container {
        position: relative;
        height: 320px;
        width: 100%;
    }
    .nu-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    .nu-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        background-color: transparent;
    }
    .nu-card-title {
        font-weight: 600;
        color: var(--nu-blue-dk);
        margin: 0;
    }
    .nu-table {
        width: 100%;
        border-collapse: collapse;
    }
    .nu-table th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .nu-table td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.875rem;
    }
    .nu-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .badge-soft {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
        border-radius: 0.25rem;
    }
    .badge-soft-danger { background-color: #fee2e2; color: #991b1b; }
    .badge-soft-warning { background-color: #fef3c7; color: #92400e; }
    .badge-soft-success { background-color: #dcfce7; color: #166534; }
    
    .fade-in-up {
        animation: fadeInUp 0.4s ease forwards;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3 p-0">
            @include('layouts.partials.sidebar-admin')
        </div>

        <!-- Main Content -->
        <div class="col-lg-10 col-md-9 py-4 predictive-dashboard">
            
            <!-- Section 1: Page Header -->
            <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-1 fw-bold" style="color: var(--nu-blue-dk);">
                        <i class="bi bi-cpu text-gold me-2"></i>Predictive Analytics & Attendance Forecasting
                    </h1>
                    <p class="text-muted mb-0">Statistical forecasts for event attendance, resource needs, and schedule optimization</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('admin.predictive.schedule') }}" class="btn btn-sm btn-outline-gold fw-bold shadow-sm">
                        <i class="bi bi-magic me-1"></i> Schedule Optimizer
                    </a>
                    <a href="{{ route('admin.predictive.export-pdf') }}" class="btn btn-sm btn-danger fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF Report
                    </a>
                </div>
            </div>

            <!-- Section 2: Historical Overview Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6 fade-in-up" style="animation-delay: 0.05s">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(0,48,135,0.1); color: var(--nu-blue);">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-value">{{ number_format($dataSummary['total_completed_events'] ?? 0) }}</div>
                        <div class="stat-label">Historical Events</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 fade-in-up" style="animation-delay: 0.1s">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(111,66,193,0.1); color: #6f42c1;">
                            <i class="bi bi-ticket-perforated"></i>
                        </div>
                        <div class="stat-value">{{ number_format($dataSummary['total_registrations'] ?? 0) }}</div>
                        <div class="stat-label">Historical Regs.</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 fade-in-up" style="animation-delay: 0.15s">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(22,163,74,0.1); color: #16a34a;">
                            <i class="bi bi-patch-check"></i>
                        </div>
                        <div class="stat-value">{{ number_format($dataSummary['total_verified_attendances'] ?? 0) }}</div>
                        <div class="stat-label">Verified Attendances</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 fade-in-up" style="animation-delay: 0.2s">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(255,184,0,0.1); color: var(--nu-gold);">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="stat-value">{{ number_format($dataSummary['overall_attendance_rate'] ?? 0, 1) }}%</div>
                        <div class="stat-label">System Show-Up Rate</div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Data Quality Assessment Banner -->
            <div class="alert alert-{{ ($dataSummary['data_quality'] ?? '') === 'sufficient' ? 'success' : (($dataSummary['data_quality'] ?? '') === 'limited' ? 'warning' : 'info') }} border-start border-4 mb-4 fade-in-up" style="animation-delay: 0.25s">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <strong class="text-uppercase small">Data Quality Assessment: {{ ucfirst($dataSummary['data_quality'] ?? 'N/A') }}</strong>
                        <p class="mb-0 small text-muted">{{ $dataSummary['data_quality_notes'] ?? '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Section 4: Upcoming Predictions Table & Category Performance Chart -->
            <div class="row g-4 mb-4">
                <!-- Upcoming Predictions Table -->
                <div class="col-lg-8 fade-in-up" style="animation-delay: 0.3s">
                    <div class="nu-card h-100 mb-0">
                        <div class="nu-card-header d-flex justify-content-between align-items-center">
                            <h3 class="nu-card-title h5"><i class="bi bi-calendar-event me-2"></i>Upcoming Events Attendance Predictions</h3>
                            <span class="badge bg-secondary">{{ count($predictions ?? []) }} Upcoming</span>
                        </div>
                        <div class="table-responsive">
                            <table class="nu-table">
                                <thead>
                                    <tr>
                                        <th>Event Title</th>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th class="text-center">Reg. / Pred. Att.</th>
                                        <th class="text-center">Rate</th>
                                        <th class="text-center">Utilization</th>
                                        <th class="text-center">Confidence</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($predictions ?? [] as $pred)
                                    <tr>
                                        <td class="fw-medium">{{ Str::limit($pred['event_title'] ?? 'Unknown', 28) }}</td>
                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($pred['event_date'] ?? now())->format('M d, Y') }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $pred['category'] ?? 'General' }}</span></td>
                                        <td class="text-center fw-bold">
                                            {{ $pred['current_registrations'] ?? 0 }} <span class="text-muted fw-normal">/</span> <span class="text-primary">{{ $pred['predicted_count'] ?? 0 }}</span>
                                        </td>
                                        <td class="text-center fw-semibold">{{ number_format($pred['predicted_rate'] ?? 0, 1) }}%</td>
                                        <td class="text-center">
                                            @php $util = $pred['capacity_utilization'] ?? 0; @endphp
                                            <div class="small mb-1">{{ number_format($util, 1) }}% of {{ $pred['venue_capacity'] ?? 0 }}</div>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar {{ $util > 90 ? 'bg-danger' : ($util > 70 ? 'bg-warning' : 'bg-success') }}" style="width: {{ min(100, $util) }}%"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @php $conf = $pred['confidence'] ?? 'medium'; @endphp
                                            <span class="badge badge-soft badge-soft-{{ $conf == 'high' ? 'success' : ($conf == 'medium' ? 'warning' : 'danger') }}">{{ ucfirst($conf) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.predictive.event', $pred['event_id']) }}" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.75rem;">
                                                Details <i class="bi bi-chevron-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No upcoming published events found for prediction.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Category Attendance Performance Chart -->
                <div class="col-lg-4 fade-in-up" style="animation-delay: 0.35s">
                    <div class="chart-card h-100 mb-0">
                        <div class="chart-header">
                            <h3 class="chart-title"><i class="bi bi-bar-chart-line me-2" style="color: var(--nu-blue);"></i>Category Attendance Rates</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="categoryAttendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Category & Day Pattern Breakdown Tables -->
            <div class="row g-4">
                <!-- Category Breakdown Table -->
                <div class="col-lg-6 fade-in-up" style="animation-delay: 0.4s">
                    <div class="nu-card mb-0">
                        <div class="nu-card-header">
                            <h3 class="nu-card-title h6"><i class="bi bi-tags me-2"></i>Historical Category Attendance Performance</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="nu-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th class="text-center">Events</th>
                                        <th class="text-center">Avg. Reg / Att</th>
                                        <th class="text-end">Attendance Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categoryRates ?? [] as $catName => $catData)
                                    <tr>
                                        <td class="fw-semibold">{{ $catName }}</td>
                                        <td class="text-center">{{ $catData['events'] ?? 0 }}</td>
                                        <td class="text-center small">{{ number_format($catData['avg_registrations'] ?? 0, 1) }} / {{ number_format($catData['avg_attendances'] ?? 0, 1) }}</td>
                                        <td class="text-end fw-bold text-primary">{{ number_format($catData['attendance_rate'] ?? 0, 1) }}%</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted">No category data recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Day of Week Pattern Table -->
                <div class="col-lg-6 fade-in-up" style="animation-delay: 0.45s">
                    <div class="nu-card mb-0">
                        <div class="nu-card-header">
                            <h3 class="nu-card-title h6"><i class="bi bi-calendar3 me-2"></i>Day of Week Participation Patterns</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="nu-table">
                                <thead>
                                    <tr>
                                        <th>Day of Week</th>
                                        <th class="text-center">Historical Events</th>
                                        <th class="text-center">Avg Reg / Att</th>
                                        <th class="text-end">Show-Up Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dayPatterns ?? [] as $dayName => $dayData)
                                    <tr>
                                        <td class="fw-semibold">{{ $dayName }}</td>
                                        <td class="text-center">{{ $dayData['events'] ?? 0 }}</td>
                                        <td class="text-center small">{{ number_format($dayData['avg_registrations'] ?? 0, 1) }} / {{ number_format($dayData['avg_attendances'] ?? 0, 1) }}</td>
                                        <td class="text-end fw-bold text-success">{{ number_format($dayData['attendance_rate'] ?? 0, 1) }}%</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted">No day pattern data recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"
    integrity="sha384-e6nUZLBkQ86NJ6TVVKAeSaK8jWa3NhkYWZFomE39AvDbQWeie9PlQqM3pmYW5d1g" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Inter', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
        
        const catDataRaw = @json($categoryRates ?? []);
        const labels = Object.keys(catDataRaw);
        const rates = labels.map(k => catDataRaw[k].attendance_rate || 0);

        const ctx = document.getElementById('categoryAttendanceChart');
        if (ctx && labels.length > 0) {
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Attendance Rate (%)',
                        data: rates,
                        backgroundColor: 'rgba(0, 48, 135, 0.85)',
                        borderColor: '#003087',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) { return context.raw + '% attendance rate'; }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { callback: function(value) { return value + '%'; } },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endpush
