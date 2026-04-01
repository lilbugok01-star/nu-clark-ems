@extends('layouts.app')

@section('title', 'All Attendees — Organizer')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:48px;height:48px;background:var(--nu-blue);color:var(--nu-gold)">
            <i class="bi bi-person-check fs-4"></i>
        </div>
        <div>
            <h1 class="h4 fw-800 mb-0" style="color:var(--nu-blue)">All Attendees</h1>
            <p class="text-muted small mb-0">Registrations across all your events</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('organizer.attendees') }}" class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-600 mb-1" style="color:var(--nu-blue)">Filter by Event</label>
                    <select name="event_id" class="form-select form-select-sm">
                        <option value="">— All Events —</option>
                        @foreach($myEvents as $ev)
                            <option value="{{ $ev->id }}" {{ request('event_id') == $ev->id ? 'selected' : '' }}>
                                {{ $ev->title }} ({{ $ev->event_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-600 mb-1" style="color:var(--nu-blue)">Attendance Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">— All —</option>
                        <option value="attended" {{ request('status') === 'attended' ? 'selected' : '' }}>Attended (Verified)</option>
                        <option value="not_attended" {{ request('status') === 'not_attended' ? 'selected' : '' }}>Not Yet Attended</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm fw-600 px-3" style="background:var(--nu-blue);color:#fff">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('organizer.attendees') }}" class="btn btn-sm btn-outline-secondary fw-600 px-3">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Summary Badge --}}
    <div class="mb-3">
        <span class="badge rounded-pill fw-600 px-3 py-2"
              style="background:rgba(0,48,135,.1);color:var(--nu-blue);font-size:.8rem">
            <i class="bi bi-people me-1"></i>
            {{ $registrations->total() }} registration(s) found
        </span>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:var(--nu-blue);color:#fff">
                    <tr>
                        <th class="py-3 px-3" style="font-size:.78rem;font-weight:700;letter-spacing:.05em">#</th>
                        <th class="py-3 px-3" style="font-size:.78rem;font-weight:700;letter-spacing:.05em">Student</th>
                        <th class="py-3 px-3" style="font-size:.78rem;font-weight:700;letter-spacing:.05em">Course / Section</th>
                        <th class="py-3 px-3" style="font-size:.78rem;font-weight:700;letter-spacing:.05em">Event</th>
                        <th class="py-3 px-3" style="font-size:.78rem;font-weight:700;letter-spacing:.05em">Registered</th>
                        <th class="py-3 px-3" style="font-size:.78rem;font-weight:700;letter-spacing:.05em">Attendance</th>
                        <th class="py-3 px-3" style="font-size:.78rem;font-weight:700;letter-spacing:.05em">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $index => $reg)
                        @php
                            $att = $reg->attendance;
                            $attStatus = $att ? $att->status : null;
                        @endphp
                        <tr>
                            <td class="px-3 text-muted small">{{ $registrations->firstItem() + $index }}</td>
                            <td class="px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-800 flex-shrink-0"
                                         style="width:32px;height:32px;background:rgba(0,48,135,.1);color:var(--nu-blue);font-size:.75rem">
                                        {{ strtoupper(substr($reg->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-600 small" style="color:var(--nu-blue)">{{ $reg->user->name ?? 'N/A' }}</div>
                                        <div class="text-muted" style="font-size:.72rem">{{ $reg->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 small text-muted">
                                {{ $reg->user->course->name ?? '—' }}
                                @if($reg->user->section ?? null)
                                    <span class="text-secondary">&bull; {{ $reg->user->section->name }}</span>
                                @endif
                            </td>
                            <td class="px-3">
                                <div class="fw-600 small" style="color:var(--nu-blue)">{{ $reg->event->title ?? 'N/A' }}</div>
                                <div class="text-muted" style="font-size:.72rem">
                                    {{ $reg->event->event_date ? $reg->event->event_date->format('M d, Y') : '' }}
                                </div>
                            </td>
                            <td class="px-3 small text-muted">{{ $reg->created_at->format('M d, Y') }}</td>
                            <td class="px-3">
                                @if($attStatus === 'verified')
                                    <span class="badge rounded-pill px-2 py-1" style="background:#dcfce7;color:#166534;font-size:.7rem">
                                        <i class="bi bi-check-circle-fill me-1"></i>Verified
                                    </span>
                                @elseif($attStatus === 'pending')
                                    <span class="badge rounded-pill px-2 py-1" style="background:#fef9c3;color:#854d0e;font-size:.7rem">
                                        <i class="bi bi-clock me-1"></i>Pending
                                    </span>
                                @elseif($attStatus === 'rejected')
                                    <span class="badge rounded-pill px-2 py-1" style="background:#fee2e2;color:#991b1b;font-size:.7rem">
                                        <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-1" style="background:#f1f5f9;color:#64748b;font-size:.7rem">
                                        <i class="bi bi-dash-circle me-1"></i>No Record
                                    </span>
                                @endif
                            </td>
                            <td class="px-3">
                                @if($reg->event_id)
                                    <a href="{{ route('organizer.event.attendees', $reg->event_id) }}"
                                       class="btn btn-sm px-2 py-1"
                                       style="background:rgba(0,48,135,.1);color:var(--nu-blue);font-size:.75rem"
                                       title="View event attendees">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-person-x fs-1 d-block mb-2" style="color:var(--nu-blue);opacity:.3"></i>
                                No registrations found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
            <div class="card-footer d-flex justify-content-center py-3 border-0">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
