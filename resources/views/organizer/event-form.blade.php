@extends('layouts.app')
@section('title', ($event ? 'Edit Event' : 'Create New Event'))
@section('content')
<div class="container py-5" style="max-width:820px">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('organizer.events') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h4 class="fw-800 mb-0" style="color:var(--nu-blue)">
                <i class="bi bi-{{ $event ? 'pencil-square' : 'calendar-plus' }} me-2" style="color:var(--nu-gold)"></i>
                {{ $event ? 'Edit Event' : 'Create New Event' }}
            </h4>
            <p class="text-muted small mb-0">{{ $event ? 'Update event details and venue reservation' : 'Fill in the details to publish your event' }}</p>
        </div>
    </div>

    <form method="POST"
          action="{{ $event ? route('organizer.event.update', $event->id) : route('organizer.event.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if($event) @method('PUT') @endif

        @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-4">
            <i class="bi bi-exclamation-circle me-1"></i>
            Please fix the following errors:
            <ul class="mb-0 mt-1 small">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
        @endif

        @if($event)
        <!-- Shopee-like Status Tracking UI -->
        <div class="nu-card p-4 mb-4">
            <h6 class="fw-bold mb-4" style="color:var(--nu-blue)"><i class="bi bi-geo me-2"></i>Approval Status Tracking</h6>
            
            <div class="position-relative m-4">
                <div class="progress" style="height: 4px; position: absolute; top: 18px; left: 10%; right: 10%; z-index: 1;">
                    @php
                        $statuses = ['pending_adviser', 'pending_dept_head', 'pending_dean', 'pending_director', 'published'];
                        $currentIndex = array_search($event->status, $statuses);
                        if($currentIndex === false) $currentIndex = -1;
                        if($event->status === 'rejected') $currentIndex = -1; // special case
                        $progressWidth = max(0, min(100, ($currentIndex / 4) * 100));
                    @endphp
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressWidth }}%;"></div>
                </div>
                
                <div class="d-flex justify-content-between position-relative" style="z-index: 2;">
                    <!-- Step 1 Adviser -->
                    <div class="text-center" style="width: 20%;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white shadow-sm" style="width: 40px; height: 40px; background-color: {{ $currentIndex >= 0 ? '#198754' : '#dee2e6' }};">
                            <i class="bi bi-{{ $currentIndex >= 1 ? 'check' : 'person' }} fs-5"></i>
                        </div>
                        <div class="fw-bold small {{ $currentIndex >= 0 ? 'text-success' : 'text-muted' }}">Adviser</div>
                    </div>
                    <!-- Step 2 Dept Head -->
                    <div class="text-center" style="width: 20%;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white shadow-sm" style="width: 40px; height: 40px; background-color: {{ $currentIndex >= 1 ? '#198754' : '#dee2e6' }};">
                            <i class="bi bi-{{ $currentIndex >= 2 ? 'check' : 'briefcase' }} fs-5"></i>
                        </div>
                        <div class="fw-bold small {{ $currentIndex >= 1 ? 'text-success' : 'text-muted' }}">Dept Head</div>
                    </div>
                    <!-- Step 3 Dean -->
                    <div class="text-center" style="width: 20%;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white shadow-sm" style="width: 40px; height: 40px; background-color: {{ $currentIndex >= 2 ? '#198754' : '#dee2e6' }};">
                            <i class="bi bi-{{ $currentIndex >= 3 ? 'check' : 'award' }} fs-5"></i>
                        </div>
                        <div class="fw-bold small {{ $currentIndex >= 2 ? 'text-success' : 'text-muted' }}">Dean</div>
                    </div>
                    <!-- Step 4 Director -->
                    <div class="text-center" style="width: 20%;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white shadow-sm" style="width: 40px; height: 40px; background-color: {{ $currentIndex >= 3 ? '#198754' : '#dee2e6' }};">
                            <i class="bi bi-{{ $currentIndex >= 4 ? 'check' : 'star' }} fs-5"></i>
                        </div>
                        <div class="fw-bold small {{ $currentIndex >= 3 ? 'text-success' : 'text-muted' }}">Exec Director</div>
                    </div>
                    <!-- Step 5 Published -->
                    <div class="text-center" style="width: 20%;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white shadow-sm" style="width: 40px; height: 40px; background-color: {{ $currentIndex >= 4 ? '#198754' : '#dee2e6' }};">
                            <i class="bi bi-globe fs-5"></i>
                        </div>
                        <div class="fw-bold small {{ $currentIndex >= 4 ? 'text-success' : 'text-muted' }}">Published</div>
                    </div>
                </div>
            </div>

            @if($event->status === 'rejected')
                <div class="alert alert-danger mt-3 mb-0 small">
                    <i class="bi bi-x-circle-fill me-1"></i> Your event was rejected during the approval process. Please check your notifications.
                </div>
            @endif
        </div>
        @endif

        <div class="row g-4">
            <!-- Event Details Card -->
            <div class="col-12">
                <div class="nu-card p-4">
                    <h6 class="fw-700 mb-3" style="color:var(--nu-blue)"><i class="bi bi-info-circle me-2" style="color:var(--nu-gold)"></i>Event Information</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Event Title *</label>
                            <input type="text" name="title" class="form-control" required
                                   value="{{ old('title', $event->title ?? '') }}" placeholder="e.g. IT Summit 2026">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"
                                      placeholder="Describe your event…">{{ old('description', $event->description ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">-- Select Category --</option>
                                @foreach(['Academic','Social','Sports','Cultural','Leadership','Technology','Seminar','Workshop','Competition','Other'] as $cat)
                                    <option value="{{ $cat }}" {{ old('category', $event->category ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacity (Max attendees) *</label>
                            <input type="number" name="capacity" class="form-control" min="1" required
                                   value="{{ old('capacity', $event->capacity ?? 100) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Venue Card -->
            <div class="col-12">
                <div class="nu-card p-4">
                    <h6 class="fw-700 mb-3" style="color:var(--nu-blue)"><i class="bi bi-building me-2" style="color:var(--nu-gold)"></i>Venue & Schedule</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Venue Type / Room *</label>
                            <select name="venue_type" class="form-select" id="venueTypeSelect" required onchange="updateVenueText()">
                                <option value="">-- Select Venue --</option>
                                @foreach(\App\Models\VenueReservation::venueNames() as $vn)
                                    @if($vn !== 'Other')
                                    <option value="{{ $vn }}" {{ old('venue_type', $event->venue_type ?? '') === $vn ? 'selected' : '' }}>{{ $vn }}</option>
                                    @endif
                                @endforeach
                                <option value="Other" {{ old('venue_type', $event->venue_type ?? '') === 'Other' ? 'selected' : '' }}>Other (Custom Name)</option>
                            </select>
                            <input type="text" name="custom_venue_type" class="form-control mt-2" id="customVenueType"
                                   placeholder="Enter custom venue/room name" style="display:none;"
                                   value="{{ old('custom_venue_type', '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Venue / Location *</label>
                            <input type="text" name="venue" class="form-control" id="venueText" required
                                   value="{{ old('venue', $event->venue ?? '') }}" placeholder="e.g. NU Clark Gymnasium, 2nd Floor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Event Date *</label>
                            <input type="date" name="event_date" class="form-control" required
                                   min="{{ now()->toDateString() }}"
                                   value="{{ old('event_date', $event ? $event->event_date->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" required
                                   value="{{ old('start_time', $event ? substr($event->start_time,0,5) : '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="end_time" class="form-control" required
                                   value="{{ old('end_time', $event ? substr($event->end_time,0,5) : '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Poster & Settings Card -->
            <div class="col-12">
                <div class="nu-card p-4">
                    <h6 class="fw-700 mb-3" style="color:var(--nu-blue)"><i class="bi bi-image me-2" style="color:var(--nu-gold)"></i>Poster & Settings</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Event Poster / Cover Image</label>
                            @if($event && $event->poster_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$event->poster_path) }}" class="rounded-2" style="height:80px;object-fit:cover">
                                <span class="text-muted small ms-2">Current poster (upload new to replace)</span>
                            </div>
                            @endif
                            <input type="file" name="poster" class="form-control" accept="image/*" onchange="previewPoster(event)">
                            <small class="text-muted">JPG, PNG, WEBP · Max 4MB</small>
                            <div id="posterPreview" class="mt-2 d-none">
                                <img id="posterImg" class="rounded-2 w-100" style="max-height:150px;object-fit:cover" alt="Preview">
                            </div>
                        </div>
                        @if($event)
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['published','draft','cancelled','completed'] as $s)
                                    <option value="{{ $s }}" {{ ($event->status ?? 'published') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch pt-3">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1"
                                       {{ old('is_featured', $event->is_featured ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-600 small" for="is_featured">
                                    <i class="bi bi-star me-1" style="color:var(--nu-gold)"></i>Feature on Homepage
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Venue Reservation Notice -->
            <div class="col-12">
                <div class="alert-nu rounded-3">
                    <i class="bi bi-info-circle me-2" style="color:var(--nu-blue)"></i>
                    <strong>Venue Reservation:</strong> After creating this event, you can submit a formal venue reservation request under <a href="{{ route('student_department.dashboard') }}" style="color:var(--nu-blue)">My Venue Reservations</a>. Admin approval required for Gymnasium and Auditorium.
                </div>
            </div>

            <!-- Submit -->
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-nu-blue px-5 fw-700">
                    <i class="bi bi-{{ $event ? 'save' : 'plus-circle' }} me-2"></i>
                    {{ $event ? 'Update Event' : 'Publish Event' }}
                </button>
                <a href="{{ route('organizer.events') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
function updateVenueText() {
    const sel = document.getElementById('venueTypeSelect');
    const txt = document.getElementById('venueText');
    const customInput = document.getElementById('customVenueType');

    if (sel.value === 'Other') {
        customInput.style.display = 'block';
        customInput.disabled = false;
        customInput.required = true;
        customInput.focus();
        // Clear venue text so user fills in custom
        if (!customInput.value) txt.value = '';
    } else {
        customInput.style.display = 'none';
        customInput.disabled = true;
        customInput.required = false;
        customInput.value = '';
        if (sel.value && !txt.value) txt.value = sel.value;
    }
}

// When custom venue input changes, sync to venue text
document.addEventListener('DOMContentLoaded', function() {
    const customInput = document.getElementById('customVenueType');
    if (customInput) {
        customInput.addEventListener('input', function() {
            document.getElementById('venueText').value = this.value;
        });
    }
    // Init: show custom input if 'Other' is pre-selected
    const sel = document.getElementById('venueTypeSelect');
    if (sel && sel.value === 'Other') {
        customInput.style.display = 'block';
        customInput.disabled = false;
        customInput.required = true;
    }
});

function previewPoster(e) {
    const f = e.target.files[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = ev => {
        document.getElementById('posterImg').src = ev.target.result;
        document.getElementById('posterPreview').classList.remove('d-none');
    };
    r.readAsDataURL(f);
}
</script>
@endpush
