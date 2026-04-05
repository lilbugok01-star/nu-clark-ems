@extends('layouts.app')
@section('title', 'Project Venue Reservations')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-800 mb-0" style="color:var(--nu-blue)"><i class="bi bi-building me-2" style="color:var(--nu-gold)"></i>Venue Reservations</h4>
            <p class="text-muted small mb-0">Track your facility requests like a parcel</p>
        </div>
        {{-- E-Signature Upload --}}
        <form action="{{ route('student_department.signature.upload') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
            @csrf
            <div>
                @php $sigUser = Auth::user(); @endphp
                @if($sigUser->e_signature_path)
                    <img src="{{ asset('storage/'.$sigUser->e_signature_path) }}" alt="My Signature" style="max-height:40px;border:1px solid var(--gray-200);border-radius:6px;padding:2px 6px;background:#fff">
                @endif
                <label class="btn btn-outline-secondary btn-sm ms-1" style="cursor:pointer">
                    <i class="bi bi-pen me-1" style="color:var(--nu-blue)"></i>{{ $sigUser->e_signature_path ? 'Update Signature' : 'Upload Signature' }}
                    <input type="file" name="signature" accept="image/*" class="d-none" onchange="this.closest('form').submit()">
                </label>
            </div>
        </form>
    </div>

    <!-- Facility Overview -->
    <div class="row g-3 mb-4">
        @foreach([
            ['name' => 'NU Clark Gymnasium', 'icon' => 'trophy'],
            ['name' => 'NU Clark Auditorium', 'icon' => 'mic'],
            ['name' => 'NU Clark Library',    'icon' => 'book'],
            ['name' => 'Mini Chapel',         'icon' => 'moon-stars'],
            ['name' => '4th Floor Rooms',     'icon' => 'door-open'],
            ['name' => '5th Floor Rooms',     'icon' => 'door-open'],
            ['name' => '6th Floor Rooms',     'icon' => 'door-open'],
            ['name' => '7th & 8th Floor',     'icon' => 'door-open']
        ] as $vn)
        <div class="col-md-3 col-6">
            <div class="venue-card text-center">
                <i class="bi bi-{{ $vn['icon'] }} mb-2" style="font-size:1.6rem;color:var(--nu-blue)"></i>
                <div class="fw-600 small">{{ $vn['name'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <style>
        .fc .fc-button-primary {
            background-color: var(--nu-blue) !important;
            border-color: var(--nu-blue) !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            padding: 0.4rem 0.8rem !important;
            transition: all 0.2s ease !important;
        }
        .fc .fc-button-primary:hover {
            background-color: var(--nu-blue-dk) !important;
            border-color: var(--nu-blue-dk) !important;
            transform: translateY(-1px);
        }
        .fc .fc-button-active {
            background-color: var(--nu-gold) !important;
            border-color: var(--nu-gold) !important;
            color: var(--nu-blue) !important;
        }
        .fc .fc-toolbar-title {
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            color: var(--nu-blue);
        }
        .fc-icon {
            font-size: 1.25em !important;
        }
    </style>

    <!-- Venue Availability Calendar -->
    <div class="nu-card p-4 mb-4">
        <h6 class="fw-700 mb-3"><i class="bi bi-calendar3 me-2" style="color:var(--nu-gold)"></i>Venue Availability Calendar</h6>
        <div id="calendar" style="min-height: 500px;"></div>
    </div>
    
    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day',
                    prev: 'Prev',
                    next: 'Next'
                },
                slotMinTime: '06:00:00',
                slotMaxTime: '23:00:00',
                events: '{{ route("student_department.venue.events.json") }}',
                eventClick: function(info) {
                    alert('Venue: ' + info.event.extendedProps.venue + '\nStatus: ' + info.event.extendedProps.status);
                }
            });
            calendar.render();
        });

        function toggleCustomFields() {
            const evSelect = document.getElementById('event_select');
            const evInput = document.getElementById('custom_event_title');
            if (evSelect.value === 'custom') {
                evInput.style.display = 'block';
                evInput.disabled = false;
                evInput.required = true;
            } else {
                evInput.style.display = 'none';
                evInput.disabled = true;
                evInput.required = false;
            }

            const vnSelect = document.getElementById('venue_select');
            const vnInput = document.getElementById('custom_venue_name');
            if (vnSelect.value === 'Other') {
                vnInput.style.display = 'block';
                vnInput.disabled = false;
                vnInput.required = true;
            } else {
                vnInput.style.display = 'none';
                vnInput.disabled = true;
                vnInput.required = false;
            }
        }
    </script>
    @endpush

    <!-- My Reservations -->
    <div class="nu-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-700 mb-0"><i class="bi bi-list-check me-2" style="color:var(--nu-gold)"></i>My Venue Requests</h6>
            <button class="btn btn-sm btn-nu-blue" data-bs-toggle="modal" data-bs-target="#newReservationModal">
                <i class="bi bi-plus me-1"></i>New Request
            </button>
        </div>
        
        <div class="border-bottom">
            <ul class="nav nav-tabs nav-justified border-0" id="reservationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-3 border-0 rounded-0" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-requests" type="button" role="tab" style="color:var(--nu-blue)">
                        Active Requests @if($activeReservations->count() > 0)<span class="badge bg-danger ms-1">{{ $activeReservations->count() }}</span>@endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3 border-0 rounded-0 text-muted" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-requests" type="button" role="tab">
                        Request History
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="reservationTabsContent">
            <!-- ACTIVE REQUESTS TAB -->
            <div class="tab-pane fade show active p-4" id="active-requests" role="tabpanel">
                @forelse($activeReservations as $res)
                <div class="reservation-track-card border rounded-3 p-4 mb-4 bg-white shadow-sm position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-4 border-bottom pb-3">
                        <div>
                            <h5 class="fw-800 mb-1" style="color:var(--nu-blue)">{{ $res->event?->title ?? $res->event_title ?? 'Untitled Event' }}</h5>
                            <div class="d-flex gap-3 flex-wrap small text-muted">
                                <span><i class="bi bi-building me-1"></i>{{ $res->venue_name }}</span>
                                <span><i class="bi bi-calendar-event me-1"></i>{{ $res->reserved_date->format('M d, Y') }}</span>
                                <span><i class="bi bi-clock me-1"></i>{{ substr($res->start_time,0,5) }} – {{ substr($res->end_time,0,5) }}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i>Processing</span>
                            <div class="mt-2 d-flex justify-content-end gap-2">
                                <a href="{{ route('student_department.venue.form', $res->id) }}" class="btn btn-sm btn-outline-secondary fw-bold px-3 py-1 rounded-pill" style="font-size: 0.7rem;">
                                    <i class="bi bi-eye me-1"></i> View Form
                                </a>
                                @if(str_starts_with($res->status, 'pending_'))
                                <form action="{{ route('student_department.venue.delete', $res->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel and delete this request?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3 py-1 rounded-pill" style="font-size: 0.7rem;" title="Cancel Request">
                                        <i class="bi bi-trash me-1"></i> Cancel
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Shopee-style Tracker Integration (Dynamic File Hunting) -->
                    @php
                        $currentStep = 0;
                        $isRejected = false;
                        $isFullyApproved = false;
                        
                        // Find the index of the current 'pending_' role
                        $matchRole = str_replace('pending_', '', $res->status);
                        
                        // Some statuses have slight naming differences from roles
                        if ($matchRole === 'student_dev') $matchRole = 'student_development';
                        if ($matchRole === 'director')    $matchRole = 'executive_director';
                        if ($matchRole === 'dept_head')   $matchRole = 'department_head';
                        
                        $sigStep = $signatories->firstWhere('role', $matchRole);
                        if ($sigStep) {
                            $currentStep = $sigStep->step_order;
                        }
                        
                        $totalSteps = $signatories->count() + 1; // +1 for Final Approved
                    @endphp

                    <div class="tracker-wrapper mt-2 mb-2 overflow-auto px-2 py-1">
                        <div class="tracker-steps d-flex justify-content-between position-relative" style="min-width: 600px;">
                            <!-- Progress Line Fixed: top is 20px so it centers directly behind 40px icons -->
                            <div class="progress-line position-absolute start-0 w-100" style="top: 20px; height: 4px; background: #e9ecef; z-index: 1;"></div>
                            <div class="progress-line-fill position-absolute start-0 transition-all" 
                                 style="top: 20px; height: 4px; background: var(--nu-blue); z-index: 2; width: {{ (($currentStep > 0 ? $currentStep - 1 : 0) * (100 / max(1, $totalSteps - 1))) }}%;"></div>

                            <!-- Dynamic Steps -->
                            @foreach($signatories as $index => $sig)
                                @php
                                    $stepNum = $sig->step_order;
                                    $isPast = $currentStep > $stepNum;
                                    $isCurrent = $currentStep === $stepNum;
                                    
                                    // Check if opened
                                    $approvalDoc = $res->approvals->where('role_level', $sig->role)->first();
                                    $hasOpened = $approvalDoc && $approvalDoc->opened_at;
                                    
                                    $iconClass = 'bi-person-circle';
                                    if ($sig->role === 'student_development') $iconClass = 'bi-person-check';
                                    if ($sig->role === 'program_chair') $iconClass = 'bi-mortarboard';
                                    if ($sig->role === 'dean') $iconClass = 'bi-journal-check';
                                    if ($sig->role === 'executive_director') $iconClass = 'bi-shield-check';
                                    if ($sig->role === 'adviser') $iconClass = 'bi-people';
                                    if ($sig->role === 'department_head') $iconClass = 'bi-briefcase';

                                    $bgClass = 'bg-white text-muted border-light';
                                    $textClass = 'text-muted';
                                    
                                    if ($isPast) {
                                        $bgClass = 'bg-primary text-white border-primary';
                                        $textClass = 'text-primary';
                                    } elseif ($isCurrent) {
                                        if ($hasOpened) {
                                            $bgClass = 'bg-warning text-dark border-warning shadow-sm';
                                            $textClass = 'text-warning';
                                        } else {
                                            $bgClass = 'bg-white text-primary';
                                            $textClass = 'text-primary';
                                        }
                                    }
                                @endphp
                                <div class="step text-center position-relative" style="z-index: 3; width: 100px;">
                                    <div class="step-icon mx-auto rounded-circle d-flex align-items-center justify-content-center border-4 {{ $bgClass }}" 
                                         style="width: 40px; height: 40px; border-color: {{ $isCurrent && !$hasOpened ? 'var(--nu-blue) !important' : '' }}">
                                        <i class="bi {{ $iconClass }} fs-5"></i>
                                    </div>
                                    <div class="step-label small fw-bold mt-2 {{ $textClass }}">
                                        {{ $sig->position_label }}
                                        @if($isCurrent && $hasOpened)
                                            <div style="font-size:0.65rem; line-height:1.1;" class="text-warning mt-1"><i class="bi bi-eye"></i> Opened</div>
                                        @elseif($isCurrent && !$hasOpened)
                                            <div style="font-size:0.65rem; line-height:1.1;" class="text-primary mt-1">Pending</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <!-- Step Final: Approved -->
                            <div class="step text-center position-relative" style="z-index: 3; width: 80px;">
                                <div class="step-icon mx-auto rounded-circle d-flex align-items-center justify-content-center border-4 bg-white text-muted border-light" style="width: 40px; height: 40px;">
                                    <i class="bi bi-flag fs-5"></i>
                                </div>
                                <div class="step-label small fw-bold mt-2 text-muted">Finished</div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-building" style="font-size:2rem;opacity:.3"></i>
                    <p class="small mt-2 mb-0">No active reservations.</p>
                </div>
                @endforelse
            </div>

            <!-- HISTORY TAB -->
            <div class="tab-pane fade p-4" id="history-requests" role="tabpanel">
                @forelse($historyReservations as $res)
                <div class="reservation-track-card border rounded-3 p-4 mb-4 bg-light shadow-sm">
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                        <div>
                            <h5 class="fw-800 mb-1" style="{{ $res->status === 'approved' ? 'color:var(--nu-blue)' : 'color:#6c757d' }}">
                                {{ $res->event?->title ?? $res->event_title ?? 'Untitled Event' }}
                            </h5>
                            <div class="d-flex gap-3 flex-wrap small text-muted">
                                <span><i class="bi bi-building me-1"></i>{{ $res->venue_name }}</span>
                                <span><i class="bi bi-calendar-event me-1"></i>{{ $res->reserved_date->format('M d, Y') }}</span>
                                <span><i class="bi bi-clock me-1"></i>{{ substr($res->start_time,0,5) }} – {{ substr($res->end_time,0,5) }}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            @if($res->status === 'approved')
                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i>Fully Approved</span>
                                <div class="mt-2">
                                    <a href="{{ route('student_department.venue.form', $res->id) }}" class="btn btn-sm btn-outline-primary fw-bold px-3 py-1 rounded-pill">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> View Permission Form
                                    </a>
                                </div>
                            @elseif($res->status === 'rejected')
                                <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i>Declined</span>
                            @elseif($res->status === 'cancelled')
                                <span class="badge bg-secondary px-3 py-2 rounded-pill"><i class="bi bi-slash-circle me-1"></i>Cancelled</span>
                            @endif
                        </div>
                    </div>

                    @if($res->status === 'rejected' && $res->approvals->where('status', 'rejected')->isNotEmpty())
                        @php $rejectedNode = $res->approvals->where('status', 'rejected')->last(); @endphp
                        <div class="alert alert-danger mb-0 border-0 rounded-3 d-flex align-items-start gap-3 p-3">
                            <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                            <div>
                                <div class="fw-bold text-danger">Declined by {{ $rejectedNode->approver->name ?? 'Approver' }}</div>
                                <p class="mb-0 small text-danger">{{ $rejectedNode->comments ?? 'No comment provided.' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="small text-muted"><i class="bi bi-info-circle me-1"></i> This request has completed the approval process.</div>
                    @endif
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-archive" style="font-size:2rem;opacity:.3"></i>
                    <p class="small mt-2 mb-0">No past reservations found.</p>
                </div>
                @endforelse
            </div>
        </div>
        </div>
    </div>
</div>

<!-- New Reservation Modal -->
<div class="modal fade" id="newReservationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-3">
            <div class="modal-header" style="background:var(--nu-blue);border:none">
                <h5 class="modal-title text-white fw-700"><i class="bi bi-building me-2" style="color:var(--nu-gold)"></i>Request Venue Reservation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('student_department.venue.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Link to Event *</label>
                            <select name="event_id" id="event_select" class="form-select" required onchange="toggleCustomFields()">
                                <option value="">-- Select your event --</option>
                                @foreach($myEvents as $ev)
                                    <option value="{{ $ev->id }}">{{ $ev->title }} ({{ $ev->event_date->format('M d') }})</option>
                                @endforeach
                                <option value="custom">Not on the list (Custom Event)</option>
                            </select>
                            <input type="text" name="event_title" id="custom_event_title" class="form-control mt-2" placeholder="Enter custom event title" style="display:none;" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Venue / Room *</label>
                            <select name="venue_name" id="venue_select" class="form-select" required onchange="toggleCustomFields()">
                                <option value="">-- Select venue --</option>
                                @foreach(\App\Models\VenueReservation::venueNames() as $vn)
                                    <option value="{{ $vn }}">{{ $vn }}</option>
                                @endforeach
                                <option value="Other">Other (Custom Name)</option>
                            </select>
                            <input type="text" name="custom_venue_name" id="custom_venue_name" class="form-control mt-2" placeholder="Enter custom venue name" style="display:none;" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reserved Date *</label>
                            <input type="date" name="reserved_date" class="form-control" min="{{ now()->addDays(15)->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" min="08:00" max="22:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="end_time" class="form-control" min="08:00" max="22:00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expected Attendees</label>
                            <input type="number" name="expected_attendees" class="form-control" min="1" value="50">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Purpose / Notes</label>
                            <textarea name="purpose" class="form-control" rows="2" placeholder="Briefly describe the purpose or special requirements…"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-nu-blue fw-700"><i class="bi bi-send me-1"></i>Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>



<style>
    .transition-all { transition: all 0.3s ease; }
    .progress-line-fill { transition: width 0.8s ease-in-out; }
    .step-icon { border-style: solid; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .reservation-track-card:hover { border-color: var(--nu-blue) !important; }
    .venue-card {
        background: white;
        border: 1px solid var(--gray-200);
        padding: 1.2rem;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .venue-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        border-color: var(--nu-blue);
    }
</style>
@endsection
