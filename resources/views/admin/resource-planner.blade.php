@extends('layouts.app')
@section('title', 'Resource Planning')
@push('styles')
<style>
    .nu-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    .nu-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); background-color: transparent; }
    .nu-card-title { font-weight: 600; color: var(--nu-blue-dk); margin: 0; }
    .nu-table { width: 100%; border-collapse: collapse; }
    .nu-table th { background-color: #f8fafc; color: #475569; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; }
    .nu-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem; }
    .fade-in-up { animation: fadeInUp 0.5s ease forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
</style>
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-2 col-md-3 p-0">
            @include('layouts.partials.sidebar-admin')
        </div>
        <div class="col-lg-10 col-md-9 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 fw-bold" style="color: var(--nu-blue);"><i class="bi bi-box-seam text-gold me-2"></i>Resource Planning</h1>
                    <p class="text-muted mb-0">For: <span class="fw-bold text-dark">{{ $event->title ?? 'Selected Event' }}</span></p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary btn-sm px-3 fw-bold shadow-sm me-2" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
                    <a href="{{ route('admin.predictive.event', $event->id) }}" class="btn btn-nu-blue btn-sm px-3 fw-bold shadow-sm" style="background-color: var(--nu-blue); color: white;">Back to Prediction</a>
                </div>
            </div>

            <div class="row mb-4 fade-in-up" style="animation-delay: 0.1s">
                <div class="col-12">
                    <div class="alert alert-info border-info border-start border-4 d-flex align-items-center mb-0 bg-white shadow-sm">
                        <i class="bi bi-info-circle-fill fs-3 text-info me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Prediction Summary</h6>
                            <p class="mb-0 small">Based on a predicted attendance of <strong>{{ number_format($resourcePlan['predicted_attendance'] ?? 0) }}</strong> out of <strong>{{ $resourcePlan['venue_capacity'] ?? 0 }}</strong> capacity.</p>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($resourcePlan['warnings']) && count($resourcePlan['warnings']) > 0)
            <div class="alert alert-warning border-warning border-start border-4 fade-in-up mb-4" style="animation-delay: 0.15s">
                <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Resource Warnings</h6>
                <ul class="mb-0 small">
                    @foreach($resourcePlan['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="nu-card fade-in-up" style="animation-delay: 0.2s">
                <div class="nu-card-header">
                    <h3 class="nu-card-title h6">Resource Requirements</h3>
                </div>
                <div class="table-responsive">
                    <table class="nu-table">
                        <thead>
                            <tr>
                                <th>Resource Type</th>
                                <th class="text-center">Recommended Quantity</th>
                                <th class="text-center">Buffer %</th>
                                <th>Operational Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resourcePlan['resources'] ?? [] as $resource)
                            <tr>
                                <td class="fw-bold text-dark">{{ $resource['name'] ?? '' }}</td>
                                <td class="text-center fs-5 text-primary fw-bold">{{ $resource['quantity'] ?? 0 }}</td>
                                <td class="text-center small">
                                    <span class="badge bg-light text-secondary border">+{{ $resource['buffer_percent'] ?? 0 }}%</span>
                                </td>
                                <td class="small text-muted">{{ $resource['notes'] ?? '' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No resources recommended for this scale.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
