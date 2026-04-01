<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permission Form - {{ $res->event?->title ?? $res->event_title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .container { width: 100% !important; max-width: 100% !important; padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .permission-form { background: white; padding: 50px; border-radius: 0; box-shadow: 0 0 20px rgba(0,0,0,0.1); max-width: 850px; margin: 30px auto; min-height: 1050px; position: relative; border: 1px solid #ddd; }
        .nu-header { border-bottom: 3px solid #003087; padding-bottom: 20px; margin-bottom: 30px; }
        .nu-logo { width: 80px; height: 80px; background: #003087; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 2rem; border-radius: 8px; }
        .form-title { color: #003087; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
        .info-label { font-weight: 700; color: #555; width: 180px; display: inline-block; font-size: 0.9rem; }
        .info-value { font-weight: 400; color: #000; border-bottom: 1px solid #eee; display: inline-block; width: calc(100% - 190px); padding-bottom: 2px; }
        .signature-box { border-bottom: 1px solid #333; min-height: 80px; position: relative; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 5px; margin-top: 15px; }
        .signature-img { position: absolute; max-height: 70px; max-width: 150px; bottom: 10px; object-fit: contain; }
        .signer-name { font-weight: 700; font-size: 0.85rem; text-transform: uppercase; margin-top: 5px; }
        .signer-role { font-size: 0.75rem; color: #666; text-transform: capitalize; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 8rem; opacity: 0.05; font-weight: 900; pointer-events: none; color: #003087; }
    </style>
</head>
<body>

<div class="container no-print py-4 text-center">
    <button onclick="window.print()" class="btn btn-primary px-4 fw-bold shadow-sm">
        <i class="bi bi-printer me-2"></i>Print Permission Form
    </button>
    <a href="{{ route('student_department.dashboard') }}" class="btn btn-outline-secondary px-4 ms-2">
        <i class="bi bi-arrow-left me-2"></i>Back to Tracker
    </a>
</div>

<div class="permission-form shadow">
    <div class="watermark">{{ strtoupper($res->status) }}</div>

    <!-- Header -->
    <div class="nu-header d-flex align-items-center gap-4">
        <div class="nu-logo">NU</div>
        <div>
            <h2 class="mb-1" style="color:#003087; font-weight: 900;">NATIONAL UNIVERSITY</h2>
            <h5 class="mb-0 text-muted fw-bold">CLARK CAMPUS</h5>
        </div>
        <div class="ms-auto text-end">
            <h4 class="form-title mb-0">Venue Reservation<br>Permission Form</h4>
        </div>
    </div>

    <!-- Details Section -->
    <div class="mb-5">
        <h6 class="bg-light p-2 fw-bold text-uppercase mb-3" style="border-left: 5px solid #003087;">Event Information</h6>
        <div class="mb-3">
            <span class="info-label text-uppercase">Event Title:</span>
            <span class="info-value fw-bold">{{ $res->event?->title ?? $res->event_title }}</span>
        </div>
        <div class="row mb-3">
            <div class="col-6">
                <span class="info-label text-uppercase">Venue Request:</span>
                <span class="info-value">{{ $res->venue_name }}</span>
            </div>
            <div class="col-6">
                <span class="info-label text-uppercase">Date:</span>
                <span class="info-value">{{ $res->reserved_date->format('F d, Y') }}</span>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-6">
                <span class="info-label text-uppercase">Time:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($res->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($res->end_time)->format('h:i A') }}</span>
            </div>
            <div class="col-6">
                <span class="info-label text-uppercase">Attendees:</span>
                <span class="info-value">{{ $res->expected_attendees ?? 'N/A' }} Pax</span>
            </div>
        </div>
        <div class="mb-3">
            <span class="info-label text-uppercase">Purpose:</span>
            <span class="info-value">{{ $res->purpose ?? 'University Event / Student Activity' }}</span>
        </div>
    </div>

    <div class="mb-5">
        <h6 class="bg-light p-2 fw-bold text-uppercase mb-3" style="border-left: 5px solid #003087;">Department Details</h6>
        <div class="mb-3">
            <span class="info-label text-uppercase">Reserved By:</span>
            <span class="info-value">{{ $res->reservedBy->name }}</span>
        </div>
        <div class="mb-3">
            <span class="info-label text-uppercase">Submission Date:</span>
            <span class="info-value">{{ $res->created_at->format('F d, Y h:i A') }}</span>
        </div>
    </div>

    <!-- Signatures Section -->
    <div class="mt-5 pt-4">
        <h6 class="bg-light p-2 fw-bold text-uppercase mb-4" style="border-left: 5px solid #003087;">Approval Chain & E-Signatures</h6>
        
        <div class="row g-5">
            <!-- Requester -->
            <div class="col-4 text-center mb-5">
                <div class="signature-box">
                    <div class="fw-bold small fst-italic text-muted">Electronically Signed</div>
                </div>
                <div class="signer-name">{{ $res->reservedBy->name }}</div>
                <div class="signer-role">Requestor / Student Rep</div>
            </div>

            <!-- Student Development -->
            @php $sd = $res->approvals->where('role_level', 'student_development')->where('status', 'approved')->first(); @endphp
            <div class="col-4 text-center mb-5">
                <div class="signature-box">
                    @if($sd && $sd->e_signature_used)
                        <img src="{{ asset('storage/' . $sd->e_signature_used) }}" class="signature-img">
                    @endif
                </div>
                <div class="signer-name">{{ $sd->approver->name ?? 'Engr Vernie B Garcia' }}</div>
                <div class="signer-role">Student Development</div>
            </div>

            <!-- Program Chair -->
            @php $pc = $res->approvals->where('role_level', 'program_chair')->where('status', 'approved')->first(); @endphp
            <div class="col-4 text-center mb-5">
                <div class="signature-box">
                    @if($pc && $pc->e_signature_used)
                        <img src="{{ asset('storage/' . $pc->e_signature_used) }}" class="signature-img">
                    @endif
                </div>
                <div class="signer-name">{{ $pc->approver->name ?? 'Ronielle B. Antonio' }}</div>
                <div class="signer-role">Program Chair</div>
            </div>

            <!-- Dean -->
            @php $dn = $res->approvals->where('role_level', 'dean')->where('status', 'approved')->first(); @endphp
            <div class="col-4 text-center">
                <div class="signature-box">
                    @if($dn && $dn->e_signature_used)
                        <img src="{{ asset('storage/' . $dn->e_signature_used) }}" class="signature-img">
                    @endif
                </div>
                <div class="signer-name">{{ $dn->approver->name ?? 'Rafaela Mae M. Landayan' }}</div>
                <div class="signer-role">Dean / Department Head</div>
            </div>

            <!-- Exec Director -->
            @php $ed = $res->approvals->where('role_level', 'executive_director')->where('status', 'approved')->first(); @endphp
            <div class="col-4 text-center">
                <div class="signature-box">
                    @if($ed && $ed->e_signature_used)
                        <img src="{{ asset('storage/' . $ed->e_signature_used) }}" class="signature-img">
                    @endif
                </div>
                <div class="signer-name">{{ $ed->approver->name ?? 'Dr. Arnell A. Diego' }}</div>
                <div class="signer-role">Executive Director</div>
            </div>
            
            <div class="col-4 text-center d-flex align-items-center justify-content-center">
                <div class="p-3 border rounded text-muted small fst-italic bg-light">
                    Generated from NU Clark Event Management System on {{ date('M d, Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="mt-5 pt-5 text-muted small text-center" style="position: absolute; bottom: 50px; width: calc(100% - 100px);">
        <p>This document serves as an official permission form. Any alterations to this printed form without system validation are invalid.</p>
    </div>
</div>

<script>
    // Auto-trigger print dialog if requested
    if (window.location.search.includes('print=1')) {
        window.print();
    }
</script>

</body>
</html>
