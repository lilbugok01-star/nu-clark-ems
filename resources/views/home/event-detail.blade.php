@extends('layouts.app')
@section('title', $event->title)
@section('content')
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="nu-card overflow-hidden">
                @if($event->poster_path)
                    <img src="{{ \App\Helpers\StorageUrl::url($event->poster_path) }}" class="w-100" style="max-height:400px;object-fit:cover" alt="{{ $event->title }}">
                @else
                    <div class="event-card-img-placeholder" style="height:250px"><i class="bi bi-calendar-event"></i></div>
                @endif
                <div class="p-4">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge-category">{{ $event->category ?? 'General' }}</span>
                        @php $statusLabel = match($event->status) { 'pending_adviser','pending_dept_head','pending_dean','pending_director' => 'Under Review', 'published' => 'Published', 'draft' => 'Draft', 'cancelled' => 'Cancelled', 'completed' => 'Completed', 'rejected' => 'Rejected', default => ucfirst($event->status) }; @endphp
                        <span class="badge-status-{{ $event->status }}">{{ $statusLabel }}</span>
                        @if($event->is_featured) <span class="badge bg-warning text-dark" style="font-size:0.7rem"><i class="bi bi-star-fill"></i> Featured</span> @endif
                    </div>
                    <h1 class="fw-bold h3" style="color:var(--nu-blue)">{{ $event->title }}</h1>
                    <p class="text-muted">{{ $event->description }}</p>
                    <hr>
                    <div class="row g-3">
                        <div class="col-sm-6"><i class="bi bi-geo-alt text-gold me-1"></i> <strong>{{ $event->venue }}</strong></div>
                        <div class="col-sm-6"><i class="bi bi-calendar text-gold me-1"></i> {{ $event->event_date->format('F d, Y') }}</div>
                        <div class="col-sm-6"><i class="bi bi-clock text-gold me-1"></i> {{ substr($event->start_time,0,5) }} – {{ substr($event->end_time,0,5) }}</div>
                        <div class="col-sm-6"><i class="bi bi-person-circle text-gold me-1"></i> {{ $event->organizer->name }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Registration Card -->
            <div class="nu-card p-4 mb-3">
                <h6 class="fw-bold mb-3">Registration</h6>
                <!-- Capacity -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Registered</span>
                        <span>{{ $event->registered_count }} / {{ $event->capacity }}</span>
                    </div>
                    <div class="progress" style="height:8px; border-radius:10px;">
                        <div class="progress-bar" style="width:{{ $event->capacity > 0 ? ($event->registered_count / $event->capacity * 100) : 0 }}%; background:var(--nu-blue)"></div>
                    </div>
                </div>

                @auth
                    @if($isRegistered)
                        <div class="alert alert-success text-center small mb-2 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i> You're registered!
                        </div>
                        @php $reg = \App\Models\Registration::where('user_id', Auth::id())->where('event_id', $event->id)->first(); @endphp
                        @if($reg)
                        <a href="{{ route('student.qr', $reg->id) }}" class="btn btn-gold w-100 mb-2">
                            <i class="bi bi-qr-code me-1"></i> View My QR Code
                        </a>
                        @endif
                    @elseif($event->isFull())
                        <button class="btn btn-secondary w-100" disabled>Event Full</button>
                    @elseif($event->event_date < now()->toDateString())
                        <button class="btn btn-secondary w-100" disabled>Event Passed</button>
                    @else
                        <form action="{{ route('student.register', $event->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-nu-blue w-100">
                                <i class="bi bi-check-circle me-1"></i> Register for this Event
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-gold w-100">
                        <i class="bi bi-person me-1"></i> Login to Register
                    </a>
                @endauth
            </div>

            <!-- Organizer Card -->
            <div class="nu-card p-4">
                <h6 class="fw-bold mb-3">Organized by</h6>
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon" style="background:rgba(0,48,135,0.1)">
                        <i class="bi bi-person-circle" style="color:var(--nu-blue)"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">{{ $event->organizer->name }}</div>
                        <div class="text-muted" style="font-size:0.75rem">{{ $event->organizer->email }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
