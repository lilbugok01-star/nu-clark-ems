@extends('layouts.app')
@section('title', 'Predictive Analytics')
@push('styles')
<style>
    .predictive-dashboard { padding-bottom: 2rem; }
    .stat-card {
        background: #fff; border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        padding: 1.25rem; border: 1px solid rgba(0,0,0,0.05); height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.03); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; }
    .stat-value { font-size: 1.75rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem; }
    .stat-label { font-size: 0.875rem; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; }
    .chart-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); padding: 1.5rem; border: 1px solid rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
    .chart-title { font-size: 1.1rem; font-weight: 600; color: #1f2937; margin: 0; }
    .chart-container { position: relative; height: 300px; width: 100%; }
    .nu-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    .nu-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); background-color: transparent; }
    .nu-card-title { font-weight: 600; color: var(--nu-blue-dk); margin: 0; }
    .nu-table { width: 100%; border-collapse: collapse; }
    .nu-table th { background-color: #f8fafc; color: #475569; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; }
    .nu-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem; }
    .badge-soft { padding: 0.35em 0.65em; font-size: 0.75em; font-weight: 600; border-radius: 0.25rem; }
    .badge-soft-danger { background-color: #fee2e2; color: #991b1b; }
    .badge-soft-warning { background-color: #fef3c7; color: #92400e; }
    .badge-soft-success { background-color: #dcfce7; color: #166534; }
    .fade-in-up { animation: fadeInUp 0.5s ease forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
</style>
@endpush
@section('content')
<div class="container-fluid py-4 predictive-dashboard">
    <div class="row">
        <div class="col-lg-2 col-md-3 p-0">
            @include('layouts.partials.sidebar-admin')
        </div>
        <div class="col-lg-10 col-md-9 py-4">
            
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h1 class="h3 mb-1 fw-bold" style="color: var(--nu-blue);"><i class="bi bi-graph-up text-gold me-2"></i>Predictive Analytics <span class="badge bg-nu-blue fs-6 ms-2">Beta</span></h1>
                    <p class="text-muted mb-0">AI-driven forecasts for event attendance, resource needs, and schedule optimization</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.predictive.schedule') }}" class="btn btn-outline-gold fw-bold shadow-sm">
                        <i class="bi bi-magic me-1"></i> Schedule Optimizer
                    </a>
                    <a href="{{ route('admin.predictive.export-pdf') }}" class="btn btn-danger fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                    </a>
                </div>
            </div>

            <!-- Historical Data Quality -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 fade-in-up" style="animation-delay: 0.1s">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-database"></i>
                        </div>
                        <div class="stat-value">{{ $dataSummary['total_completed_events'] ?? 0 }}</div>
                        <div class="stat-label">Historical Events</div>
                    </div>
                </div>
                <div class="col-md-3 fade-in-up" style="animation-delay: 0.15s">
                    <div class="stat-card">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-ticket-perforated"></i>
                        </div>
                        <div class="stat-value">{{ number_format($dataSummary['total_registrations'] ?? 0) }}</div>
                        <div class="stat-label">Historical Regs.</div>
                    </div>
                </div>
                <div class="col-md-3 fade-in-up" style="animation-delay: 0.2s">
                    <div class="stat-card">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div class="stat-value">{{ number_format($dataSummary['total_verified_attendances'] ?? 0) }}</div>
                        <div class="stat-label">Verified Attendances</div>
                    </div>
                </div>
                    </div>
                </div>

                <div class="col-lg-4 fade-in-up" style="animation-delay: 0.6s">
                    <div class="chart-card h-100 mb-0">
                        <div class="chart-header">
                            <h3 class="chart-title"><i class="bi bi-bullseye me-2" style="color: #16a34a;"></i>Prediction Accuracy</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="accuracyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row fade-in-up" style="animation-delay: 0.7s">
                <div class="col-12">
                    <div class="nu-card">
                        <div class="nu-card-header">
                            <h3 class="nu-card-title h5"><i class="bi bi-tags me-2"></i>Predicted Category Attendance Rates</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="nu-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th class="text-center">Historical Rate</th>
                                        <th class="text-center">Trend Adjusted Rate</th>
                                        <th>Forecast Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categoryRates ?? [] as $cat)
                                    <tr>
                                        <td class="fw-medium">{{ $cat['category'] ?? 'N/A' }}</td>
                                        <td class="text-center">{{ number_format($cat['historical'] ?? 0, 1) }}%</td>
                                        <td class="text-center fw-bold text-primary">{{ number_format($cat['predicted'] ?? 0, 1) }}%</td>
                                        <td>
                                            @php $diff = ($cat['predicted'] ?? 0) - ($cat['historical'] ?? 0); @endphp
                                            @if($diff > 0)
                                                <span class="text-success small fw-bold"><i class="bi bi-arrow-up-right me-1"></i>Trending Up</span>
                                            @elseif($diff < 0)
                                                <span class="text-danger small fw-bold"><i class="bi bi-arrow-down-right me-1"></i>Trending Down</span>
                                            @else
                                                <span class="text-muted small fw-bold"><i class="bi bi-dash me-1"></i>Stable</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No category data available.</td></tr>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Inter', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
        const ctx = document.getElementById('accuracyChart');
        if(ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Event 1', 'Event 2', 'Event 3', 'Event 4', 'Event 5'],
                    datasets: [
                        {
                            label: 'Predicted',
                            data: [120, 150, 80, 200, 95],
                            backgroundColor: 'rgba(0, 48, 135, 0.7)',
                            borderRadius: 4
                        },
                        {
                            label: 'Actual',
                            data: [115, 155, 75, 190, 100],
                            backgroundColor: 'rgba(255, 184, 0, 0.7)',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    });
</script>
@endpush
