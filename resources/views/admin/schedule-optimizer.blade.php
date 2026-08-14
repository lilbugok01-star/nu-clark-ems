@extends('layouts.app')
@section('title', 'Schedule Optimizer')
@push('styles')
<style>
    .nu-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    .nu-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); background-color: transparent; }
    .nu-card-title { font-weight: 600; color: var(--nu-blue-dk); margin: 0; }
    .nu-table { width: 100%; border-collapse: collapse; }
    .nu-table th { background-color: #f8fafc; color: #475569; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; }
    .nu-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem; vertical-align: top; }
    .fade-in-up { animation: fadeInUp 0.5s ease forwards; opacity: 0; transform: translateY(10px); }
    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
    .score-bar { height: 6px; border-radius: 3px; background-color: #e2e8f0; overflow: hidden; margin-top: 5px; }
    .score-fill { height: 100%; border-radius: 3px; }
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
                    <h1 class="h3 mb-1 fw-bold" style="color: var(--nu-blue);"><i class="bi bi-magic text-gold me-2"></i>Schedule Optimizer</h1>
                    <p class="text-muted mb-0">Find the optimal date and time for your event to maximize attendance</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 fade-in-up" style="animation-delay: 0.1s">
                    <div class="nu-card">
                        <div class="nu-card-header">
                            <h3 class="nu-card-title h6">Event Parameters</h3>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.predictive.schedule') }}" method="GET">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Event Category</label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Select Category</option>
                                        @php $categories = ['Academic', 'Social', 'Sports', 'Cultural', 'Leadership', 'Technology', 'Seminar', 'Workshop', 'Competition', 'Other']; @endphp
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}" {{ ($params['category'] ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Target Venue (Optional)</label>
                                    <input type="text" name="venue" class="form-control" list="venuesList" value="{{ $params['venue'] ?? '' }}" placeholder="e.g. Multi-purpose Hall">
                                    <datalist id="venuesList">
                                        @foreach($venues ?? [] as $v)
                                            <option value="{{ $v }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Expected Capacity</label>
                                    <input type="number" name="capacity" class="form-control" value="{{ $params['capacity'] ?? 100 }}" required min="1">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Search From</label>
                                        <input type="date" name="date_from" class="form-control" value="{{ $params['date_from'] ?? now()->addDays(7)->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Search To</label>
                                        <input type="date" name="date_to" class="form-control" value="{{ $params['date_to'] ?? now()->addDays(21)->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">Duration (Hours)</label>
                                    <input type="number" name="duration" class="form-control" value="{{ $params['duration'] ?? 2 }}" required min="1" max="8">
                                </div>
                                <button type="submit" class="btn btn-nu-blue w-100 fw-bold" style="background-color: var(--nu-blue); color: white;">
                                    <i class="bi bi-search me-2"></i>Find Optimal Slots
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 fade-in-up" style="animation-delay: 0.2s">
                    <div class="nu-card h-100">
                        <div class="nu-card-header">
                            <h3 class="nu-card-title h6">Recommended Time Slots</h3>
                        </div>
                        <div class="table-responsive">
                            @if(isset($recommendations) && count($recommendations) > 0)
                            <table class="nu-table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th class="text-center">Conflicts</th>
                                        <th>Confidence Score</th>
                                        <th>Key Reasons</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recommendations as $rec)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($rec['date'])->format('M d, Y') }}</div>
                                            <div class="small text-muted">{{ $rec['day'] }} • {{ $rec['time'] }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($rec['conflicts'] == 0)
                                                <span class="badge bg-success-subtle text-success border border-success">None</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning">{{ $rec['conflicts'] }} Found</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                                <span>{{ number_format($rec['score'], 0) }}%</span>
                                                <span class="text-muted">{{ $rec['score'] > 85 ? 'Excellent' : ($rec['score'] > 70 ? 'Good' : 'Fair') }}</span>
                                            </div>
                                            <div class="score-bar">
                                                <div class="score-fill bg-{{ $rec['score'] > 85 ? 'success' : ($rec['score'] > 70 ? 'primary' : 'warning') }}" style="width: {{ $rec['score'] }}%"></div>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            <ul class="mb-0 ps-3">
                                                @foreach($rec['reasons'] as $reason)
                                                    <li>{{ $reason }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @elseif(isset($recommendations))
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-calendar-x fs-1 mb-3 d-block text-secondary"></i>
                                <h5>No Optimal Slots Found</h5>
                                <p class="small">Try expanding your date range or adjusting parameters.</p>
                            </div>
                            @else
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-magic fs-1 mb-3 d-block" style="color: var(--nu-gold);"></i>
                                <h5>Awaiting Parameters</h5>
                                <p class="small">Fill out the form on the left to generate schedule recommendations based on historical attendance patterns.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
