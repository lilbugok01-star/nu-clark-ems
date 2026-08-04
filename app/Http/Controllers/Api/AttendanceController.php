<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Registration;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * POST /api/attendance/checkin
     * Validates QR token and records photo-based check-in.
     */
    public function checkin(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        if (!$request->hasFile('photo') && empty($request->photo_data)) {
            return response()->json(['status' => 'error', 'message' => 'A photo is required for attendance check-in. Please take a selfie.'], 422);
        }

        $token = $request->qr_token;
        $parts = explode('|', $token);
        $registration = null;

        if (count($parts) === 3) {
            $registrationId = $parts[0];
            $expiresAt = $parts[1];
            $signature = $parts[2];

            $expectedSignature = hash_hmac('sha256', $registrationId . '|' . $expiresAt, config('app.key'));
            if (!hash_equals($expectedSignature, $signature)) {
                \App\Models\AttendanceAuditLog::create([
                    'qr_token' => $token,
                    'action' => 'selfie_checkin',
                    'status' => 'invalid_signature',
                    'ip_address' => request()->ip(),
                    'device_info' => request()->userAgent(),
                ]);
                return response()->json(['status' => 'error', 'message' => 'Invalid QR Code signature.'], 422);
            }

            if (now()->timestamp > $expiresAt) {
                \App\Models\AttendanceAuditLog::create([
                    'registration_id' => $registrationId,
                    'qr_token' => $token,
                    'action' => 'selfie_checkin',
                    'status' => 'expired_token',
                    'ip_address' => request()->ip(),
                    'device_info' => request()->userAgent(),
                ]);
                return response()->json(['status' => 'error', 'message' => 'This QR Code has expired. Please refresh the QR code on the student app.'], 422);
            }

            $registration = Registration::with('event', 'user')->find($registrationId);
        } else {
            $registration = Registration::with('event', 'user')
                ->where('qr_token', $token)
                ->first();
        }

        if (!$registration) {
            \App\Models\AttendanceAuditLog::create([
                'qr_token' => $token,
                'action' => 'selfie_checkin',
                'status' => 'invalid_token',
                'ip_address' => request()->ip(),
                'device_info' => request()->userAgent(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Invalid QR code.'], 404);
        }

        if ($registration->status === 'cancelled') {
            \App\Models\AttendanceAuditLog::create([
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
                'registration_id' => $registration->id,
                'qr_token' => $token,
                'action' => 'selfie_checkin',
                'status' => 'cancelled_registration',
                'ip_address' => request()->ip(),
                'device_info' => request()->userAgent(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Registration is cancelled.'], 422);
        }

        if ($registration->isExpired()) {
            \App\Models\AttendanceAuditLog::create([
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
                'registration_id' => $registration->id,
                'qr_token' => $token,
                'action' => 'selfie_checkin',
                'status' => 'expired_registration',
                'ip_address' => request()->ip(),
                'device_info' => request()->userAgent(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'QR code has expired.'], 422);
        }

        // ── Live-window check (Forced PHT to avoid server sync issues) ─────
        $event       = $registration->event;
        $now         = \Carbon\Carbon::now('Asia/Manila');
        $today       = $now->toDateString();

        if ($event->event_date->toDateString() !== $today) {
            \App\Models\AttendanceAuditLog::create([
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
                'registration_id' => $registration->id,
                'qr_token' => $token,
                'action' => 'selfie_checkin',
                'status' => 'outside_event_window',
                'ip_address' => request()->ip(),
                'device_info' => request()->userAgent(),
            ]);
            \Illuminate\Support\Facades\Log::warning("Attendance Rejected (Date): User [{$registration->user->id}] attempted check-in for event [{$event->id}] scheduled for [{$event->event_date->toDateString()}] but server today is [{$today}].");
            return response()->json([
                'status'  => 'error',
                'message' => 'Attendance can only be recorded on the day of the event (' . $event->event_date->format('M d, Y') . ').',
            ], 422);
        }

        $eventStartTime = \Carbon\Carbon::parse($event->event_date->toDateString() . ' ' . $event->start_time, 'Asia/Manila');
        $eventEndTime   = \Carbon\Carbon::parse($event->event_date->toDateString() . ' ' . $event->end_time, 'Asia/Manila');

        if ($now->lt($eventStartTime) || $now->gt($eventEndTime)) {
            \App\Models\AttendanceAuditLog::create([
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
                'registration_id' => $registration->id,
                'qr_token' => $token,
                'action' => 'selfie_checkin',
                'status' => 'outside_event_window',
                'ip_address' => request()->ip(),
                'device_info' => request()->userAgent(),
            ]);
            $start = $eventStartTime->format('h:i A');
            $end   = $eventEndTime->format('h:i A');
            \Illuminate\Support\Facades\Log::warning("Attendance Rejected (Time): User [{$registration->user->id}] attempted check-in for event [{$event->id}] at [{$now->format('H:i:s')}] but window is [{$event->start_time} - {$event->end_time}].");
            return response()->json([
                'status'  => 'error',
                'message' => "Attendance is only open during the event window ({$start} – {$end}).",
            ], 422);
        }

        if ($registration->attendance) {
            \App\Models\AttendanceAuditLog::create([
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
                'registration_id' => $registration->id,
                'qr_token' => $token,
                'action' => 'selfie_checkin',
                'status' => 'duplicate',
                'ip_address' => request()->ip(),
                'device_info' => request()->userAgent(),
            ]);
            return response()->json([
                'status'  => 'already_checked_in',
                'message' => 'Attendance already recorded.',
                'data'    => $registration->attendance,
            ]);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store(
                'attendance-photos/' . $registration->event_id, 's3'
            );
        }

        $attendance = Attendance::create([
            'registration_id' => $registration->id,
            'photo_path'      => $photoPath,
            'checked_in_at'   => now(),
            'status'          => 'pending',
        ]);

        \App\Models\AttendanceAuditLog::create([
            'user_id' => $registration->user_id,
            'event_id' => $registration->event_id,
            'registration_id' => $registration->id,
            'qr_token' => $token,
            'action' => 'selfie_checkin',
            'status' => 'success',
            'ip_address' => request()->ip(),
            'device_info' => request()->userAgent(),
        ]);

        AppNotification::create([
            'user_id' => $registration->user_id,
            'type'    => 'attendance_recorded',
            'title'   => 'Attendance Recorded',
            'message' => "Your attendance for {$registration->event->title} has been recorded. Awaiting verification.",
            'data'    => ['event_id' => $registration->event_id, 'attendance_id' => $attendance->id],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Attendance recorded',
            'data'    => $attendance,
        ], 201);
    }

    /**
     * GET /api/events/{id}/attendance — list attendees for an event
     */
    public function eventAttendance($eventId)
    {
        $attendances = Attendance::with(['registration.user.course', 'registration.user.section', 'verifiedBy'])
            ->whereHas('registration', fn($q) => $q->where('event_id', $eventId))
            ->get();

        return response()->json($attendances);
    }

    /**
     * GET /api/attendance/{id}
     */
    public function show($id)
    {
        $attendance = Attendance::with(['registration.user', 'registration.event', 'verifiedBy'])
            ->findOrFail($id);
        return response()->json($attendance);
    }

    /**
     * PUT /api/attendance/{id}/verify
     */
    public function verify(Request $request, $id)
    {
        $attendance = Attendance::with('registration.event')->findOrFail($id);

        // Ownership check: only event organizer or admin
        $event = $attendance->registration->event;
        if ($event->organizer_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'You can only verify attendance for your own events.'], 403);
        }

        $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes'  => 'nullable|string',
        ]);

        $attendance->update([
            'status'      => $request->status,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'notes'       => $request->notes,
        ]);

        $statusLabel = ucfirst($request->status);
        AppNotification::create([
            'user_id' => $attendance->registration->user_id,
            'type'    => 'attendance_' . $request->status,
            'title'   => "Attendance {$statusLabel}",
            'message' => "Your attendance for {$attendance->registration->event->title} has been {$request->status}.",
            'data'    => ['attendance_id' => $attendance->id],
        ]);

        return response()->json(['status' => 'success', 'attendance' => $attendance->fresh('verifiedBy')]);
    }
}
