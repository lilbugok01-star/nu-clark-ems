@extends('layouts.app')

@section('title', 'Create Event Proposal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Create Event Proposal</h2>
    <a href="javascript:history.back()" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card nu-card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold" style="color: var(--nu-blue);"><i class="bi bi-calendar-event me-2"></i>Event Information</h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">{{ $event->title }}</h6>
                <p class="text-muted small mb-3">{{ ucfirst($event->category) }}</p>
                
                <div class="mb-2">
                    <i class="bi bi-clock text-primary me-2"></i>
                    <span class="small">{{ \Carbon\Carbon::parse($event->date_time)->format('F d, Y h:i A') }}</span>
                </div>
                <div class="mb-2">
                    <i class="bi bi-geo-alt text-danger me-2"></i>
                    <span class="small">{{ $event->venue }}</span>
                </div>
                
                <hr>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="fw-bold text-muted small">Current Budget Estimate:</span>
                    <span class="fw-bold fs-5" style="color: var(--nu-gold);">₱{{ number_format($estimatedBudget, 2) }}</span>
                </div>
                <div class="text-muted small text-end">{{ $budgetItems->count() }} item(s)</div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card nu-card border-0 shadow-sm">
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('proposal.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                    
                    <div class="mb-4">
                        <label for="event_overview" class="form-label fw-bold">Event Overview <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="event_overview" name="event_overview" rows="3" required placeholder="Provide a brief overview of the event...">{{ old('event_overview', $event->description) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="objectives" class="form-label fw-bold">Objectives <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="objectives" name="objectives" rows="3" required placeholder="What are the goals of this event?">{{ old('objectives') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="target_audience" class="form-label fw-bold">Target Audience</label>
                        <textarea class="form-control" id="target_audience" name="target_audience" rows="2" placeholder="Who should attend this event?">{{ old('target_audience') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="estimated_budget" class="form-label fw-bold">Estimated Budget (₱) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="estimated_budget" name="estimated_budget" required value="{{ old('estimated_budget', $estimatedBudget) }}">
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="venue_details" class="form-label fw-bold">Venue Details</label>
                            <textarea class="form-control" id="venue_details" name="venue_details" rows="2" placeholder="Specific venue requirements...">{{ old('venue_details', $event->venue) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="schedule_details" class="form-label fw-bold">Schedule Details</label>
                            <textarea class="form-control" id="schedule_details" name="schedule_details" rows="2" placeholder="Event flow or timeline...">{{ old('schedule_details', \Carbon\Carbon::parse($event->date_time)->format('M d, Y h:i A')) }}</textarea>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="requirements" class="form-label fw-bold">Requirements / Logistics</label>
                        <textarea class="form-control" id="requirements" name="requirements" rows="2" placeholder="Equipment, catering, security, etc...">{{ old('requirements') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="expected_outcomes" class="form-label fw-bold">Expected Outcomes</label>
                        <textarea class="form-control" id="expected_outcomes" name="expected_outcomes" rows="2" placeholder="What results do you expect?">{{ old('expected_outcomes') }}</textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" style="background-color: var(--nu-blue); border-color: var(--nu-blue);">
                            <i class="bi bi-save me-2"></i>Save Proposal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
