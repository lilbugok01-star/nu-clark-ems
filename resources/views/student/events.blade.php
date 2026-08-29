@extends('layouts.app')
@section('title', 'Browse Events')
@section('content')
<div class="container py-5">
    <!-- Header + Filters -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-800 mb-0" style="color:var(--nu-blue)"><i class="bi bi-calendar3 me-2" style="color:var(--nu-gold)"></i>Upcoming Events</h4>
            <p class="text-muted small mb-0">Discover and register for NU Clark events</p>
        </div>
    </div>

    <!-- Tabs: Upcoming vs Past Events -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? 'upcoming') === 'upcoming' ? 'active' : '' }}"
               href="{{ route('student.events', array_merge(request()->except('page'), ['tab' => 'upcoming'])) }}"
               style="{{ ($tab ?? 'upcoming') === 'upcoming' ? 'background:var(--nu-blue);color:#fff;font-weight:700;' : 'background:var(--gray-100);color:var(--gray-700);font-weight:600;' }}">
                <i class="bi bi-calendar-check me-1"></i> Upcoming Events
                <span class="badge {{ ($tab ?? 'upcoming') === 'upcoming' ? 'bg-light text-dark' : 'bg-secondary text-white' }} ms-1">{{ $upcomingCount ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? 'upcoming') === 'past' ? 'active' : '' }}"
               href="{{ route('student.events', array_merge(request()->except('page'), ['tab' => 'past'])) }}"
               style="{{ ($tab ?? 'upcoming') === 'past' ? 'background:var(--nu-blue);color:#fff;font-weight:700;' : 'background:var(--gray-100);color:var(--gray-700);font-weight:600;' }}">
                <i class="bi bi-clock-history me-1"></i> Past / Completed Events
                <span class="badge {{ ($tab ?? 'upcoming') === 'past' ? 'bg-light text-dark' : 'bg-secondary text-white' }} ms-1">{{ $pastCount ?? 0 }}</span>
            </a>
        </li>
    </ul>

    <!-- Search/Filter -->
    <form method="GET" class="mb-4">
        <input type="hidden" name="tab" value="{{ $tab ?? 'upcoming' }}">
        <div class="row g-2">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search {{ ($tab ?? 'upcoming') === 'past' ? 'past' : 'upcoming' }} events…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-nu-blue w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('student.events', ['tab' => $tab ?? 'upcoming']) }}" class="btn btn-outline-secondary w-100" title="Clear"><i class="bi bi-x"></i></a>
            </div>
        </div>
    </form>

    @if(isset($recommended) && count($recommended) > 0)
    <div class="mb-4">
        <div class="nu-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-700 mb-0" style="color:var(--nu-blue)">
                        <i class="bi bi-stars me-2" style="color:var(--nu-gold)"></i>Recommended For You
                    </h6>
                    <p class="text-muted small mb-0" style="font-size:.75rem">Based on your course and interests</p>
                </div>
            </div>
            <div class="row g-3">
                @foreach($recommended->take(4) as $ev)
                <div class="col-md-6 col-lg-3">
                    <div class="h-100 p-3 rounded-3 d-flex flex-column justify-content-between" style="background:var(--gray-50);border:1px solid var(--gray-200)">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge" style="background:rgba(0,48,135,.1);color:var(--nu-blue);font-size:.65rem">{{ $ev->category ?? 'General' }}</span>
                                <span class="text-muted" style="font-size:.7rem"><i class="bi bi-calendar-event me-1"></i>{{ $ev->event_date?->format('M d') }}</span>
                            </div>
                            <h6 class="fw-700 mb-1 text-truncate" style="font-size:.88rem;color:var(--nu-blue)" title="{{ $ev->title }}">{{ $ev->title }}</h6>
                            <p class="text-muted mb-1 text-truncate" style="font-size:.75rem"><i class="bi bi-geo-alt me-1"></i>{{ $ev->venue }}</p>
                            @if(!empty($ev->recommendation_reason))
                            <div class="mb-2">
                                <span class="badge bg-light text-dark border" style="font-size:.62rem;font-weight:500;">{{ $ev->recommendation_reason }}</span>
                            </div>
                            @endif
                        </div>
                        @if(!$registeredIds->contains($ev->id))
                        <form action="{{ route('student.register', $ev->id) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-nu-blue btn-sm w-100 fw-600" style="font-size:.78rem">
                                <i class="bi bi-ticket-perforated me-1"></i>Register
                            </button>
                        </form>
                        @else
                        <span class="badge w-100 text-center py-2" style="background:rgba(22,163,74,.1);color:#16a34a;font-size:.75rem">Already Registered ✓</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Event Grid -->
    @if($events->count() > 0)
    <div class="row g-4 mb-4">
        @foreach($events as $ev)
        <div class="col-md-6 col-lg-4 fade-in-up" style="animation-delay:{{ $loop->index * 0.05 }}s">
            <div class="event-card h-100">
                @if($ev->poster_path)
                    <img src="{{ \App\Helpers\StorageUrl::url($ev->poster_path) }}" class="event-card-img" alt="{{ $ev->title }}">
                @else
                    <div class="event-card-img-placeholder">
                        <i class="bi bi-calendar-event text-white" style="font-size:3rem;opacity:.5"></i>
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
                    <h6 class="fw-700 mb-2" style="color:var(--nu-blue)">{{ $ev->title }}</h6>
                    <p class="text-muted small mb-2" style="flex-grow:1">{{ Str::limit($ev->description, 90) }}</p>
                    <div class="mt-auto">
                        <div class="small text-muted mb-1"><i class="bi bi-geo-alt me-1 text-nu-blue"></i>{{ $ev->venue }}</div>
                        <div class="small text-muted"><i class="bi bi-clock me-1 text-nu-blue"></i>{{ substr($ev->start_time,0,5) }} – {{ substr($ev->end_time,0,5) }}</div>
                    </div>
                </div>
                <div class="event-card-footer d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-700 small" style="color:var(--nu-blue)">{{ $ev->event_date->format('M d, Y') }}</div>
                        <div class="text-muted" style="font-size:.72rem">
                            <i class="bi bi-people me-1"></i>{{ $ev->registeredCount() }}/{{ $ev->capacity }} slots
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('event.show', $ev->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i></a>
                        @if(($tab ?? 'upcoming') === 'past')
                            @if($registeredIds->contains($ev->id))
                                <span class="badge bg-success-subtle text-success py-1.5 px-2.5 fw-600"><i class="bi bi-check-circle me-1"></i>Attended / Registered</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary py-1.5 px-2.5 fw-600"><i class="bi bi-clock-history me-1"></i>Completed</span>
                            @endif
                        @else
                            @if($registeredIds->contains($ev->id))
                                <button class="btn btn-sm" style="background:#dcfce7;color:#166534;border:none;font-weight:600" disabled><i class="bi bi-check-circle me-1"></i>Registered</button>
                            @elseif($ev->isFull())
                                <button class="btn btn-outline-danger btn-sm" disabled>Full</button>
                            @else
                                <form action="{{ route('student.register', $ev->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-gold btn-sm fw-600"><i class="bi bi-plus me-1"></i>Register</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $events->links() }}
    @else
    <div class="text-center py-5 nu-card">
        <i class="bi bi-calendar-x" style="font-size:4rem;color:var(--gray-200)"></i>
        <h5 class="text-muted mt-3">No events found</h5>
        <p class="text-muted small">Try adjusting your filters or check back later.</p>
        <a href="{{ route('student.events') }}" class="btn btn-nu-blue mt-2">Clear Filters</a>
    </div>
    @endif
</div>
@endsection
