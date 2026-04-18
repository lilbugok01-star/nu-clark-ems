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

                    <div class="d-flex justify-content-center mb-4 p-3"
                        style="background:white;border-radius:12px;border:2px solid var(--gray-200)">
                        {!! $qrCode !!}
                    </div>

                    @if($registration->isExpired())
                        <div class="alert alert-warning text-center rounded-3">
                            <i class="bi bi-hourglass-split me-1"></i> This QR code has <strong>expired</strong>.
                        </div>
                    @elseif($registration->attendance)
                        <div class="alert alert-success text-center rounded-3">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Attendance already submitted.
                            Status: <strong>{{ ucfirst($registration->attendance->status) }}</strong>
                        </div>
                    @else
                        <div class="alert text-center rounded-3"
                            style="background:rgba(0,48,135,.07);border:1px solid rgba(0,48,135,.2);color:var(--nu-blue)">
                            <i class="bi bi-info-circle me-1"></i>
                            Valid until <strong>{{ $registration->qr_expires_at?->format('M d, Y H:i') }}</strong>
                        </div>
                    @endif

                    <p class="text-center text-muted small">Show this QR code at the event for check-in, or submit a photo
                        below.</p>
                </div>

                <!-- Photo Check-in Form -->
                @if(!$registration->attendance && !$registration->isExpired())
                    <div class="nu-card p-4 mb-3">
                        <h6 class="fw-700 mb-1"><i class="bi bi-camera me-2" style="color:var(--nu-gold)"></i>Submit Photo
                            Attendance</h6>
                        <p class="text-muted small mb-4">Take a selfie or upload a photo as proof of attendance. The organizer
                            will verify it after the event.</p>

                        <form action="{{ route('student.checkin') }}" method="POST" enctype="multipart/form-data"
                            id="checkinForm">
                            @csrf
                            <input type="hidden" name="qr_token" value="{{ $registration->qr_token }}">

                            <!-- Camera capture option -->
                            <div class="mb-3">
                                <label class="form-label fw-600">Take Photo (Camera)</label>
                                <div id="cameraArea" class="text-center p-3 rounded-3"
                                    style="background:var(--gray-50);border:2px dashed var(--gray-200)">
                                    <video id="cameraPreview" class="d-none w-100 rounded-2" style="max-height:250px" autoplay
                                        playsinline></video>
                                    <canvas id="cameraCanvas" class="d-none"></canvas>
                                    <img id="capturedPhoto" class="d-none w-100 rounded-2"
                                        style="max-height:250px;object-fit:cover" alt="Captured">
                                    <div id="cameraPlaceholder">
                                        <i class="bi bi-camera" style="font-size:2.5rem;color:var(--gray-400)"></i>
                                        <p class="text-muted small mt-2">Click button below to use camera</p>
                                    </div>
                                    <div class="mt-2 d-flex gap-2 justify-content-center flex-wrap">
                                        <button type="button" class="btn btn-nu-blue btn-sm" id="startCameraBtn" onclick="startCamera()"><i
                                                class="bi bi-camera me-1"></i>Start Camera</button>
                                        <button type="button" class="btn btn-gold btn-sm d-none" id="captureBtn"
                                            onclick="capturePhoto()"><i class="bi bi-camera-fill me-1"></i>Capture</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="retakeBtn"
                                            onclick="retakePhoto()">Retake</button>
                                    </div>
                                </div>
                                <input type="hidden" name="photo_data" id="photoData">
                            </div>

                            <div class="text-center text-muted small my-2">— or —</div>

                            <!-- File upload option -->
                            <div class="mb-4">
                                <label class="form-label fw-600">Upload from Gallery</label>
                                <input type="file" name="photo" class="form-control" accept="image/*" id="photoFile"
                                    onchange="previewFile(event)">
                                <div id="previewContainer" class="mt-2 d-none">
                                    <img id="filePreview" class="rounded-3 w-100" style="max-height:200px;object-fit:cover"
                                        alt="Preview">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-nu-blue w-100 fw-700 py-2">
                                <i class="bi bi-send me-2"></i>Submit Attendance
                            </button>
                        </form>
                    </div>
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

        function previewFile(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => {
                document.getElementById('filePreview').src = ev.target.result;
                document.getElementById('previewContainer').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    </script>
@endpush