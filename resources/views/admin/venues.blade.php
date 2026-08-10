@extends('layouts.app')
@section('title', 'Venue Reservation Management')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 col-md-3">
            @include('layouts.partials.sidebar-admin')
        </div>
        <div class="col-lg-10 col-md-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h4 class="fw-800 mb-0" style="color:var(--nu-blue)">
                        <i class="bi bi-building me-2" style="color:var(--nu-gold)"></i>Venue Reservation Management
                    </h4>
                    <p class="text-muted small mb-0">Review, approve, override, or reject venue reservation requests</p>
                </div>
                <div>
                    <span class="badge py-2 px-3 rounded-pill fw-700" style="background:rgba(255,193,7,.18);color:#856404;font-size:.85rem">
                        <i class="bi bi-hourglass-split me-1"></i>{{ $pending }} Pending Review
                    </span>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-white rounded-3 shadow-sm border text-center">
                        <div class="h3 fw-800 mb-0" style="color:var(--nu-blue)">{{ $total }}</div>
                        <div class="text-muted small fw-600">Total Requests</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-white rounded-3 shadow-sm border text-center" style="border-color:#fef3c7 !important">
                        <div class="h3 fw-800 mb-0" style="color:#b45309">{{ $pending }}</div>
                        <div class="text-muted small fw-600">Pending Approvals</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-white rounded-3 shadow-sm border text-center" style="border-color:#bbf7d0 !important">
                        <div class="h3 fw-800 mb-0" style="color:#15803d">{{ $approved }}</div>
                        <div class="text-muted small fw-600">Approved</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-white rounded-3 shadow-sm border text-center" style="border-color:#fecdd3 !important">
                        <div class="h3 fw-800 mb-0" style="color:#be123c">{{ $rejected }}</div>
                        <div class="text-muted small fw-600">Rejected</div>
                    </div>
                </div>
            </div>

            <!-- Status Filter Tabs -->
            <div class="mb-4">
                <div class="d-flex gap-2 flex-wrap">
                    @foreach(['all' => 'All Requests', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $lbl)
                        <a href="{{ route('admin.venues', ['status' => $val]) }}"
                           class="btn btn-sm rounded-pill px-3 fw-600 {{ request('status', 'all') === $val ? 'btn-nu-blue shadow-sm' : 'btn-outline-secondary' }}">
                            {{ $lbl }}
                            @if($val === 'pending' && $pending > 0)
                                <span class="badge rounded-pill bg-warning text-dark ms-1" style="font-size:0.7rem">{{ $pending }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Reservations Table Card -->
            <div class="nu-card bg-white rounded-3 shadow-sm border overflow-hidden">
                <div class="table-responsive">
                    <table class="table nu-table align-middle mb-0">
                        <thead style="background:#f8fafc">
                            <tr class="text-uppercase text-secondary small fw-700" style="font-size:0.75rem;letter-spacing:0.03em">
                                <th class="py-3 ps-3">Requested By</th>
                                <th class="py-3">Event Details</th>
                                <th class="py-3">Reserved Venue / Rooms</th>
                                <th class="py-3">Date & Time</th>
                                <th class="py-3 text-center">Attendees</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $res)
                                @php
                                    $isPending = str_starts_with($res->status, 'pending_');
                                    
                                    // Parse multi-rooms array
                                    $roomList = $res->rooms->pluck('room_name')->toArray();
                                    if (empty($roomList) && !empty($res->venue_name)) {
                                        $roomList = [$res->venue_name];
                                    }

                                    // Role label for pending state
                                    $pendingRole = str_replace('pending_', '', $res->status);
                                    $roleLabel = match($pendingRole) {
                                        'student_development', 'student_dev' => 'Student Dev',
                                        'program_chair' => 'Program Chair',
                                        'department_head', 'dept_head' => 'Dept Head',
                                        'dean' => 'Dean',
                                        'executive_director', 'director' => 'Exec Director',
                                        'adviser' => 'Adviser',
                                        default => ucfirst($pendingRole),
                                    };

                                    $firstRoom = reset($roomList);
                                    $lastRoom = end($roomList);
                                    $allRoomsStr = implode(', ', $roomList);
                                @endphp
                                <tr>
                                    <!-- Requested By -->
                                    <td class="ps-3 py-3">
                                        <div class="fw-700 small text-dark">{{ $res->reservedBy?->full_name ?? 'Unknown User' }}</div>
                                        <div class="text-muted" style="font-size:.75rem">{{ $res->reservedBy?->email ?? '-' }}</div>
                                    </td>

                                    <!-- Event Title -->
                                    <td class="py-3">
                                        <div class="fw-600 small text-dark">{{ $res->event?->title ?? $res->event_title ?? 'Untitled Event' }}</div>
                                        @if($res->purpose)
                                            <div class="text-muted text-truncate" style="font-size:.72rem;max-width:180px" title="{{ $res->purpose }}">
                                                {{ $res->purpose }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Venue / Multi-Room Display (Clean Compact Formatting) -->
                                    <td class="py-3">
                                        @if(count($roomList) > 3)
                                            <!-- Multiple Rooms Summary Pill -->
                                            <span class="badge rounded-pill px-3 py-1.5 fw-700 shadow-sm"
                                                  style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:0.78rem"
                                                  title="Rooms: {{ $allRoomsStr }}">
                                                <i class="bi bi-building me-1"></i>{{ count($roomList) }} Rooms ({{ $firstRoom }} – {{ $lastRoom }})
                                            </span>
                                        @elseif(count($roomList) > 0)
                                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                                @foreach($roomList as $r)
                                                    <span class="badge rounded-2 px-2 py-1 font-monospace" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:0.75rem">
                                                        <i class="bi bi-door-closed text-secondary me-1"></i>{{ $r }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    <!-- Date & Time -->
                                    <td class="py-3 small">
                                        <div class="fw-700 text-dark"><i class="bi bi-calendar-event me-1 text-primary"></i>{{ $res->reserved_date->format('M d, Y') }}</div>
                                        <div class="text-muted fw-500" style="font-size:0.75rem">
                                            <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($res->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($res->end_time)->format('g:i A') }}
                                        </div>
                                    </td>

                                    <!-- Attendees -->
                                    <td class="py-3 text-center font-monospace fw-700 small">{{ $res->expected_attendees ?? '-' }}</td>

                                    <!-- Status -->
                                    <td class="py-3">
                                        @if($isPending)
                                            <span class="badge rounded-pill px-3 py-1.5 fw-700" style="background:#fffbeb;color:#b45309;border:1px solid #fef3c7;font-size:0.75rem">
                                                <i class="bi bi-clock-history me-1"></i>Pending ({{ $roleLabel }})
                                            </span>
                                        @elseif($res->status === 'approved')
                                            @if($res->override_by)
                                                <span class="badge rounded-pill px-3 py-1.5 fw-700" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;font-size:0.75rem" title="Approved via Admin Override">
                                                    <i class="bi bi-shield-check me-1"></i>Approved (Override)
                                                </span>
                                            @else
                                                <span class="badge rounded-pill px-3 py-1.5 fw-700" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;font-size:0.75rem">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Approved
                                                </span>
                                            @endif
                                        @elseif($res->status === 'rejected')
                                            <span class="badge rounded-pill px-3 py-1.5 fw-700" style="background:#fff1f2;color:#be123c;border:1px solid #fecdd3;font-size:0.75rem">
                                                <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                            </span>
                                        @elseif($res->status === 'cancelled')
                                            <span class="badge rounded-pill px-3 py-1.5 fw-700" style="background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;font-size:0.75rem">
                                                <i class="bi bi-slash-circle me-1"></i>Cancelled
                                            </span>
                                        @else
                                            <span class="badge rounded-pill px-2.5 py-1 bg-light text-dark border" style="font-size:0.75rem">{{ ucfirst($res->status) }}</span>
                                        @endif
                                    </td>

                                    <!-- Actions Dropdown -->
                                    <td class="py-3 text-end pe-3">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-3 fw-600" type="button" data-bs-toggle="dropdown" style="font-size:0.8rem">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-1" style="min-width:180px">
                                                <!-- View Permission Form Document -->
                                                <li>
                                                    <a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2" href="{{ route('approver.venues.form', $res->id) }}" target="_blank">
                                                        <i class="bi bi-file-earmark-text text-primary"></i> View Form Document
                                                    </a>
                                                </li>

                                                @if($isPending)
                                                    <li><hr class="dropdown-divider my-1"></li>
                                                    <!-- Quick Approve -->
                                                    <li>
                                                        <form action="{{ route('admin.venues.status', $res->id) }}" method="POST" onsubmit="return confirm('Quick approve this venue reservation?')">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="status" value="approved">
                                                            <button type="submit" class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2 text-success fw-600">
                                                                <i class="bi bi-check-circle-fill text-success"></i> Quick Approve
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <!-- Admin Force Override -->
                                                    <li>
                                                        <button type="button" class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2 text-dark fw-600" data-bs-toggle="modal" data-bs-target="#overrideModal{{ $res->id }}">
                                                            <i class="bi bi-shield-lightning-fill text-warning"></i> Force Override
                                                        </button>
                                                    </li>
                                                    <!-- Reject Request -->
                                                    <li>
                                                        <button type="button" class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2 text-danger fw-600" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $res->id }}">
                                                            <i class="bi bi-x-circle-fill text-danger"></i> Reject Request
                                                        </button>
                                                    </li>
                                                @endif

                                                <li><hr class="dropdown-divider my-1"></li>
                                                <!-- Delete Record -->
                                                <li>
                                                    <form action="{{ route('admin.venues.delete', $res->id) }}" method="POST" onsubmit="return confirm('Delete this venue reservation record?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2 text-danger">
                                                            <i class="bi bi-trash text-danger"></i> Delete Record
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $res->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-3 border-0 shadow">
                                            <div class="modal-header bg-danger text-white border-0">
                                                <h6 class="modal-title fw-800 mb-0"><i class="bi bi-x-circle me-2"></i>Reject Venue Reservation</h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.venues.status', $res->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="status" value="rejected">
                                                <div class="modal-body p-4">
                                                    <p class="small text-muted mb-3">Rejecting reservation for <strong>{{ $res->event?->title ?? $res->event_title }}</strong> on {{ $res->reserved_date->format('M d, Y') }}.</p>
                                                    <label class="form-label fw-700 small">Reason for Rejection <span class="text-danger">*</span></label>
                                                    <textarea name="notes" class="form-control" rows="3" required placeholder="Provide feedback explaining why this reservation was rejected..."></textarea>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 fw-700">Submit Rejection</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Admin Override Modal -->
                                @if($isPending)
                                <div class="modal fade" id="overrideModal{{ $res->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-3 border-0 shadow">
                                            <div class="modal-header bg-warning text-dark border-0">
                                                <h6 class="modal-title fw-800 mb-0"><i class="bi bi-shield-lightning me-2"></i>Admin Force Override Approval</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.venues.override', $res->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <div class="alert alert-warning small py-2 px-3 mb-3">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                        This action will bypass all remaining signatory steps and immediately mark the reservation as <strong>Approved</strong>.
                                                    </div>
                                                    <label class="form-label fw-700 small">Reason for Admin Override <span class="text-danger">*</span></label>
                                                    <textarea name="override_reason" class="form-control" rows="3" required minlength="5" placeholder="State official administrative reason for overriding the approval chain..."></textarea>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning btn-sm text-dark rounded-pill px-4 fw-800">Confirm Override Approval</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-building" style="font-size:2.5rem;opacity:.2"></i>
                                        <p class="small mt-2 mb-0">No venue reservations found for the selected filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">{{ $reservations->links() }}</div>
        </div>
    </div>
</div>
@endsection

