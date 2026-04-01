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

    <!-- Search/Filter -->
    <form method="GET" class="mb-4">
        <div class="row g-2">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search events…" value="{{ request('search') }}">
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
                <a href="{{ route('student.events') }}" class="btn btn-outline-secondary w-100" title="Clear"><i class="bi bi-x"></i></a>
            </div>
        </div>
    </form>

    <!-- Event Grid -->
    @if($events->count() > 0)
    <div class="row g-4 mb-4">
        @foreach($events as $ev)
        <div class="col-md-6 col-lg-4 fade-in-up" style="animation-delay:{{ $loop->index * 0.05 }}s">
            <div class="event-card h-100">
                @if($ev->poster_path)
                    <img src="{{ asset('storage/'.$ev->poster_path) }}" class="event-card-img" alt="{{ $ev->title }}">
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
