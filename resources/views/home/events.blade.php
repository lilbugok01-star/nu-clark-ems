@extends('layouts.app')
@section('title', 'Browse Events')
@section('content')

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold" style="color:var(--nu-blue)"><i class="bi bi-calendar3 me-2"></i>Events</h2>
        </div>
    </div>

    <!-- Filters -->
    <form class="row g-2 mb-4">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Search events..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-nu-blue w-100">Filter</button>
            <a href="{{ route('events') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    @if($events->count() > 0)
    <div class="row g-4">
        @foreach($events as $event)
        <div class="col-md-6 col-lg-4">
            <div class="nu-card h-100">
                @if($event->poster_path)
                    <img src="{{ \App\Helpers\StorageUrl::url($event->poster_path) }}" class="event-card-img" alt="{{ $event->title }}">
                @else
                    <div class="event-card-img-placeholder"><i class="bi bi-calendar-event"></i></div>
                @endif
                <div class="p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge-category">{{ $event->category ?? 'General' }}</span>
                        @if($event->event_date < now()->toDateString())
                            <span class="badge bg-secondary" style="font-size:0.7rem">Past</span>
                        @endif
                    </div>
                    <h6 class="fw-bold">{{ $event->title }}</h6>
                    <p class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i>{{ $event->venue }}</p>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-calendar me-1"></i>{{ $event->event_date->format('M d, Y') }}
                        <i class="bi bi-clock ms-2 me-1"></i>{{ substr($event->start_time,0,5) }} – {{ substr($event->end_time,0,5) }}
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="bi bi-people me-1"></i>{{ $event->registeredCount() }}/{{ $event->capacity }}</small>
                        <a href="{{ route('event.show', $event->id) }}" class="btn btn-nu-blue btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $events->links('pagination::bootstrap-5') }}</div>
    @else
    <div class="text-center py-5">
        <div style="font-size:4rem">📅</div>
        <h5 class="text-muted mt-3">No events found.</h5>
        <a href="{{ route('events') }}" class="btn btn-nu-blue mt-2">Clear Filters</a>
    </div>
    @endif
</div>
@endsection
