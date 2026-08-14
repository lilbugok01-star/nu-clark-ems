@extends('layouts.app')
@section('title', 'Student Analytics — ' . $student->full_name)
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
                <div>
                    <h4 class="fw-bold mb-1" style="color:var(--nu-blue)"><i class="bi bi-person-lines-fill me-2"></i>Student Analytics</h4>
                    <p class="text-muted mb-0">Participation profile for <strong>{{ $student->full_name }}</strong></p>
                </div>
                <a href="{{ route('admin.analytics') }}" class="btn btn-outline-gold btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Analytics</a>
            </div>

            <!-- Student Info -->
            <div class="nu-card p-4 mb-4 fade-in-up">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-person-circle text-gold me-2"></i>Student Profile</h6>
                        <table class="table table-borderless mb-0">
                            <tr><td class="text-muted" style="width:140px">Name</td><td class="fw-semibold">{{ $student->full_name }}</td></tr>
                            <tr><td class="text-muted">Student ID</td><td>{{ $student->student_id ?? 'N/A' }}</td></tr>
                            <tr><td class="text-muted">Email</td><td>{{ $student->email }}</td></tr>
                            <tr><td class="text-muted">Course</td><td>{{ $student->course->code ?? 'N/A' }} — {{ $student->course->name ?? '' }}</td></tr>
                            <tr><td class="text-muted">Section</td><td>{{ $student->section->name ?? 'N/A' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-card" style="border-color:var(--nu-blue)">
                                    <div class="stat-value" style="color:var(--nu-blue)">{{ $profile['events_count'] }}</div>
                                    <div class="stat-label">Events Registered</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card" style="border-color:#28a745">
                                    <div class="stat-value" style="color:#28a745">{{ $profile['attendance_rate'] }}%</div>
                                    <div class="stat-label">Attendance Rate</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Interests -->
            <div class="row g-4">
                <div class="col-lg-6 fade-in-up" style="animation-delay:0.1s">
                    <div class="nu-card p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-tag text-gold me-2"></i>Category Interests</h6>
                        @if(!empty($profile['categories']))
                            @foreach($profile['categories'] as $cat => $count)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background:var(--gray-100)">
                                <span class="fw-semibold">{{ $cat }}</span>
                                <span class="badge bg-nu-blue">{{ $count }} event{{ $count > 1 ? 's' : '' }}</span>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center mb-0">No category data available.</p>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6 fade-in-up" style="animation-delay:0.15s">
                    <div class="nu-card p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-star text-gold me-2"></i>Top Preferences</h6>
                        <div class="mb-3">
                            <span class="text-muted small">Primary Interest</span>
                            <div class="fw-bold fs-5" style="color:var(--nu-blue)">{{ $profile['most_category'] ?? 'Not enough data' }}</div>
                        </div>
                        <div>
                            <span class="text-muted small">Secondary Interest</span>
                            <div class="fw-bold fs-5" style="color:var(--nu-gold)">{{ $profile['secondary'] ?? 'Not enough data' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($profile['categories']))
            <!-- Category Distribution Chart -->
            <div class="nu-card p-4 mt-4 fade-in-up" style="animation-delay:0.2s">
                <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart text-gold me-2"></i>Participation Distribution</h6>
                <div style="max-width:400px;margin:0 auto">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(!empty($profile['categories']))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const catLabels = @json(array_keys($profile['categories']));
const catData   = @json(array_values($profile['categories']));
const catColors = ['#003087','#C8962E','#28a745','#17a2b8','#6f42c1','#fd7e14','#dc3545','#20c997','#e83e8c','#6610f2'];

new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: catLabels,
        datasets: [{
            data: catData,
            backgroundColor: catColors.slice(0, catLabels.length),
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } }
        }
    }
});
</script>
@endpush
@endif
@endsection
