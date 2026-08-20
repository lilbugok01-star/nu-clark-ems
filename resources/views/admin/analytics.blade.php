@extends('layouts.app')

@section('title', 'Participation Analytics')

@push('styles')
<style>
    .analytics-dashboard {
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
        height: 300px;
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
    .badge-soft-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }
    .badge-soft-warning {
        background-color: #fef3c7;
        color: #92400e;
    }
    .badge-soft-success {
        background-color: #dcfce7;
        color: #166534;
    }
    
    /* Heatmap styles */
    .heatmap-container {
        display: flex;
        flex-direction: column;
        gap: 2px;
        overflow-x: auto;
    }
    .heatmap-row {
        display: flex;
        gap: 2px;
        align-items: center;
    }
    .heatmap-label-y {
        width: 40px;
        font-size: 0.75rem;
        color: #64748b;
        text-align: right;
        padding-right: 8px;
    }
    .heatmap-cell {
        width: 24px;
        height: 24px;
        border-radius: 2px;
        position: relative;
        cursor: pointer;
    }
    .heatmap-header {
        display: flex;
        gap: 2px;
        margin-left: 40px;
        margin-bottom: 4px;
    }
    .heatmap-label-x {
        width: 24px;
        font-size: 0.7rem;
        color: #64748b;
        text-align: center;
    }
    .heatmap-cell:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #334155;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 10;
        pointer-events: none;
        margin-bottom: 4px;
    }
    
    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background-color: #e2e8f0;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 4px;
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
        <div class="col-lg-10 col-md-9 py-4 analytics-dashboard">
            
            <!-- Section 1: Header with Date Filter -->
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h1 class="h3 mb-1 fw-bold" style="color: var(--nu-blue-dk);">📊 Student Participation Analytics</h1>
                    <p class="text-muted mb-0">Analyze student participation patterns, event preferences, and engagement trends</p>
                </div>
                
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('admin.analytics.export-pdf', request()->query()) }}" class="btn btn-sm btn-danger fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF Report
                    </a>

                    <form action="{{ route('admin.analytics') }}" method="GET" class="d-flex gap-2 align-items-end bg-white p-3 rounded shadow-sm border">
                        <div>
                            <label for="date_from" class="form-label small mb-1 text-muted fw-semibold">From</label>
                            <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ $dateFrom ?? '' }}">
                        </div>
                        <div>
                            <label for="date_to" class="form-label small mb-1 text-muted fw-semibold">To</label>
                            <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ $dateTo ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary" style="background-color: var(--nu-blue); border-color: var(--nu-blue);">
                            <i class="bi bi-funnel"></i> Apply
                        </button>
                        @if($dateFrom || $dateTo)
                            <a href="{{ route('admin.analytics') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Section 2: Overview Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(0,48,135,0.1); color: var(--nu-blue);">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <div class="stat-value">{{ number_format($overview['total_events'] ?? 0) }}</div>
                        <div class="stat-label">Total Events</div>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(111,66,193,0.1); color: #6f42c1;">
                            <i class="bi bi-ticket-perforated"></i>
                        </div>
                        <div class="stat-value">{{ number_format($overview['total_registrations'] ?? 0) }}</div>
                        <div class="stat-label">Registrations</div>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(22,163,74,0.1); color: #16a34a;">
                            <i class="bi bi-patch-check"></i>
                        </div>
                        <div class="stat-value">{{ number_format($overview['total_attendances'] ?? 0) }}</div>
                        <div class="stat-label">Attendances</div>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(255,184,0,0.1); color: var(--nu-gold);">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="stat-value">{{ number_format($overview['overall_attendance_rate'] ?? 0, 1) }}%</div>
                        <div class="stat-label">Attendance Rate</div>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(239,68,68,0.1); color: #ef4444;">
                            <i class="bi bi-star"></i>
                        </div>
                        <div class="stat-value" style="font-size: 1.25rem; word-break: break-all;">{{ $overview['most_popular_category'] ?? 'N/A' }}</div>
                        <div class="stat-label">Top Category</div>
                    </div>
                </div>
                <div class="col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: rgba(14,165,233,0.1); color: #0ea5e9;">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-value">{{ number_format($overview['avg_registrations_per_event'] ?? 0, 1) }}</div>
                        <div class="stat-label">Avg Reg/Event</div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Two charts side-by-side -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title"><i class="bi bi-bar-chart-steps me-2" style="color: var(--nu-blue);"></i>Event Category Popularity</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title"><i class="bi bi-activity me-2" style="color: var(--nu-gold);"></i>Participation Trends</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="trendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Course vs Event Category -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title"><i class="bi bi-diagram-3 me-2" style="color: #6f42c1;"></i>Course vs Event Category Participation</h3>
                        </div>
                        <div class="chart-container" style="height: 400px;">
                            <canvas id="courseCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Two charts side-by-side -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title"><i class="bi bi-bar-chart me-2" style="color: #16a34a;"></i>Registration vs Attendance</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="regVsAttChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title"><i class="bi bi-clock-history me-2" style="color: #ef4444;"></i>Peak Participation Times</h3>
                        </div>
                        <div class="heatmap-wrapper">
                            @php
                                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                $hours = range(7, 22);
                                
                                // Process heatmap data
                                $heatmapData = [];
                                $maxCount = 1; // Default to 1 to avoid division by zero
                                
                                if(isset($peakTimes) && is_array($peakTimes)) {
                                    foreach($peakTimes as $pt) {
                                        if(($pt['count'] ?? 0) > $maxCount) {
                                            $maxCount = $pt['count'];
                                        }
                                        $heatmapData[$pt['day']][$pt['hour']] = $pt['count'] ?? 0;
                                    }
                                }
                            @endphp
                            
                            <div class="heatmap-container mt-3">
                                <!-- X-axis labels (Hours) -->
                                <div class="heatmap-header">
                                    @foreach($hours as $hour)
                                        <div class="heatmap-label-x">{{ $hour }}</div>
                                    @endforeach
                                </div>
                                
                                <!-- Y-axis and grid -->
                                @foreach($days as $day)
                                    <div class="heatmap-row">
                                        <div class="heatmap-label-y">{{ $day }}</div>
                                        @foreach($hours as $hour)
                                            @php
                                                $count = $heatmapData[$day][$hour] ?? 0;
                                                $intensity = $count / max($maxCount, 1);
                                                if ($count == 0) {
                                                    $color = '#f1f5f9';
                                                } elseif ($intensity < 0.25) {
                                                    $color = 'rgba(0, 48, 135, 0.25)';
                                                } elseif ($intensity < 0.50) {
                                                    $color = 'rgba(0, 48, 135, 0.50)';
                                                } elseif ($intensity < 0.75) {
                                                    $color = 'rgba(0, 48, 135, 0.75)';
                                                } else {
                                                    $color = 'rgba(0, 48, 135, 1)';
                                                }
                                                $tooltip = $count > 0 ? "{$day} {$hour}:00 - {$count} interactions" : "No interactions";
                                            @endphp
                                            <div class="heatmap-cell" style="background-color: {{ $color }}" title="{{ $tooltip }}"></div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-end align-items-center mt-3 gap-2" style="font-size: 0.75rem;">
                                <span>Less</span>
                                <div style="width: 12px; height: 12px; background-color: #f1f5f9; border-radius: 2px;"></div>
                                <div style="width: 12px; height: 12px; background-color: rgba(0, 48, 135, 0.2); border-radius: 2px;"></div>
                                <div style="width: 12px; height: 12px; background-color: rgba(0, 48, 135, 0.6); border-radius: 2px;"></div>
                                <div style="width: 12px; height: 12px; background-color: rgba(0, 48, 135, 1); border-radius: 2px;"></div>
                                <span>More</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 6: Event Engagement Scores -->
            <div class="row mb-4">
                <div class="col-lg-5">
                    <div class="chart-card h-100">
                        <div class="chart-header">
                            <h3 class="chart-title"><i class="bi bi-heart-pulse me-2" style="color: #0ea5e9;"></i>Engagement Scores</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="engagementChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="nu-card h-100 mb-0">
                        <div class="nu-card-header">
                            <h3 class="nu-card-title h5">Engagement Breakdown</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="nu-table">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Fill Rate</th>
                                        <th>Attendance Rate</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($engagementScores) && count($engagementScores) > 0)
                                        @foreach(array_slice($engagementScores, 0, 8) as $score)
                                            @php
                                                $engScore = $score['engagement_score'] ?? $score['score'] ?? 0;
                                                $scoreColor = $engScore >= 70 ? '#16a34a' : ($engScore >= 40 ? '#f59e0b' : '#ef4444');
                                            @endphp
                                            <tr>
                                                <td class="fw-medium">{{ Str::limit($score['title'] ?? 'Event', 30) }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span style="font-size: 0.75rem; width: 35px;">{{ number_format($score['fill_rate'] ?? 0, 0) }}%</span>
                                                        <div class="progress-bar-custom flex-grow-1">
                                                            <div class="progress-bar-fill" style="width: {{ min(100, $score['fill_rate'] ?? 0) }}%; background-color: #0ea5e9;"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span style="font-size: 0.75rem; width: 35px;">{{ number_format($score['attendance_rate'] ?? 0, 0) }}%</span>
                                                        <div class="progress-bar-custom flex-grow-1">
                                                            <div class="progress-bar-fill" style="width: {{ min(100, $score['attendance_rate'] ?? 0) }}%; background-color: var(--nu-gold);"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background-color: {{ $scoreColor }};">{{ number_format($engScore, 1) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No engagement data available</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 7: Underparticipated Events -->
            <div class="nu-card">
                <div class="nu-card-header d-flex justify-content-between align-items-center">
                    <h3 class="nu-card-title h5"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Events Requiring Attention</h3>
                    <span class="badge bg-secondary">{{ isset($underparticipated) ? count($underparticipated) : 0 }} Issues Found</span>
                </div>
                <div class="table-responsive">
                    <table class="nu-table">
                        <thead>
                            <tr>
                                <th>Event Details</th>
                                <th>Date</th>
                                <th class="text-center">Capacity</th>
                                <th class="text-center">Registered</th>
                                <th class="text-center">Attended</th>
                                <th class="text-center">Rates</th>
                                <th>Issue Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($underparticipated) && count($underparticipated) > 0)
                                @foreach($underparticipated as $event)
                                    @php
                                        $eventDateStr = isset($event['event_date']) && $event['event_date'] ? \Carbon\Carbon::parse($event['event_date'])->format('M d, Y') : '—';
                                        $issue = $event['issue'] ?? 'Attention Needed';
                                    @endphp
                                    <tr>
                                        <td class="fw-medium text-dark">{{ $event['title'] ?? 'Event' }}</td>
                                        <td>{{ $eventDateStr }}</td>
                                        <td class="text-center">{{ $event['capacity'] ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $event['registered'] ?? 0 }}</td>
                                        <td class="text-center">{{ $event['attended'] ?? 0 }}</td>
                                        <td class="text-center">
                                            <div class="small">
                                                Fill: <span class="{{ ($event['fill_rate'] ?? 0) < 50 ? 'text-danger fw-bold' : '' }}">{{ number_format($event['fill_rate'] ?? 0, 1) }}%</span><br>
                                                Att: <span class="{{ ($event['attendance_rate'] ?? 0) < 50 ? 'text-danger fw-bold' : '' }}">{{ number_format($event['attendance_rate'] ?? 0, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($issue === 'Low Registration')
                                                <span class="badge badge-soft badge-soft-danger"><i class="bi bi-ticket-detailed me-1"></i>Low Registration</span>
                                            @elseif($issue === 'Low Attendance')
                                                <span class="badge badge-soft badge-soft-warning"><i class="bi bi-people-fill me-1"></i>Low Attendance</span>
                                            @else
                                                <span class="badge badge-soft badge-soft-danger">{{ $issue }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center text-success">
                                            <i class="bi bi-check-circle-fill fs-2 mb-2"></i>
                                            <h5 class="mb-0">All Good!</h5>
                                            <p class="text-muted small">All events have healthy participation levels.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
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
        // Common Chart Defaults
        Chart.defaults.font.family = "'Inter', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
        Chart.defaults.color = '#64748b';
        Chart.defaults.scale.grid.color = '#f1f5f9';
        
        const nuBlue = '#003087';
        const nuGold = '#FFB800';
        const nuBlueDk = '#001d50';
        
        // --- 1. Event Category Popularity ---
        const categoryData = @json($categoryPopularity ?? []);
        if(document.getElementById('categoryChart') && categoryData.length > 0) {
            const ctxCat = document.getElementById('categoryChart').getContext('2d');
            new Chart(ctxCat, {
                type: 'bar',
                data: {
                    labels: categoryData.map(d => d.category),
                    datasets: [
                        {
                            label: 'Registrations',
                            data: categoryData.map(d => d.registrations),
                            backgroundColor: 'rgba(0, 48, 135, 0.7)',
                            borderColor: nuBlue,
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Attendances',
                            data: categoryData.map(d => d.attendances),
                            backgroundColor: 'rgba(255, 184, 0, 0.7)',
                            borderColor: nuGold,
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: { beginAtZero: true }
                    }
                }
            });
        }

        // --- 2. Participation Trends ---
        const trendsData = @json($trends ?? []);
        if(document.getElementById('trendsChart') && trendsData.length > 0) {
            const ctxTrends = document.getElementById('trendsChart').getContext('2d');
            new Chart(ctxTrends, {
                type: 'line',
                data: {
                    labels: trendsData.map(d => d.label || d.month),
                    datasets: [
                        {
                            label: 'Registrations',
                            data: trendsData.map(d => d.registrations),
                            borderColor: nuBlue,
                            backgroundColor: 'rgba(0, 48, 135, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Attendances',
                            data: trendsData.map(d => d.attendances),
                            borderColor: nuGold,
                            backgroundColor: 'rgba(255, 184, 0, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // --- 3. Course vs Event Category ---
        const courseCategoryDataRaw = @json($courseVsCategory ?? []);
        if(document.getElementById('courseCategoryChart') && courseCategoryDataRaw.length > 0) {
            // Process data for grouped bar chart
            const courses = [...new Set(courseCategoryDataRaw.map(d => d.course))];
            const categories = [...new Set(courseCategoryDataRaw.map(d => d.category))];
            
            // Generate distinct colors for categories
            const colors = [
                nuBlue, nuGold, '#6f42c1', '#16a34a', '#0ea5e9', '#ef4444', '#f59e0b', '#8b5cf6'
            ];
            
            const datasets = categories.map((category, index) => {
                const data = courses.map(course => {
                    const match = courseCategoryDataRaw.find(d => d.course === course && d.category === category);
                    return match ? match.attendances : 0;
                });
                
                return {
                    label: category,
                    data: data,
                    backgroundColor: colors[index % colors.length],
                    borderRadius: 4
                };
            });
            
            const ctxCourseCat = document.getElementById('courseCategoryChart').getContext('2d');
            new Chart(ctxCourseCat, {
                type: 'bar',
                data: {
                    labels: courses,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: { stacked: false },
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // --- 4. Registration vs Attendance Rate ---
        const regVsAttData = @json($regVsAttendance ?? []);
        if(document.getElementById('regVsAttChart') && regVsAttData.length > 0) {
            // Limit to top 10 for readability
            const limitedData = regVsAttData.slice(0, 10);
            
            const ctxRegAtt = document.getElementById('regVsAttChart').getContext('2d');
            new Chart(ctxRegAtt, {
                type: 'bar',
                data: {
                    labels: limitedData.map(d => {
                        let title = d.title;
                        return title.length > 15 ? title.substring(0, 15) + '...' : title;
                    }),
                    datasets: [
                        {
                            type: 'line',
                            label: 'Attendance Rate (%)',
                            data: limitedData.map(d => d.rate),
                            borderColor: '#ef4444',
                            backgroundColor: '#ef4444',
                            borderWidth: 2,
                            tension: 0.3,
                            yAxisID: 'y1'
                        },
                        {
                            type: 'bar',
                            label: 'Registered',
                            data: limitedData.map(d => d.registered),
                            backgroundColor: 'rgba(0, 48, 135, 0.7)',
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            type: 'bar',
                            label: 'Attended',
                            data: limitedData.map(d => d.attended),
                            backgroundColor: 'rgba(255, 184, 0, 0.7)',
                            borderRadius: 4,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                title: function(context) {
                                    return limitedData[context[0].dataIndex].title;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Counts' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            min: 0,
                            max: 100,
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Rate (%)' }
                        }
                    }
                }
            });
        }

        // --- 5. Engagement Scores ---
        const engagementData = @json($engagementScores ?? []);
        if(document.getElementById('engagementChart') && engagementData.length > 0) {
            // Limit to top 8
            const limitedEngData = engagementData.slice(0, 8);
            
            const ctxEng = document.getElementById('engagementChart').getContext('2d');
            new Chart(ctxEng, {
                type: 'bar',
                data: {
                    labels: limitedEngData.map(d => {
                        let title = d.title;
                        return title.length > 20 ? title.substring(0, 20) + '...' : title;
                    }),
                    datasets: [
                        {
                            label: 'Engagement Score',
                            data: limitedEngData.map(d => d.engagement_score),
                            backgroundColor: limitedEngData.map(d => {
                                if(d.engagement_score >= 70) return 'rgba(22, 163, 74, 0.8)'; // green
                                if(d.engagement_score >= 40) return 'rgba(245, 158, 11, 0.8)'; // yellow
                                return 'rgba(239, 68, 68, 0.8)'; // red
                            }),
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return limitedEngData[context[0].dataIndex].title;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            title: { display: true, text: 'Score' }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
