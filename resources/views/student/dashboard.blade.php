@extends('layouts.app')
@section('title', 'Student Dashboard')
@section('content')
@php
    /* Pre-process upcoming registrations for JS to avoid @json + multiline array Blade ParseError */
    $jsRegs = $upcoming->map(fn($r) => [
        'id'         => $r->id,
        'event_id'   => $r->event_id,
        'event_name' => $r->event->title,
        'venue'      => $r->event->venue,
        'date'       => $r->event->event_date->toDateString(),
        'start'      => substr($r->event->start_time, 0, 5),
        'end'        => substr($r->event->end_time,   0, 5),
        'qr_url'     => route('student.qr', $r->id),
    ])->values()->toArray();
    $firstName = explode(' ', $user->name)[0];
@endphp

<div class="container-fluid py-4 px-4">
<div class="row g-4">

    {{-- ─── SIDEBAR ─────────────────────────────── --}}
    <div class="col-lg-2 col-md-3">
        <div class="dashboard-sidebar">
            <div class="sidebar-section-label">Student Menu</div>
            <a href="{{ route('student.dashboard') }}" class="sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('student.events') }}" class="sidebar-link {{ request()->routeIs('student.events') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Browse Events
            </a>
            <a href="{{ route('student.my-events') }}" class="sidebar-link {{ request()->routeIs('student.my-events') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated"></i> My Events
            </a>
            <a href="{{ route('student.history') }}" class="sidebar-link {{ request()->routeIs('student.history') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> History
            </a>
            <a href="{{ route('student.profile') }}" class="sidebar-link {{ request()->routeIs('student.profile') ? 'active' : '' }}">
                <i class="bi bi-person"></i> Profile
            </a>
            <hr style="border-color:rgba(255,255,255,.15);margin:.8rem 0">
            <button type="button" class="sidebar-link" style="color:rgba(255,100,100,.85);background:none;border:0;width:100%;text-align:left" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
        </div>
    </div>

    {{-- ─── MAIN CONTENT ─────────────────────────── --}}
    <div class="col-lg-10 col-md-9">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h4 class="fw-800 mb-0" style="color:var(--nu-blue)">
                    <i class="bi bi-hand-wave me-2" style="color:var(--nu-gold)"></i>Welcome, {{ $firstName }}!
                </h4>
                <p class="text-muted small mb-0 mt-1">
                    <i class="bi bi-mortarboard me-1"></i>{{ $user->course->name ?? 'NU Clark' }}
                    @if($user->section)
                        &nbsp;·&nbsp; <span class="badge-category">{{ $user->section->name }}</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('student.events') }}" class="btn btn-nu-blue btn-sm px-3">
                <i class="bi bi-search me-1"></i>Browse Events
            </a>
        </div>

        {{-- Stat Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4 fade-in-up">
                <div class="stat-card" style="border-left-color:var(--nu-blue)">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value">{{ $totalRegistered }}</div>
                            <div class="stat-label">Registered Events</div>
                        </div>
                        <div class="stat-icon" style="background:rgba(0,48,135,.1)">
                            <i class="bi bi-ticket-perforated" style="color:var(--nu-blue);font-size:1.3rem"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 fade-in-up stagger-1">
                <div class="stat-card" style="border-left-color:#16a34a">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value" style="color:#16a34a">{{ $totalAttended }}</div>
                            <div class="stat-label">Events Attended</div>
                        </div>
                        <div class="stat-icon" style="background:rgba(22,163,74,.1)">
                            <i class="bi bi-patch-check" style="color:#16a34a;font-size:1.3rem"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 fade-in-up stagger-2">
                <div class="stat-card" style="border-left-color:var(--nu-gold)">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value text-gold">{{ $totalRegistered > 0 ? round($totalAttended/$totalRegistered*100) : 0 }}%</div>
                            <div class="stat-label">Attendance Rate</div>
                        </div>
                        <div class="stat-icon" style="background:rgba(255,184,0,.1)">
                            <i class="bi bi-graph-up-arrow" style="color:var(--nu-gold);font-size:1.3rem"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upcoming + Notifications --}}
        <div class="row g-4">
            {{-- Upcoming Events --}}
            <div class="col-lg-7">
                <div class="nu-card h-100">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-700 mb-0">
                            <i class="bi bi-calendar-check me-2" style="color:var(--nu-gold)"></i>My Upcoming Events
                        </h6>
                        <a href="{{ route('student.my-events') }}" class="btn btn-outline-secondary btn-sm">View All</a>
                    </div>
                    <div class="p-4">
                        @forelse($upcoming as $reg)
                        <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3"
                             style="background:{{ $reg->event?->isLive() ? 'rgba(255,184,0,.08)' : 'var(--gray-50)' }};border:1px solid {{ $reg->event?->isLive() ? 'rgba(255,184,0,.35)' : 'var(--gray-200)' }}">
                            <div class="text-center" style="min-width:48px">
                                <div class="fw-800 lh-1" style="color:var(--nu-blue);font-size:1.35rem">{{ $reg->event?->event_date?->format('d') ?? '-' }}</div>
                                <div class="text-muted" style="font-size:.68rem">{{ $reg->event?->event_date?->format('M Y') }}</div>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-600 small d-flex align-items-center gap-2 text-truncate">
                                    {{ $reg->event?->title ?? 'Deleted Event' }}
                                    @if($reg->event?->isLive())
                                        <span class="live-badge flex-shrink-0">● LIVE</span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:.74rem">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $reg->event?->venue ?? '-' }}
                                    &nbsp;·&nbsp;
                                    <i class="bi bi-clock me-1"></i>{{ substr($reg->event?->start_time ?? '',0,5) }}
                                </div>
                            </div>
                            <a href="{{ route('student.qr', $reg->id) }}"
                               class="btn {{ $reg->event?->isLive() ? 'btn-gold' : 'btn-nu-blue' }} btn-sm flex-shrink-0"
                               title="View QR Code">
                                <i class="bi bi-qr-code me-1"></i>
                                @if($reg->event?->isLive()) Check In @else QR @endif

                            </a>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x" style="font-size:3rem;color:var(--gray-200)"></i>
                            <p class="text-muted small mt-2 mb-0">No upcoming registered events.
                                <a href="{{ route('student.events') }}" style="color:var(--nu-gold)">Browse events →</a>
                            </p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="col-lg-5">
                <div class="nu-card h-100">
                    <div class="p-4 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-bell" style="color:var(--nu-gold)"></i>
                        <h6 class="fw-700 mb-0">Notifications</h6>
                        @if($unreadCount > 0)
                            <span class="badge ms-auto" style="background:#ef4444;color:#fff;font-size:.68rem">{{ $unreadCount }} new</span>
                        @endif
                    </div>
                    <div class="p-4">
                        @forelse($notifications as $n)
                        <div class="mb-3 p-3 rounded-3"
                             style="background:{{ !$n->read_at ? 'rgba(0,48,135,.04)' : 'var(--gray-50)' }};
                                    border-left:3px solid {{ !$n->read_at ? 'var(--nu-gold)' : 'var(--gray-200)' }}">
                            <div class="small {{ !$n->read_at ? 'fw-600' : '' }}" style="color:var(--gray-800)">{{ $n->title }}</div>
                            <div class="text-muted" style="font-size:.72rem">{{ $n->created_at->diffForHumans() }}</div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash" style="font-size:2.5rem;color:var(--gray-200)"></i>
                            <p class="small text-muted mt-2 mb-0">No notifications yet.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>{{-- end row --}}

        {{-- Events Calendar --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="nu-card p-4">
                    <h6 class="fw-700 mb-3"><i class="bi bi-calendar3 me-2" style="color:var(--nu-gold)"></i>Events Calendar</h6>
                    <x-event-calendar calendarId="studentCalendar" rightToolbar="dayGridMonth,timeGridWeek" />
                </div>
            </div>
        </div>

    </div>{{-- end main col --}}
</div>{{-- end row --}}
</div>{{-- end container --}}

{{-- ─── LIVE EVENT TOAST (fixed bottom-right) ─── --}}
<div class="live-toast-wrap" id="liveEventToast" style="display:none;z-index:9999">
    <div class="live-toast">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="live-badge">● LIVE</span>
                <span class="fw-700 small" style="color:var(--nu-blue)">Event Started!</span>
            </div>
            <button onclick="closeLiveToast()" class="btn-close btn-sm ms-2" style="width:20px;height:20px;font-size:.7rem"></button>
        </div>
        <div id="liveEventName" class="fw-700 mb-1 small" style="color:var(--gray-800)"></div>
        <div id="liveEventVenue" class="text-muted mb-3" style="font-size:.75rem"></div>
        <a id="liveEventLink" href="#" class="btn btn-gold btn-sm w-100 fw-700">
            <i class="bi bi-qr-code me-2"></i>Attend Now — Open QR
        </a>
    </div>
</div>

{{-- ─── LIVE EVENT MODAL ─── --}}
<div class="modal fade" id="liveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius-lg);border:2px solid var(--nu-gold);overflow:hidden">
            <div style="background:linear-gradient(135deg,var(--nu-blue-dk),var(--nu-blue));padding:1.75rem;text-align:center">
                <div class="live-badge mb-2 d-inline-block">● EVENT IS LIVE NOW</div>
                <h5 id="liveModalTitle" class="text-white fw-800 mb-0"></h5>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-3"
                     style="background:rgba(255,184,0,.15);width:64px;height:64px">
                    <i class="bi bi-qr-code-scan" style="color:var(--nu-blue);font-size:1.8rem"></i>
                </div>
                <p id="liveModalVenue" class="text-muted mb-1 small"></p>
                <p class="text-muted small mb-0">The event has started! Scan your QR code or take a selfie to mark your attendance.</p>
            </div>
            <div class="modal-footer justify-content-center gap-2 border-0 pb-4">
                <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Dismiss</button>
                <a id="liveModalLink" href="#" class="btn btn-gold fw-700 px-4">
                    <i class="bi bi-qr-code me-2"></i>Open My QR Code
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ═══ LIVE EVENT DETECTION ══════════════════════════
const shownIds = new Set(JSON.parse(localStorage.getItem('shownLiveIds') || '[]'));
const myRegistrations = @json($jsRegs);

function checkLiveEvents() {
    const now     = new Date();
    const todayStr = now.toISOString().slice(0, 10);
    const timeStr  = now.toTimeString().slice(0, 5);

    myRegistrations.forEach(reg => {
        if (reg.date === todayStr &&
            timeStr >= reg.start &&
            timeStr <= reg.end &&
            !shownIds.has(String(reg.event_id)))
        {
            shownIds.add(String(reg.event_id));
            localStorage.setItem('shownLiveIds', JSON.stringify([...shownIds]));
            showLiveToast(reg);
        }
    });
}

function showLiveToast(reg) {
    document.getElementById('liveEventName').textContent  = reg.event_name;
    document.getElementById('liveEventVenue').innerHTML   = '<i class="bi bi-geo-alt me-1"></i>' + reg.venue;
    document.getElementById('liveEventLink').href         = reg.qr_url;
    document.getElementById('liveEventToast').style.display = 'block';

    // Also show modal for more visibility
    const modal = new bootstrap.Modal(document.getElementById('liveModal'));
    document.getElementById('liveModalTitle').textContent = reg.event_name;
    document.getElementById('liveModalVenue').innerHTML   = '<i class="bi bi-geo-alt me-1"></i>' + reg.venue;
    document.getElementById('liveModalLink').href         = reg.qr_url;
    setTimeout(() => modal.show(), 500);
}

function closeLiveToast() {
    document.getElementById('liveEventToast').style.display = 'none';
}

checkLiveEvents();
setInterval(checkLiveEvents, 30000);
</script>
@endpush
