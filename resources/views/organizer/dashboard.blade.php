@extends('layouts.app')
@section('title', 'Organizer Dashboard')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3">
            <div class="dashboard-sidebar rounded-xl mb-4">
                <div class="text-white-50 small text-uppercase fw-semibold mb-3 ps-2" style="letter-spacing:1px">Organizer</div>
                <a href="{{ route('organizer.dashboard') }}" class="sidebar-link @if(request()->routeIs('organizer.dashboard')) active @endif"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="{{ route('organizer.events') }}" class="sidebar-link @if(request()->routeIs('organizer.events')) active @endif"><i class="bi bi-calendar3"></i> My Events</a>
                <a href="{{ route('organizer.event.create') }}" class="sidebar-link @if(request()->routeIs('organizer.event.create')) active @endif"><i class="bi bi-plus-circle"></i> New Event</a>
                <a href="{{ route('organizer.analytics') }}" class="sidebar-link @if(request()->routeIs('organizer.analytics')) active @endif"><i class="bi bi-bar-chart"></i> Analytics</a>
                <hr style="border-color:rgba(255,255,255,0.1)">
                <button type="button" class="sidebar-link w-100 border-0" style="background:none;text-align:left" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </div>
        </div>

        <div class="col-lg-10 col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0" style="color:var(--nu-blue)">Organizer Dashboard</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-nu-blue fw-bold" data-bs-toggle="modal" data-bs-target="#webScannerModal">
                        <i class="bi bi-qr-code-scan me-1"></i>Web Scanner
                    </button>
                    <a href="{{ route('organizer.event.create') }}" class="btn btn-gold">
                        <i class="bi bi-plus me-1"></i>Create
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="stat-card"><div class="stat-value">{{ $stats['total_events'] }}</div><div class="stat-label">My Events</div></div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card" style="border-color:var(--nu-gold)"><div class="stat-value text-gold">{{ $stats['upcoming_events'] }}</div><div class="stat-label">Upcoming</div></div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card" style="border-color:#28a745"><div class="stat-value" style="color:#28a745">{{ $stats['total_regs'] }}</div><div class="stat-label">Total Registrations</div></div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card" style="border-color:#6f42c1"><div class="stat-value" style="color:#6f42c1">{{ $stats['verified_att'] }}</div><div class="stat-label">Verified Attendances</div></div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="nu-card p-4">
                        <h6 class="fw-bold mb-3">My Recent Events</h6>
                        @forelse($myEvents as $e)
                        <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-xl event-list-card" style="background:var(--white); border: 1px solid var(--gray-200); box-shadow: var(--shadow-sm); transition: all 0.3s ease;">
                            <div class="text-center bg-light rounded p-2" style="min-width:55px">
                                <div class="fw-bold lh-1" style="color:var(--nu-blue);font-size:1.4rem">{{ $e->event_date->format('d') }}</div>
                                <div class="text-muted small fw-600 mt-1" style="font-size:0.75rem; text-transform:uppercase;">{{ $e->event_date->format('M') }}</div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="color:var(--nu-blue);">{{ $e->title }}</div>
                                <div class="text-muted" style="font-size:0.75rem"><i class="bi bi-people me-1"></i>{{ $e->reg_count }} registered</div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('organizer.event.attendees', $e->id) }}" class="btn btn-outline-gold btn-sm" title="Attendees"><i class="bi bi-people"></i></a>
                                <a href="{{ route('organizer.event.edit', $e->id) }}" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted small text-center mt-3">No events yet. <a href="{{ route('organizer.event.create') }}">Create one →</a></p>
                        @endforelse
                    </div>

                    @push('styles')
                        <link href="{{ asset('css/organizer/dashboard.css') }}" rel="stylesheet">
                    @endpush
                </div>

                <!-- Pending Verifications -->
                <div class="col-lg-5">
                    <div class="nu-card p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-camera text-gold me-2"></i>Pending Verifications</h6>
                        @forelse($pendingVerifications as $att)
                        <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" style="background:var(--gray-100)">
                            @if($att->photo_path)
                                <a href="#" data-bs-toggle="modal" data-bs-target="#photoModal{{ $att->id }}">
                                    <img src="{{ asset('storage/' . $att->photo_path) }}" class="attendance-photo" style="cursor:pointer;object-fit:cover;width:42px;height:42px;border-radius:50%;border:2px solid var(--nu-blue)" alt="photo">
                                </a>

                                <!-- Photo Modal -->
                                <div class="modal fade" id="photoModal{{ $att->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-3 overflow-hidden">
                                            <div class="modal-header border-0 pb-0">
                                                <strong class="small" style="color:var(--nu-blue)">{{ $att->registration->user->name }} — Pending Check-in</strong>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-2">
                                                <img src="{{ asset('storage/' . $att->photo_path) }}" class="w-100 rounded-2" style="object-fit:cover;max-height:400px" alt="Attendance Photo">
                                            </div>
                                            <div class="modal-footer d-flex gap-2 border-0 pt-0">
                                                <form action="{{ route('organizer.attendance.verify', $att->id) }}" method="POST" class="d-flex gap-2 w-100" style="margin:0">
                                                    @csrf @method('PUT')
                                                    <button name="status" value="verified" class="btn btn-success flex-grow-1 fw-700" onclick="document.querySelector('#photoModal{{ $att->id }} .btn-close').click()"><i class="bi bi-check-lg me-1"></i>Verify</button>
                                                    <button name="status" value="rejected" class="btn btn-danger flex-grow-1 fw-700" onclick="document.querySelector('#photoModal{{ $att->id }} .btn-close').click()"><i class="bi bi-x-lg me-1"></i>Reject</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="attendance-photo d-flex align-items-center justify-content-center" style="background:#eee;width:42px;height:42px;border-radius:50%"><i class="bi bi-person text-muted"></i></div>
                            @endif
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">{{ $att->registration->user->name }}</div>
                                <div class="text-muted" style="font-size:0.72rem">{{ $att->registration->event->title }}</div>
                            </div>
                            <form action="{{ route('organizer.attendance.verify', $att->id) }}" method="POST" class="d-flex gap-1" style="z-index:10;">
                                @csrf @method('PUT')
                                <button name="status" value="verified" class="btn btn-success btn-sm py-0 px-2" title="Verify"><i class="bi bi-check"></i></button>
                                <button name="status" value="rejected" class="btn btn-danger btn-sm py-0 px-2" title="Reject"><i class="bi bi-x"></i></button>
                            </form>
                        </div>
                        @empty
                        <p class="text-muted small text-center mt-3">No pending verifications. 🎉</p>
                        @endforelse
                        <a href="{{ route('organizer.events') }}" class="btn btn-outline-gold btn-sm w-100 mt-2">View All Events</a>
                    </div>
                </div>
            </div>

            <!-- Events Calendar -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="nu-card p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-calendar3 text-gold me-2"></i>Events Calendar</h6>
                        <x-event-calendar calendarId="organizerCalendar" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Web Scanner Modal -->
<div class="modal fade" id="webScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <strong class="small" style="color:var(--nu-blue)"><i class="bi bi-camera me-2"></i>Webcam QR Scanner</strong>
                <button class="btn-close" data-bs-dismiss="modal" id="closeScannerModal"></button>
            </div>
            <div class="modal-body text-center p-3">
                <div id="reader" class="rounded-3 overflow-hidden" style="width: 100%; border:2px dashed var(--nu-blue)"></div>
                <p class="text-muted small mt-3 mb-0">Point the student's QR code at your laptop webcam to check them in seamlessly.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrcodeScanner;
    const scannerModal = document.getElementById('webScannerModal');

    scannerModal.addEventListener('shown.bs.modal', function () {
        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: {width: 250, height: 250} }, false);
        
        html5QrcodeScanner.render(function(decodedText) {
            // Once scanned, redirect securely to the decoded link
            html5QrcodeScanner.clear();
            const modalInstance = bootstrap.Modal.getInstance(scannerModal);
            if(modalInstance) modalInstance.hide();
            
            // Navigate to the scanned URL
            window.location.href = decodedText;
        }, function(error) {
            // Background scan noise (ignore)
        });
    });

    scannerModal.addEventListener('hidden.bs.modal', function () {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }
    });
</script>
@endpush

@endsection
