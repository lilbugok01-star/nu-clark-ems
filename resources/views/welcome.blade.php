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
                    <a href="{{ route('events') }}" class="btn btn-gold w-100 mt-3 fw-700 btn-sm">View All Events →</a>
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

<!-- ── FEATURES ──────────────────────────────────────── -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label">Built for NU Clark</div>
            <div class="section-title">One System, Every Campus Event</div>
            <div class="section-divider mx-auto"></div>
            <p class="text-muted" style="max-width:540px;margin:0 auto">From organizing school activities to tracking student attendance — NU Clark EMS streamlines every step of the campus event lifecycle.</p>
        </div>
        <div class="row g-4">
            @foreach([
                ['bi-calendar-plus','var(--nu-blue)','Event Management','Organizers plan and publish school events with full details — venue, capacity, category, schedules, and event posters — all in one place.'],
                ['bi-qr-code-scan','var(--nu-gold)','QR Code Check-In','Every registered student gets a personal QR code. Scan at the door for instant, contactless attendance verification during active event hours.'],
                ['bi-camera','#6f42c1','Photo Attendance','Students submit a selfie as proof of presence. Organizers review each photo submission and approve or reject with a single click.'],
                ['bi-file-earmark-check','#0d9488','File Hunting & Approvals','Venue reservations follow a structured digital approval chain — from Student Department to Dean — with e-signatures and real-time status tracking.'],
                ['bi-bell-fill','#e11d48','Real-Time Notifications','Students receive instant pop-up alerts the moment an event goes live, keeping the whole campus informed without checking manually.'],
                ['bi-bar-chart-line','#7c3aed','Reports & Analytics','Generate PDF and Excel attendance reports per event. Admins view system-wide stats, monthly trends, and participation data at a glance.'],
            ] as [$icon,$color,$title,$desc])
            <div class="col-md-4 col-sm-6">
                <div class="feature-card h-100">
                    <div class="feature-icon mx-auto mb-3" style="background:{{ $color }}20;box-shadow:none">
                        <i class="bi {{ $icon }}" style="color:{{ $color }};font-size:1.7rem"></i>
                    </div>
                    <h6 class="fw-700 mb-2">{{ $title }}</h6>
                    <p class="text-muted small mb-0">{{ $desc }}</p>
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
        <a href="{{ Auth::user()->role === 'student' ? route('student.events') : route('organizer.events') }}" class="btn btn-gold btn-lg px-5 fw-700">
            <i class="bi bi-calendar-event me-2"></i>Browse Events
        </a>
        @endguest
    </div>
</section>

@endsection
