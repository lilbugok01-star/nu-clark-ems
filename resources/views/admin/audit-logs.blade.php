@extends('layouts.app')
@section('title', 'System Audit Logs')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3">
            @include('layouts.partials.sidebar-admin')
        </div>

        <!-- Content -->
        <div class="col-lg-10 col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="fw-bold mb-0" style="color:var(--nu-blue)"><i class="bi bi-journal-text me-2"></i>System & Attendance Audit Trails</h4>
                <span class="badge bg-nu-blue px-3 py-2">System Logs</span>
            </div>

            <!-- Tab Navigation -->
            <div class="nu-card mb-4 p-2">
                <ul class="nav nav-pills nav-fill" id="auditTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-2 fw-bold" id="tab-sys" data-bs-toggle="pill" data-bs-target="#pane-sys" type="button" role="tab"><i class="bi bi-cpu me-2"></i>System Activities</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-2 fw-bold" id="tab-att" data-bs-toggle="pill" data-bs-target="#pane-att" type="button" role="tab"><i class="bi bi-qr-code-scan me-2"></i>QR Check-in Telemetry</button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="auditTabsContent">
                <!-- SYSTEM LOGS TAB -->
                <div class="tab-pane fade show active" id="pane-sys" role="tabpanel">
                    <div class="nu-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-gold">Filter System Activities</h6>
                        <form action="{{ route('admin.audit-logs') }}" method="GET" class="row g-3">
                            <input type="hidden" name="tab" value="sys">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Action</label>
                                <input type="text" name="action" class="form-control form-control-sm" placeholder="e.g. create_venue" value="{{ request('action') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">User</label>
                                <select name="user_id" class="form-select form-select-sm">
                                    <option value="">-- All Users --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" @if(request('user_id') == $u->id) selected @endif>{{ $u->full_name }} ({{ ucfirst($u->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Date From</label>
                                <input type="date" name="date_start" class="form-control form-control-sm" value="{{ request('date_start') }}">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label small fw-bold">Date To</label>
                                <input type="date" name="date_end" class="form-control form-control-sm" value="{{ request('date_end') }}">
                            </div>
                            <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                                <a href="{{ route('admin.audit-logs') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                                <button type="submit" class="btn btn-sm btn-nu-blue px-3"><i class="bi bi-filter"></i> Apply Filter</button>
                            </div>
                        </form>
                    </div>

                    <div class="nu-card p-4">
                        <div class="table-responsive">
                            <table class="table nu-table align-middle">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Model / Resource</th>
                                        <th>IP Address</th>
                                        <th>Timestamp</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($systemLogs as $log)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $log->user->full_name ?? 'System' }}</div>
                                                <div class="text-muted small" style="font-size:0.7rem;">{{ ucfirst($log->user->role ?? 'Daemon') }}</div>
                                            </td>
                                            <td>
                                                <code class="bg-light text-dark px-2 py-1 rounded small">{{ $log->action }}</code>
                                            </td>
                                            <td>
                                                @if($log->model_type)
                                                    <span class="small text-muted">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</span>
                                                @else
                                                    <span class="small text-muted">-</span>
                                                @endif
                                            </td>
                                            <td><span class="small">{{ $log->ip_address }}</span></td>
                                            <td><span class="small text-muted">{{ $log->created_at->format('M d, Y h:i A') }}</span></td>
                                            <td>
                                                @if($log->old_values || $log->new_values)
                                                    <button class="btn btn-sm btn-outline-primary py-1 px-2 rounded-pill" 
                                                            style="font-size:0.7rem;" 
                                                            onclick="viewLogDetails({{ $log->id }}, '{{ $log->action }}', {{ json_encode($log->old_values) }}, {{ json_encode($log->new_values) }})">
                                                        <i class="bi bi-eye"></i> Inspect
                                                    </button>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No system activities logged.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $systemLogs->links() }}
                        </div>
                    </div>
                </div>

                <!-- ATTENDANCE TELEMETRY LOGS TAB -->
                <div class="tab-pane fade" id="pane-att" role="tabpanel">
                    <div class="nu-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-gold">Filter Check-in Attempts</h6>
                        <form action="{{ route('admin.audit-logs') }}" method="GET" class="row g-3">
                            <input type="hidden" name="tab" value="att">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Check-in Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">-- All Outcomes --</option>
                                    <option value="success" @if(request('status') === 'success') selected @endif>Success</option>
                                    <option value="expired_token" @if(request('status') === 'expired_token') selected @endif>Expired Token (QR Rotation)</option>
                                    <option value="invalid_signature" @if(request('status') === 'invalid_signature') selected @endif>Invalid Signature (Tampered)</option>
                                    <option value="duplicate" @if(request('status') === 'duplicate') selected @endif>Duplicate Attempt</option>
                                    <option value="outside_event_window" @if(request('status') === 'outside_event_window') selected @endif>Outside Event Window</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Student</label>
                                <select name="att_user_id" class="form-select form-select-sm">
                                    <option value="">-- All Students --</option>
                                    @foreach($users->where('role', 'student') as $stu)
                                        <option value="{{ $stu->id }}" @if(request('att_user_id') == $stu->id) selected @endif>{{ $stu->full_name }} ({{ $stu->student_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                                <a href="{{ route('admin.audit-logs') }}?tab=att" class="btn btn-sm btn-outline-secondary">Reset</a>
                                <button type="submit" class="btn btn-sm btn-nu-blue px-3"><i class="bi bi-filter"></i> Apply Filter</button>
                            </div>
                        </form>
                    </div>

                    <div class="nu-card p-4">
                        <div class="table-responsive">
                            <table class="table nu-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Event</th>
                                        <th>Method</th>
                                        <th>Outcome Status</th>
                                        <th>IP Address</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendanceLogs as $attLog)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $attLog->user->full_name ?? 'Unknown' }}</div>
                                                <div class="text-muted small" style="font-size:0.7rem;">ID: {{ $attLog->user->student_id ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold small text-truncate" style="max-width:180px;">{{ $attLog->event->title ?? 'Untitled' }}</div>
                                                <div class="text-muted small" style="font-size:0.7rem;">{{ $attLog->event?->event_date?->format('M d, Y') ?? '' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size:0.65rem;">
                                                    {{ $attLog->action === 'selfie_checkin' ? 'Camera Selfie' : 'QR Scan' }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $badgeBg = match($attLog->status) {
                                                        'success' => 'bg-success-subtle text-success border-success',
                                                        'expired_token' => 'bg-warning-subtle text-warning border-warning',
                                                        'invalid_signature', 'invalid_token' => 'bg-danger-subtle text-danger border-danger',
                                                        'duplicate' => 'bg-info-subtle text-info border-info',
                                                        default => 'bg-secondary-subtle text-secondary border-secondary'
                                                    };
                                                    $statusLabel = match($attLog->status) {
                                                        'success' => 'Success Check-in',
                                                        'expired_token' => 'Expired QR Token',
                                                        'invalid_signature' => 'Tampered Signature',
                                                        'duplicate' => 'Duplicate Attempt',
                                                        'outside_event_window' => 'Outside Window',
                                                        default => ucfirst($attLog->status)
                                                    };
                                                @endphp
                                                <span class="badge border px-2.5 py-1.5 {{ $badgeBg }}" style="font-size: 0.7rem;">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>
                                            <td><span class="small">{{ $attLog->ip_address }}</span></td>
                                            <td><span class="small text-muted">{{ $attLog->created_at->format('M d, Y h:i A') }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No attendance scans logged.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $attendanceLogs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inspect System Log Modal -->
<div class="modal fade" id="inspectLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-3">
            <div class="modal-header" style="background:var(--nu-blue);border:none">
                <h5 class="modal-title text-white fw-700"><i class="bi bi-search me-2" style="color:var(--nu-gold)"></i>Inspect Log Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <span class="fw-bold text-muted small text-uppercase d-block mb-1">Action Code</span>
                    <h5 id="inspect-action-code" class="fw-bold" style="color:var(--nu-blue)"></h5>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <span class="fw-bold text-muted small text-uppercase d-block mb-2">Previous State (Old Values)</span>
                        <pre id="inspect-old-values" class="bg-dark text-white p-3 rounded" style="font-size:0.75rem; max-height:300px; overflow:auto;"></pre>
                    </div>
                    <div class="col-md-6 mb-3">
                        <span class="fw-bold text-muted small text-uppercase d-block mb-2">New State (New Values)</span>
                        <pre id="inspect-new-values" class="bg-dark text-white p-3 rounded" style="font-size:0.75rem; max-height:300px; overflow:auto;"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-nu-blue" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Automatically switch tabs based on query param
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        if (activeTab === 'att') {
            const trigger = document.getElementById('tab-att');
            if (trigger) {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            }
        }
    });

    function viewLogDetails(id, action, oldVals, newVals) {
        document.getElementById('inspect-action-code').innerText = action;
        
        const formatJSON = (val) => {
            if (!val || Object.keys(val).length === 0) return 'No data recorded.';
            return JSON.stringify(val, null, 4);
        };

        document.getElementById('inspect-old-values').innerText = formatJSON(oldVals);
        document.getElementById('inspect-new-values').innerText = formatJSON(newVals);
        
        const modal = new bootstrap.Modal(document.getElementById('inspectLogModal'));
        modal.show();
    }
</script>
@endpush
@endsection
