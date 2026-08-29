@extends('layouts.app')
@section('title', 'Manage Events — Admin')
@section('content')
<div class="container-fluid py-4 px-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3">
            @include('layouts.partials.sidebar-admin')
        </div>

        <!-- Main Content -->
        <div class="col-lg-10 col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h4 class="fw-800 mb-0" style="color:var(--nu-blue)">
                        <i class="bi bi-calendar-event me-2" style="color:var(--nu-gold)"></i>Event Management
                    </h4>
                    <p class="text-muted small mb-0">Oversee all campus events, change statuses, and remove cancelled organizer events</p>
                </div>
                <a href="{{ route('admin.reports') }}" class="btn btn-outline-gold btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Event Reports</a>
            </div>

            <!-- Tabs: Upcoming vs Past/Cancelled Events -->
            <ul class="nav nav-pills mb-4 gap-2">
                <li class="nav-item">
                    <a class="nav-link {{ ($tab ?? 'upcoming') === 'upcoming' ? 'active' : '' }}"
                       href="{{ route('admin.events', array_merge(request()->except('page'), ['tab' => 'upcoming'])) }}"
                       style="{{ ($tab ?? 'upcoming') === 'upcoming' ? 'background:var(--nu-blue);color:#fff;font-weight:700;' : 'background:var(--gray-100);color:var(--gray-700);font-weight:600;' }}">
                        <i class="bi bi-calendar-check me-1"></i> Upcoming Events
                        <span class="badge {{ ($tab ?? 'upcoming') === 'upcoming' ? 'bg-light text-dark' : 'bg-secondary text-white' }} ms-1">{{ $upcomingCount ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($tab ?? 'upcoming') === 'past' ? 'active' : '' }}"
                       href="{{ route('admin.events', array_merge(request()->except('page'), ['tab' => 'past'])) }}"
                       style="{{ ($tab ?? 'upcoming') === 'past' ? 'background:var(--nu-blue);color:#fff;font-weight:700;' : 'background:var(--gray-100);color:var(--gray-700);font-weight:600;' }}">
                        <i class="bi bi-clock-history me-1"></i> Past & Cancelled Events
                        <span class="badge {{ ($tab ?? 'upcoming') === 'past' ? 'bg-light text-dark' : 'bg-secondary text-white' }} ms-1">{{ $pastCount ?? 0 }}</span>
                    </a>
                </li>
            </ul>

            <!-- Filter Card -->
            <div class="nu-card p-3 mb-4">
                <form method="GET" action="{{ route('admin.events') }}">
                    <input type="hidden" name="tab" value="{{ $tab ?? 'upcoming' }}">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search title, venue, organizer…" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="organizer_id" class="form-select form-select-sm">
                                <option value="">All Organizers</option>
                                @foreach($organizers as $org)
                                    <option value="{{ $org->id }}" {{ request('organizer_id') == $org->id ? 'selected' : '' }}>{{ $org->full_name }} ({{ ucfirst($org->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-nu-blue btn-sm flex-fill" title="Apply Filter"><i class="bi bi-funnel"></i></button>
                            <a href="{{ route('admin.events', ['tab' => $tab ?? 'upcoming']) }}" class="btn btn-outline-secondary btn-sm flex-fill" title="Clear"><i class="bi bi-x"></i></a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Events Table -->
            <div class="nu-card">
                <div class="table-responsive">
                    <table class="table nu-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Organizer</th>
                                <th>Date & Time</th>
                                <th>Venue</th>
                                <th>Registrations</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $e)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($e->poster_path)
                                            <img src="{{ \App\Helpers\StorageUrl::url($e->poster_path) }}" class="rounded-2" style="width:42px;height:42px;object-fit:cover" alt="Poster">
                                        @else
                                            <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:rgba(0,48,135,0.08);color:var(--nu-blue)">
                                                <i class="bi bi-calendar-event"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-700 small" style="color:var(--nu-blue)">{{ $e->title }}</div>
                                            <div class="d-flex flex-wrap gap-1 mt-0.5">
                                                @if($e->category)<span class="badge-category">{{ $e->category }}</span>@endif
                                                @if($e->is_featured)<span class="badge bg-warning text-dark" style="font-size:0.6rem">★ Featured</span>@endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-600">{{ $e->organizer?->full_name ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:0.72rem">{{ $e->organizer?->email ?? '' }}</div>
                                </td>
                                <td class="small">
                                    <div class="fw-600">{{ $e->event_date ? $e->event_date->format('M d, Y') : '—' }}</div>
                                    <div class="text-muted" style="font-size:0.75rem">{{ substr($e->start_time, 0, 5) }} – {{ substr($e->end_time, 0, 5) }}</div>
                                </td>
                                <td class="small">
                                    <div>{{ $e->venue }}</div>
                                    @if($e->venue_type)<span class="venue-badge mt-1">{{ $e->venue_type }}</span>@endif
                                </td>
                                <td>
                                    <div class="small fw-600">{{ $e->registrations_count }} / {{ $e->capacity }}</div>
                                    <div class="progress mt-1" style="height:4px;width:80px">
                                        @php $pct = $e->capacity > 0 ? min(100, round(($e->registrations_count / $e->capacity) * 100)) : 0; @endphp
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <div class="text-muted" style="font-size:0.68rem">{{ $e->verified_count ?? 0 }} verified</div>
                                </td>
                                <td>
                                    @php
                                        $statusBadgeClass = match($e->status) {
                                            'published' => 'badge-status-published',
                                            'draft'     => 'badge-status-draft',
                                            'completed' => 'badge bg-info-subtle text-info',
                                            'cancelled' => 'badge-status-cancelled',
                                            default     => 'badge-status-draft',
                                        };
                                    @endphp
                                    <span class="{{ $statusBadgeClass }}">{{ ucfirst($e->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('event.show', $e->id) }}" class="btn btn-outline-secondary btn-sm" title="View Public Page" target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <!-- Change Status Dropdown Button -->
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="Change Status">
                                                <i class="bi bi-toggles"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm small">
                                                <li><h6 class="dropdown-header">Set Status</h6></li>
                                                @foreach(['published', 'draft', 'completed', 'cancelled'] as $st)
                                                    @if($e->status !== $st)
                                                    <li>
                                                        <form action="{{ route('admin.events.status', $e->id) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="status" value="{{ $st }}">
                                                            <button type="submit" class="dropdown-item py-1.5">{{ ucfirst($st) }}</button>
                                                        </form>
                                                    </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                        <!-- Fast Delete / Removal Button -->
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteEventModal{{ $e->id }}" title="Fast Remove / Cancel Event">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade text-start" id="deleteEventModal{{ $e->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-3">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-700 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Remove Event</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.events.delete', $e->id) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <div class="modal-body py-3">
                                                        <p class="mb-2">Are you sure you want to remove <strong>"{{ $e->title }}"</strong> by <strong>{{ $e->organizer?->full_name ?? 'Organizer' }}</strong>?</p>
                                                        <div class="alert alert-warning small py-2 mb-3">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            This will immediately <strong>cancel all {{ $e->registrations_count }} student registrations</strong>, notify the registered students, and remove the event from student dashboards.
                                                        </div>
                                                        <label class="form-label small fw-600">Reason for Removal (Optional / Audit Log):</label>
                                                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="e.g. Organizer/Professor resigned, schedule conflict">
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger btn-sm fw-700"><i class="bi bi-trash me-1"></i>Confirm Removal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x" style="font-size:2.5rem"></i>
                                    <p class="mt-2 mb-0">No events found in this category.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($events->hasPages())
                <div class="p-3 border-top">
                    {{ $events->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection