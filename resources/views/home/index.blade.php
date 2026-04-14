@extends('layouts.app')

@section('title', 'NU Clark Events — Home')
@section('meta_description', 'Discover and register for upcoming events at National University Clark Campus.')

@section('content')

<!-- Hero -->
<section class="hero-section position-relative">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7 fade-in-up">
                <div class="hero-badge"><i class="bi bi-mortarboard-fill me-1"></i> National University Clark</div>
                <h1 class="hero-title">
                    Your Campus,<br>
                    Your Events.<br>
                    <span class="text-gold">All in One Place.</span>
                </h1>
                <p class="text-white-75 fs-5 mb-4">Browse upcoming events, register with one click, and check in with your personalized QR code. Seamless event management for NU Clark students and faculty.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('events') }}" class="btn btn-gold btn-lg px-4 fw-bold">
                        <i class="bi bi-calendar3 me-2"></i>Browse Events
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-person-plus me-2"></i>Get Started
                        </a>
                    @endguest
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center fade-in-up stagger-2">
                <div class="text-center">
                    <div style="font-size:10rem; line-height:1; filter: drop-shadow(0 10px 30px rgba(255,184,0,0.3));">🎓</div>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row mt-5 g-3">
            <div class="col-6 col-md-3 fade-in-up stagger-1">
                <div class="text-center p-3 rounded-xl" style="background:rgba(255,255,255,0.1);">
                    <div class="text-gold fs-2 fw-bold">{{ $totalEvents }}</div>
                    <div class="text-white-75 small">Total Events</div>
                </div>
            </div>
            <div class="col-6 col-md-3 fade-in-up stagger-2">
                <div class="text-center p-3 rounded-xl" style="background:rgba(255,255,255,0.1);">
                    <div class="text-gold fs-2 fw-bold">{{ $totalStudents }}</div>
                    <div class="text-white-75 small">Students</div>
                </div>
            </div>
            <div class="col-6 col-md-3 fade-in-up stagger-3">
                <div class="text-center p-3 rounded-xl" style="background:rgba(255,255,255,0.1);">
                    <div class="text-gold fs-2 fw-bold"><i class="bi bi-qr-code"></i></div>
                    <div class="text-white-75 small">QR Check-in</div>
                </div>
            </div>
            <div class="col-6 col-md-3 fade-in-up stagger-4">
                <div class="text-center p-3 rounded-xl" style="background:rgba(255,255,255,0.1);">
                    <div class="text-gold fs-2 fw-bold"><i class="bi bi-camera"></i></div>
                    <div class="text-white-75 small">Photo Verification</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Events -->
@if($featuredEvents->count() > 0)
<section class="py-5">
    <div class="container">
        <div class="section-header">
            <h2><i class="bi bi-star-fill text-gold me-2"></i>Featured Events</h2>
            <div class="section-divider"></div>
        </div>
        <div class="row g-4">
            @foreach($featuredEvents as $i => $event)
            <div class="col-md-4 fade-in-up" style="animation-delay: {{ $i * 0.1 }}s">
                <div class="nu-card h-100">
                    @if($event->poster_path)
                        <img src="{{ \App\Helpers\StorageUrl::url($event->poster_path) }}" class="event-card-img" alt="{{ $event->title }}">
                    @else
                        <div class="event-card-img-placeholder"><i class="bi bi-calendar-event"></i></div>
                    @endif
                    <div class="p-4">
                        <span class="badge-category mb-2 d-inline-block">{{ $event->category ?? 'General' }}</span>
                        <h5 class="fw-bold mb-2">{{ $event->title }}</h5>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-geo-alt text-gold me-1"></i>{{ $event->venue }}<br>
                            <i class="bi bi-calendar text-gold me-1"></i>{{ $event->event_date->format('M d, Y') }}
                            · {{ substr($event->start_time, 0, 5) }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="small text-muted">
                                <i class="bi bi-people me-1"></i>{{ $event->registered_count ?? 0 }}/{{ $event->capacity }}
                            </span>
                            <a href="{{ route('event.show', $event->id) }}" class="btn btn-nu-blue btn-sm">View Event</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Upcoming Events -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div class="section-header mb-0">
                <h2><i class="bi bi-calendar3 me-2" style="color:var(--nu-blue)"></i>Upcoming Events</h2>
                <div class="section-divider"></div>
            </div>
            <a href="{{ route('events') }}" class="btn btn-outline-gold btn-sm">View All <i class="bi bi-arrow-right"></i></a>
        </div>

        @if($upcomingEvents->count() > 0)
        <div class="row g-4">
            @foreach($upcomingEvents as $i => $event)
            <div class="col-md-6 col-lg-4 fade-in-up" style="animation-delay: {{ $i * 0.08 }}s">
                <div class="nu-card h-100">
                    @if($event->poster_path)
                        <img src="{{ \App\Helpers\StorageUrl::url($event->poster_path) }}" class="event-card-img" alt="{{ $event->title }}">
                    @else
                        <div class="event-card-img-placeholder"><i class="bi bi-calendar-event"></i></div>
                    @endif
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge-category">{{ $event->category ?? 'General' }}</span>
                            @if($event->is_full)
                                <span class="badge bg-danger text-white" style="font-size:0.7rem;">Full</span>
                            @endif
                        </div>
                        <h6 class="fw-bold mb-1">{{ $event->title }}</h6>
                        <p class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i>{{ $event->venue }}</p>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-calendar me-1"></i>{{ $event->event_date->format('M d, Y') }}
                            <i class="bi bi-clock ms-2 me-1"></i>{{ substr($event->start_time, 0, 5) }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-people me-1"></i>{{ $event->registered_count }}/{{ $event->capacity }}
                            </small>
                            <a href="{{ route('event.show', $event->id) }}" class="btn btn-nu-blue btn-sm">Details</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <div style="font-size: 4rem;">📅</div>
            <h5 class="text-muted mt-3">No upcoming events yet.</h5>
            <p class="text-muted small">Check back soon!</p>
        </div>
        @endif
    </div>
</section>

<!-- CTA Section -->
@guest
<section class="py-5" style="background: linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light))">
    <div class="container text-center text-white">
        <h2 class="fw-bold mb-2">Ready to join the community?</h2>
        <p class="text-white-75 mb-4">Create your NU Clark Events account and never miss an event again.</p>
        <a href="{{ route('register') }}" class="btn btn-gold btn-lg px-5 fw-bold">
            <i class="bi bi-person-plus me-2"></i>Register Now — It's Free
        </a>
    </div>
</section>
@endguest

@endsection
