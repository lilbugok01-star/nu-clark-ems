@extends('layouts.app')
@section('title', 'Predictive Event Details')
@push('styles')
<style>
    .stat-card {
        background: #fff; border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        padding: 1.25rem; border: 1px solid rgba(0,0,0,0.05); height: 100%;
    }
    .stat-value { font-size: 1.75rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem; }
    .stat-label { font-size: 0.875rem; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; }
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
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 col-md-3 p-0">
            @include('layouts.partials.sidebar-admin')
        </div>
        <div class="col-lg-10 col-md-9 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('admin.predictive') }}" class="text-decoration-none small text-muted fw-bold mb-2 d-inline-block"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
                    <h1 class="h3 mb-1 fw-bold" style="color: var(--nu-blue);">{{ $event->title ?? 'Event Title' }}</h1>
                    <p class="text-muted mb-0">Prediction Details & Resource Planning</p>
                </div>
                <div>
                    <a href="{{ route('admin.predictive.resource', $event->id) }}" class="btn btn-nu-blue btn-sm px-3 fw-bold shadow-sm" style="background-color: var(--nu-blue); color: white;"><i class="bi bi-box-seam me-1"></i> Detailed Resource Planner</a>
                </div>
            </div>

            @if(isset($prediction['warnings']) && count($prediction['warnings']) > 0)
            <div class="alert alert-warning border-warning border-start border-4 fade-in-up" style="animation-delay: 0.1s">
                <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Prediction Warnings</h6>
                <ul class="mb-0 small">
                    @foreach($prediction['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="row g-3 mb-4 fade-in-up" style="animation-delay: 0.2s">
                <div class="col-md-3">
                    <div class="stat-card border-top border-primary border-4">
                        <div class="stat-label mb-2">Predicted Attendance</div>
                        <div class="stat-value text-primary">{{ number_format($prediction['predicted_count'] ?? 0) }}</div>
                        <div class="small text-muted">Out of {{ $event->capacity ?? 0 }} capacity</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-top border-info border-4">
                        <div class="stat-label mb-2">Predicted Turnout Rate</div>
                        <div class="stat-value text-info">{{ number_format($prediction['predicted_rate'] ?? 0, 1) }}%</div>
                        <div class="small text-muted">Expected conversion</div>
                    </div>
                </div>
                <div class="col-md-3">
                    @php $util = $prediction['utilization'] ?? 0; @endphp
                    <div class="stat-card border-top border-{{ $util > 90 ? 'danger' : ($util > 70 ? 'warning' : 'success') }} border-4">
                        <div class="stat-label mb-2">Capacity Utilization</div>
                        <div class="stat-value">{{ number_format($util, 1) }}%</div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-{{ $util > 90 ? 'danger' : ($util > 70 ? 'warning' : 'success') }}" style="width: {{ min(100, $util) }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-top border-secondary border-4">
                        <div class="stat-label mb-2">Confidence Level</div>
                        @php $conf = $prediction['confidence'] ?? 'medium'; @endphp
                        <div class="stat-value"><span class="badge badge-soft badge-soft-{{ $conf == 'high' ? 'success' : ($conf == 'medium' ? 'warning' : 'danger') }} fs-5 py-1 px-3">{{ ucfirst($conf) }}</span></div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6 fade-in-up" style="animation-delay: 0.3s">
                    <div class="nu-card h-100 mb-0">
                        <div class="nu-card-header">
                            <h3 class="nu-card-title h5"><i class="bi bi-diagram-3 me-2"></i>Prediction Factors Breakdown</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="nu-table">
                                <thead>
                                    <tr>
                                        <th>Factor</th>
                                        <th class="text-center">Value</th>
                                        <th class="text-center">Weight</th>
                                        <th class="text-center">Data Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($prediction['factors'] ?? [] as $factor)
                                    <tr>
                                        <td class="fw-medium">{{ $factor['name'] ?? '' }}</td>
                                        <td class="text-center">{{ is_numeric($factor['value']) ? number_format($factor['value'], 1).'%' : $factor['value'] }}</td>
                                        <td class="text-center">
                                            <div class="small mb-1">{{ number_format($factor['weight'] ?? 0, 0) }}%</div>
                                            <div class="progress" style="height: 3px;">
                                                <div class="progress-bar bg-secondary" style="width: {{ $factor['weight'] ?? 0 }}%"></div>
                                            </div>
                                        </td>
                                        <td class="text-center small text-muted">{{ $factor['data_points'] ?? 0 }} records</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted">No factor data available.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 fade-in-up" style="animation-delay: 0.4s">
                    <div class="nu-card h-100 mb-0">
                        <div class="nu-card-header">
                            <h3 class="nu-card-title h5"><i class="bi bi-box me-2"></i>Recommended Resource Plan</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="nu-table">
                                <thead>
                                    <tr>
                                        <th>Resource</th>
                                        <th class="text-center">Req. Qty</th>
                                        <th class="text-center">Buffer</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resourcePlan['resources'] ?? [] as $resource)
                                    <tr>
                                        <td class="fw-medium">{{ $resource['name'] ?? '' }}</td>
                                        <td class="text-center fw-bold">{{ $resource['quantity'] ?? 0 }}</td>
                                        <td class="text-center small text-muted">+{{ $resource['buffer_percent'] ?? 0 }}%</td>
                                        <td class="small">{{ $resource['notes'] ?? '' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted">No resource plan available.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nu-card fade-in-up" style="animation-delay: 0.5s">
                <div class="nu-card-header">
                    <h3 class="nu-card-title h6"><i class="bi bi-database me-2"></i>Data Sources</h3>
                </div>
                <div class="p-3 small text-muted">
                    <ul class="mb-0">
                        <li>Historical event data from similar categories</li>
                        <li>Past utilization rates for the selected venue</li>
                        <li>General system-wide attendance averages</li>
                        <li>Day-of-week and time-of-day participation patterns</li>
                        <li>Current registration momentum</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
