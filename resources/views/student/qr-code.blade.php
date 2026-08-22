@extends('layouts.app')
@section('title', 'My QR Code — ' . $registration->event->title)
@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <!-- Header -->
                <div class="d-flex align-items-center gap-2 mb-4">
                    <a href="{{ route('student.my-events') }}" class="btn btn-outline-secondary btn-sm"><i
                            class="bi bi-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-800 mb-0" style="color:var(--nu-blue)">My QR Code & Check-in</h5>
                        <p class="text-muted small mb-0">{{ $registration->event->title }}</p>
                    </div>
                </div>

                <!-- QR Card -->
                <div class="qr-container mb-4">
                    <div class="nu-logo-wrap mx-auto mb-3" style="background:none;box-shadow:none">
                        <img src="{{ asset('assets/img/NU_shield.png') }}" alt="NU Logo">
                    </div>
                    <h5 class="fw-800 text-center mb-1" style="color:var(--nu-blue)">{{ $registration->event->title }}</h5>
                    <div class="text-center text-muted small mb-4">
                        <i class="bi bi-calendar me-1"></i>{{ $registration->event->event_date->format('F d, Y') }}
                        &nbsp;·&nbsp;
                        <i class="bi bi-clock me-1"></i>{{ substr($registration->event->start_time, 0, 5) }} –
                        {{ substr($registration->event->end_time, 0, 5) }}
                        <br>
                        <i class="bi bi-geo-alt me-1"></i>{{ $registration->event->venue }}
                        @if($registration->event->venue_type)
                            &nbsp;·&nbsp;<span class="venue-badge">{{ $registration->event->venue_type }}</span>
                        @endif
                    </div>

                    <div class="d-flex flex-column align-items-center justify-content-center mb-4 p-3 position-relative"
                        style="background:white;border-radius:12px;border:2px solid var(--gray-200)">
                        <div id="qr-holder" class="d-flex align-items-center justify-content-center">
                            {!! $qrCode !!}
                        </div>
                        
                        <div class="w-100 mt-3 px-3">
                            <div class="progress" style="height: 6px;">
                                <div id="qr-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; transition: width 1s linear; background-color: var(--nu-blue) !important;"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1 text-muted" style="font-size:0.75rem;">
                                <span>Security Rotation Active</span>
                                <span id="qr-timer-text">Rotates in 15s</span>
                            </div>
                        </div>
                    </div>

                    @if($registration->attendance && $registration->attendance->checked_out_at)
                        <div class="alert alert-success text-center rounded-3 p-3">
                            <i class="bi bi-patch-check-fill fs-4 text-success d-block mb-1"></i>
                            <strong>Attendance Completed!</strong>
                            <div class="small mt-1 text-muted">
                                <span class="text-success fw-semibold"><i class="bi bi-box-arrow-in-right me-1"></i>Time In: {{ $registration->attendance->checked_in_at?->format('h:i A') ?? 'Recorded' }}</span>
                                &nbsp;·&nbsp;
                                <span class="text-primary fw-semibold"><i class="bi bi-box-arrow-right me-1"></i>Time Out: {{ $registration->attendance->checked_out_at->format('h:i A') }}</span>
                            </div>
                            <div class="small mt-1">Verification Status: <strong class="text-capitalize">{{ $registration->attendance->status }}</strong></div>
                        </div>
                    @elseif($registration->attendance)
                        <div class="alert text-center rounded-3 p-3" style="background:rgba(22,163,74,0.08);border:1px solid rgba(22,163,74,0.25);color:#166534">
                            <i class="bi bi-check-circle-fill me-1 text-success"></i>
                            <strong>Time-In Recorded ({{ $registration->attendance->checked_in_at?->format('h:i A') ?? 'Done' }})</strong>
                            <div class="small text-muted mt-1">Ready for check-out. Scan your QR or submit a Time-Out selfie below when leaving the event.</div>
                        </div>
                    @else
                        <div class="alert text-center rounded-3"
                            style="background:rgba(0,48,135,.07);border:1px solid rgba(0,48,135,.2);color:var(--nu-blue)">
                            <i class="bi bi-shield-lock-fill me-1" style="color: var(--nu-gold)"></i>
                            Rotating QR code active. Screenshot sharing is disabled.
                        </div>
                    @endif

                    <p class="text-center text-muted small">
                        @if($registration->attendance && $registration->attendance->checked_out_at)
                            You have completed both Time In and Time Out for this event.
                        @elseif($registration->attendance)
                            Show this QR code for Time-Out scanning, or submit a Time-Out selfie below.
                        @else
                            Show this QR code at the event for check-in, or submit a Time-In selfie below.
                        @endif
                    </p>
                </div>

                <!-- Photo Attendance Forms -->
                @if(!$registration->isExpired())
                    @if(!$registration->attendance)
                        <!-- 1. TIME-IN Photo Form -->
                        <div class="nu-card p-4 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-700 mb-0"><i class="bi bi-box-arrow-in-right me-2 text-success"></i>Submit Time-In Selfie</h6>
                                <span class="badge bg-success-subtle text-success">Step 1: Check In</span>
                            </div>
                            <p class="text-muted small mb-4">Take a selfie or upload a photo as proof of attendance for <strong>Time In</strong>. The organizer will verify it.</p>

                            <form action="{{ route('student.checkin') }}" method="POST" enctype="multipart/form-data"
                                id="checkinForm">
                                @csrf
                                <input type="hidden" name="qr_token" value="{{ $registration->qr_token }}">

                                <!-- Camera capture option -->
                                <div class="mb-3">
                                    <label class="form-label fw-600">Take Selfie (Camera)</label>
                                    <div id="cameraArea" class="text-center p-3 rounded-3"
                                        style="background:var(--gray-50);border:2px dashed var(--gray-200)">
                                        <video id="cameraPreview" class="d-none w-100 rounded-2" style="max-height:250px" autoplay
                                            playsinline></video>
                                        <canvas id="cameraCanvas" class="d-none"></canvas>
                                        <img id="capturedPhoto" class="d-none w-100 rounded-2"
                                            style="max-height:250px;object-fit:cover" alt="Captured">
                                        <div id="cameraPlaceholder">
                                            <i class="bi bi-camera" style="font-size:2.5rem;color:var(--gray-400)"></i>
                                            <p class="text-muted small mt-2">Click button below to open camera</p>
                                        </div>
                                        <div class="mt-2 d-flex gap-2 justify-content-center flex-wrap">
                                            <button type="button" class="btn btn-nu-blue btn-sm" id="startCameraBtn" onclick="startCamera()"><i
                                                    class="bi bi-camera me-1"></i>Start Camera</button>
                                            <button type="button" class="btn btn-gold btn-sm d-none" id="captureBtn"
                                                onclick="capturePhoto()"><i class="bi bi-camera-fill me-1"></i>Capture Photo</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="retakeBtn"
                                                onclick="retakePhoto()"><i class="bi bi-arrow-counterclockwise me-1"></i>Retake</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="photo_data" id="photoData">
                                </div>

                                <button type="submit" class="btn btn-nu-blue w-100 fw-700 py-2">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Submit Time-In Attendance
                                </button>
                            </form>
                        </div>
                    @elseif(!$registration->attendance->checked_out_at)
                        <!-- 2. TIME-OUT Photo Form -->
                        <div class="nu-card p-4 mb-3" style="border: 2px solid rgba(0,48,135,0.2);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-700 mb-0" style="color:var(--nu-blue)"><i class="bi bi-box-arrow-right me-2 text-primary"></i>Submit Time-Out Selfie</h6>
                                <span class="badge bg-primary-subtle text-primary">Step 2: Check Out</span>
                            </div>
                            <p class="text-muted small mb-4">You have checked in at <strong>{{ $registration->attendance->checked_in_at?->format('h:i A') }}</strong>. Please take a selfie to record your <strong>Time Out</strong>.</p>

                            <form action="{{ route('student.checkout', $registration->id) }}" method="POST" enctype="multipart/form-data"
                                id="checkoutForm">
                                @csrf

                                <!-- Camera capture option -->
                                <div class="mb-3">
                                    <label class="form-label fw-600">Take Time-Out Selfie (Camera)</label>
                                    <div id="cameraArea" class="text-center p-3 rounded-3"
                                        style="background:var(--gray-50);border:2px dashed var(--gray-200)">
                                        <video id="cameraPreview" class="d-none w-100 rounded-2" style="max-height:250px" autoplay
                                            playsinline></video>
                                        <canvas id="cameraCanvas" class="d-none"></canvas>
                                        <img id="capturedPhoto" class="d-none w-100 rounded-2"
                                            style="max-height:250px;object-fit:cover" alt="Captured">
                                        <div id="cameraPlaceholder">
                                            <i class="bi bi-camera" style="font-size:2.5rem;color:var(--gray-400)"></i>
                                            <p class="text-muted small mt-2">Click button below to open camera</p>
                                        </div>
                                        <div class="mt-2 d-flex gap-2 justify-content-center flex-wrap">
                                            <button type="button" class="btn btn-nu-blue btn-sm" id="startCameraBtn" onclick="startCamera()"><i
                                                    class="bi bi-camera me-1"></i>Start Camera</button>
                                            <button type="button" class="btn btn-gold btn-sm d-none" id="captureBtn"
                                                onclick="capturePhoto()"><i class="bi bi-camera-fill me-1"></i>Capture Photo</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="retakeBtn"
                                                onclick="retakePhoto()"><i class="bi bi-arrow-counterclockwise me-1"></i>Retake</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="photo_data" id="photoData">
                                </div>

                                <button type="submit" class="btn btn-gold w-100 fw-700 py-2">
                                    <i class="bi bi-box-arrow-right me-2"></i>Submit Time-Out & Complete
                                </button>
                            </form>
                        </div>
                    @endif
                @endif

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let stream = null;

        async function startCamera() {
            try {
                // adding facingMode user to prefer front camera
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                const video = document.getElementById('cameraPreview');
                video.srcObject = stream;
                
                video.onloadedmetadata = () => {
                    video.play();
                };

                video.classList.remove('d-none');
                document.getElementById('cameraPlaceholder').classList.add('d-none');
                document.getElementById('capturedPhoto').classList.add('d-none');
                document.getElementById('captureBtn').classList.remove('d-none');
                document.getElementById('retakeBtn').classList.add('d-none');
                const startBtn = document.getElementById('startCameraBtn');
                if (startBtn) startBtn.classList.add('d-none');
            } catch (e) {
                alert('Camera not available. Please use the file upload option.');
            }
        }

        function capturePhoto() {
            const video = document.getElementById('cameraPreview');
            const canvas = document.getElementById('cameraCanvas');
            
            // Set canvas size to match video
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Get base64 string directly - more reliable than Blob across browsers
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            
            // Show preview
            document.getElementById('capturedPhoto').src = dataUrl;
            document.getElementById('capturedPhoto').classList.remove('d-none');
            document.getElementById('cameraPreview').classList.add('d-none');
            document.getElementById('captureBtn').classList.add('d-none');
            document.getElementById('retakeBtn').classList.remove('d-none');
            
            // Save to input
            document.getElementById('photoData').value = dataUrl;
            
            // Stop camera
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
            }
        }

        function retakePhoto() {
            document.getElementById('photoData').value = '';
            document.getElementById('capturedPhoto').classList.add('d-none');
            startCamera();
        }

        // Dynamic QR Code Rotation Script
        let qrTimeRemaining = 15;
        const qrTimerText = document.getElementById('qr-timer-text');
        const qrProgressBar = document.getElementById('qr-progress-bar');
        const qrHolder = document.getElementById('qr-holder');
        
        function startQrCountdown() {
            setInterval(() => {
                qrTimeRemaining--;
                if (qrTimeRemaining <= 0) {
                    qrTimeRemaining = 15;
                    refreshQrCode();
                }
                if (qrTimerText) qrTimerText.innerText = `Rotates in ${qrTimeRemaining}s`;
                if (qrProgressBar) qrProgressBar.style.width = `${(qrTimeRemaining / 15) * 100}%`;
            }, 1000);
        }

        function refreshQrCode() {
            fetch('{{ route("student.qr.token", $registration->id) }}')
                .then(res => res.json())
                .then(data => {
                    if (data.qr_svg) {
                        qrHolder.innerHTML = data.qr_svg;
                        
                        const formTokenInput = document.querySelector('input[name="qr_token"]');
                        if (formTokenInput) {
                            formTokenInput.value = data.token;
                        }
                    }
                })
                .catch(err => console.error('Error rotating QR code:', err));
        }

        document.addEventListener('DOMContentLoaded', () => {
            startQrCountdown();
        });
    </script>
@endpush