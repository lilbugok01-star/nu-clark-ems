@extends('layouts.app')

@section('content')<!-- ── HERO ─────────────────────────────────────────── -->
<section class="nu-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-badge"><i class="bi bi-stars me-1"></i>NU Clark Official EMS</div>
                <h1 class="display-4 text-white mb-3" style="font-weight:900;line-height:1.12">
                    Your Campus,<br>
                    <span style="color:var(--nu-gold)">Your Events.</span>
                </h1>
                <p class="mb-4" style="color:rgba(255,255,255,.82);font-size:1.05rem;line-height:1.7">
                    Discover, register, and attend NU Clark events — powered by QR code and photo attendance tracking, real-time live event pop-ups, and smart venue reservations.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-5">
                    <a href="{{ route('events') }}" class="btn btn-gold btn-lg px-4 fw-700">
                        <i class="bi bi-calendar-event me-2"></i>Browse Events
                    </a>
                    @guest
                    <a href="{{ route('register') }}" class="btn btn-lg px-4 fw-600" style="background:rgba(255,255,255,.13);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:var(--radius-sm);backdrop-filter:blur(4px)">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </a>
                    @endguest
                </div>
                <!-- Quick features -->
                <div class="d-flex flex-wrap gap-4">
                    @foreach([['bi-qr-code-scan','QR Check-in'],['bi-building','Venue Booking'],['bi-camera','Photo Attendance'],['bi-bell-fill','Live Alerts']] as [$icon,$label])
                    <div class="d-flex align-items-center gap-2" style="color:rgba(255,255,255,.78)">
                        <i class="bi {{ $icon }}" style="color:var(--nu-gold);font-size:1.1rem"></i>
                        <span class="small fw-500">{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Live event panel -->
                @php
                    $nowStr   = now()->format('H:i:s');
                    $todayStr = now()->toDateString();
                    $liveNow  = \App\Models\Event::published()
                        ->where('event_date', $todayStr)
                        ->where('start_time', '<=', $nowStr)
                        ->where('end_time',   '>=', $nowStr)
                        ->take(2)->get();
                    $upcoming = \App\Models\Event::upcoming()->take(4)->get();
                @endphp
                <div class="hero-glass">
                    @if($liveNow->count())
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="live-badge">● LIVE NOW</span>
                        <span class="small text-white-70">Events in progress</span>
                    </div>
                    @foreach($liveNow as $le)
                    <div class="mb-2 p-3 rounded-3" style="background:rgba(255,184,0,.15);border:1px solid rgba(255,184,0,.3)">
                        <div class="fw-700 text-white small">{{ $le->title }}</div>
                        <div class="small" style="color:var(--nu-gold)"><i class="bi bi-geo-alt me-1"></i>{{ $le->venue }}</div>
                    </div>
                    @endforeach
                    <hr style="border-color:rgba(255,255,255,.12)">
                    @endif
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-calendar3" style="color:var(--nu-gold)"></i>
                        <span class="small fw-600 text-white">Upcoming Events</span>
                    </div>
                    @forelse($upcoming as $uev)
                    <div class="d-flex align-items-center gap-3 mb-2 p-2 rounded-3" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1)">
                        <div class="text-center" style="min-width:38px">
                            <div class="fw-800 lh-1" style="color:var(--nu-gold);font-size:1.15rem">{{ $uev->event_date->format('d') }}</div>
                            <div style="color:rgba(255,255,255,.55);font-size:.62rem">{{ $uev->event_date->format('M') }}</div>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-600 text-white small text-truncate">{{ $uev->title }}</div>
                            <div class="small text-white-70 text-truncate" style="font-size:.72rem">
                                <i class="bi bi-geo-alt me-1"></i>{{ $uev->venue }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center small py-3 mb-0" style="color:rgba(255,255,255,.5)">No upcoming events yet.</p>
                    @endforelse
                    <a href="{{ route('events') }}" class="btn btn-gold w-100 mt-3 fw-700 btn-sm"><i class="bi bi-calendar3 me-1"></i>See Full Schedule →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── STATS BAR ─────────────────────────────────────── -->
@php
    $sTotal     = \App\Models\Event::count();
    $sStudents  = \App\Models\User::where('role','student')->count();
    $sRegs      = \App\Models\Registration::count();
    $sAttended  = \App\Models\Attendance::where('status','verified')->count();
@endphp
<div style="background:var(--nu-blue);padding:2.25rem 0;margin:-1px 0">
    <div class="container">
        <div class="row g-4 text-center text-white">
            @foreach([[$sTotal,'Events Hosted','bi-calendar-check'],[$sStudents,'Students','bi-people'],[$sRegs,'Registrations','bi-ticket-perforated'],[$sAttended,'Verified Attendees','bi-patch-check']] as [$n,$l,$i])
            <div class="col-6 col-md-3">
                <div class="fw-900 mb-1" style="font-size:2.4rem;color:var(--nu-gold);line-height:1">{{ $n }}</div>
                <div class="small" style="color:rgba(255,255,255,.65)"><i class="bi {{ $i }} me-1"></i>{{ $l }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ── HOW IT WORKS ───────────────────────────────────── -->
<section class="py-5" style="background:var(--gray-50)">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label">Student Journey</div>
            <div class="section-title">How It Works</div>
            <div class="section-divider mx-auto"></div>
            <p class="text-muted" style="max-width:500px;margin:0 auto">From discovering an event to having your attendance verified — here's how NU Clark EMS works for every student.</p>
        </div>

        <div class="row g-0 align-items-stretch justify-content-center">
            @php
            $steps = [
                ['01','bi-search','#003087','Browse Events','Explore all upcoming NU Clark campus events. Filter by category, venue, or date and find what interests you.'],
                ['02','bi-person-check','#FFB800','Register','Secure your slot with one click. The system tracks capacity in real time so you never miss out.'],
                ['03','bi-qr-code','#6f42c1','Get Your QR Code','After registering, your personal QR code is generated instantly and available any time from your dashboard.'],
                ['04','bi-camera-fill','#0d9488','Check In on the Day','Scan your QR code at the door or submit a selfie photo as proof of attendance at the event venue.'],
                ['05','bi-patch-check-fill','#e11d48','Get Verified','The organizer approves your check-in. Your attendance record is saved automatically to your profile history.'],
            ];
            @endphp

            @foreach($steps as $i => $step)
            <div class="col-12 col-md">
                <div class="d-flex flex-column align-items-center text-center px-3 py-4 h-100 position-relative">
                    {{-- Connector line --}}
                    @if(!$loop->last)
                    <div class="d-none d-md-block position-absolute" style="top:2.6rem;left:calc(50% + 2.6rem);width:calc(100% - 5.2rem);height:2px;background:linear-gradient(90deg,{{ $step[2] }}55,{{ $steps[$i+1][2] }}55);z-index:0"></div>
                    @endif

                    {{-- Step number badge --}}
                    <div class="position-relative mb-3" style="z-index:1">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                             style="width:56px;height:56px;background:{{ $step[2] }};color:#fff;font-size:1.5rem">
                            <i class="bi {{ $step[1] }}"></i>
                        </div>
                        <span class="position-absolute" style="top:-6px;right:-8px;background:#fff;border:2px solid {{ $step[2] }};color:{{ $step[2] }};font-size:.58rem;font-weight:900;border-radius:50px;padding:1px 5px;line-height:1.4">{{ $step[0] }}</span>
                    </div>

                    <h6 class="fw-700 mb-1" style="font-size:.92rem">{{ $step[3] }}</h6>
                    <p class="text-muted mb-0" style="font-size:.78rem;line-height:1.55;max-width:160px">{{ $step[4] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ── WHO USES THIS SYSTEM ──────────────────────────── -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label">Built for Everyone on Campus</div>
            <div class="section-title">Who Uses NU Clark EMS?</div>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach([
                ['bi-mortarboard-fill','#003087','Students','Browse and register for campus events, receive your QR code, check in via QR or photo, and view your full attendance history — all from your personal dashboard.', ['Browse Events','QR Check-In','Photo Attendance','Attendance History']],
                ['bi-megaphone-fill','#FFB800','Organizers','Create and manage events from start to finish. Review QR scans and photo submissions, track registrations in real time, and export verified attendance reports as PDF or Excel.', ['Create Events','Verify Check-Ins','Live Registrations','Export Reports']],
                ['bi-file-earmark-check-fill','#0d9488','Approvers','Review and digitally sign venue reservation requests as part of the structured File Hunting approval chain — from Student Department, Program Chair, up to the Office of the Dean.', ['Review Requests','E-Signature','Approval Chain','Real-Time Status']],
                ['bi-shield-lock-fill','#7c3aed','Administrators','Oversee the entire system — manage users, maintain event records, configure the approval signatories, import student data via CSV, and monitor full system analytics.', ['User Management','System Analytics','CSV Import','Venue Control']],
            ] as [$icon,$color,$role,$desc,$tags])
            <div class="col-md-6 col-lg-3">
                <div class="h-100 rounded-3 p-4 d-flex flex-column" style="border:2px solid {{ $color }}22;background:{{ $color }}08;transition:all .2s ease">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                             style="width:52px;height:52px;background:{{ $color }}18">
                            <i class="bi {{ $icon }}" style="color:{{ $color }};font-size:1.4rem"></i>
                        </div>
                        <h6 class="fw-800 mb-1" style="color:{{ $color }}">{{ $role }}</h6>
                        <p class="text-muted small mb-3" style="line-height:1.55;font-size:.8rem">{{ $desc }}</p>
                    </div>
                    <div class="mt-auto d-flex flex-wrap gap-1">
                        @foreach($tags as $tag)
                        <span class="badge rounded-pill fw-500" style="background:{{ $color }}18;color:{{ $color }};font-size:.68rem;border:1px solid {{ $color }}33">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ── UPCOMING EVENTS ────────────────────────────────── -->
@php $evCards = \App\Models\Event::upcoming()->take(3)->get(); @endphp
@if($evCards->count())
<section class="py-5" style="background:var(--gray-50)">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
            <div>
                <div class="section-label">Don't Miss Out</div>
                <div class="section-title">Upcoming Events</div>
                <div class="section-divider"></div>
            </div>
            <a href="{{ route('events') }}" class="btn btn-outline-secondary btn-sm">View All →</a>
        </div>
        <div class="row g-4">
            @foreach($evCards as $ev)
            <div class="col-md-4">
                <div class="event-card h-100 position-relative">
                    @if($ev->poster_path)
                        <img src="{{ asset('storage/'.$ev->poster_path) }}" class="event-card-img" alt="{{ $ev->title }}">
                    @else
                        <div class="event-card-img-placeholder">
                            <i class="bi bi-calendar-event text-white" style="font-size:3rem;opacity:.45"></i>
                        </div>
                    @endif
                    @if($ev->is_featured)
                    <div class="position-absolute" style="top:10px;right:10px">
                        <span class="badge fw-700" style="background:var(--nu-gold);color:var(--nu-blue)"><i class="bi bi-star-fill me-1"></i>Featured</span>
                    </div>
                    @endif
                    <div class="event-card-body">
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @if($ev->category)<span class="badge-category">{{ $ev->category }}</span>@endif
                            @if($ev->venue_type)<span class="venue-badge"><i class="bi bi-building me-1"></i>{{ $ev->venue_type }}</span>@endif
                        </div>
                        <h6 class="fw-700 mb-1 text-nu-blue">{{ $ev->title }}</h6>
                        <p class="text-muted small mb-2 flex-grow-1">{{ Str::limit($ev->description, 88) }}</p>
                        <div class="small text-muted"><i class="bi bi-geo-alt me-1 text-nu-blue"></i>{{ $ev->venue }}</div>
                    </div>
                    <div class="event-card-footer d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-700 small text-nu-blue">{{ $ev->event_date->format('M d, Y') }}</div>
                            <div class="text-muted" style="font-size:.72rem"><i class="bi bi-clock me-1"></i>{{ substr($ev->start_time,0,5) }}</div>
                        </div>
                        <a href="{{ route('event.show', $ev->id) }}" class="btn btn-nu-blue btn-sm">Details</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- ── EVENTS CALENDAR ─────────────────────────────────── -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <div class="section-label">Full Schedule</div>
            <div class="section-title">Events Calendar</div>
            <div class="section-divider mx-auto"></div>
            <p class="text-muted" style="max-width:500px;margin:0 auto">Browse all upcoming NU Clark events on an interactive calendar. Click any event for details.</p>
        </div>
        <div class="d-flex flex-wrap gap-3 justify-content-center mb-3">
            <span class="d-flex align-items-center gap-1 small text-muted"><span style="width:12px;height:12px;border-radius:3px;background:#003087;display:inline-block"></span> Published Events</span>
            <span class="d-flex align-items-center gap-1 small text-muted"><span style="width:12px;height:12px;border-radius:3px;background:#28a745;display:inline-block"></span> Approved Venues</span>
            <span class="d-flex align-items-center gap-1 small text-muted"><span style="width:12px;height:12px;border-radius:3px;background:#ffc107;display:inline-block"></span> Pending Venues</span>
        </div>
        <div class="nu-card p-4" style="border-radius:var(--radius-lg)">
            <div id="welcomeCalendar" style="min-height:500px"></div>
        </div>
    </div>
</section>

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var cal = new FullCalendar.Calendar(document.getElementById('welcomeCalendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        buttonText: { today: 'Today', month: 'Month', week: 'Week' },
        events: '{{ route("calendar.events.json") }}',
        eventClick: function(info) { showCalendarEventModal(info); },
        height: 'auto'
    });
    cal.render();
});
</script>
<style>
    #welcomeCalendar .fc .fc-button-primary {
        background-color: var(--nu-blue) !important;
        border-color: var(--nu-blue) !important;
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        padding: 0.4rem 0.8rem !important;
    }
    #welcomeCalendar .fc .fc-button-primary:hover {
        background-color: var(--nu-blue-dk) !important;
        border-color: var(--nu-blue-dk) !important;
    }
    #welcomeCalendar .fc .fc-button-active {
        background-color: var(--nu-gold) !important;
        border-color: var(--nu-gold) !important;
        color: var(--nu-blue) !important;
    }
    #welcomeCalendar .fc .fc-toolbar-title {
        font-size: 1.1rem !important;
        font-weight: 800 !important;
        color: var(--nu-blue);
    }
</style>
@endpush


<!-- ── CTA ───────────────────────────────────────────── -->
<section class="py-5" style="background:linear-gradient(135deg,var(--nu-blue-dk),var(--nu-blue))">
    <div class="container text-center py-2">
        <h2 class="text-white fw-800 mb-2">Ready to join the community?</h2>
        <p class="mb-4" style="color:rgba(255,255,255,.7)">Create your account and never miss an NU Clark event again.</p>
        @guest
        <a href="{{ route('register') }}" class="btn btn-gold btn-lg px-5 fw-700">
            <i class="bi bi-person-plus me-2"></i>Get Started — It's Free
        </a>
        @else
        <a href="{{ route('events') }}" class="btn btn-gold btn-lg px-5 fw-700">
            <i class="bi bi-calendar-event me-2"></i>Browse Events
        </a>
        @endguest
    </div>
</section>

@endsection
