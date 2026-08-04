<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function register(Request $request, $eventId)
    {
        $event = Event::where('status', 'published')->findOrFail($eventId);
        $user  = $request->user();

        // Check event is still upcoming
        if ($event->event_date < now()->toDateString()) {
            return response()->json(['message' => 'This event has already passed.'], 422);
        }

        // Check capacity
        if ($event->isFull()) {
            return response()->json(['message' => 'This event is already at full capacity.'], 422);
        }

        // Check duplicate registration
        if (Registration::where('user_id', $user->id)->where('event_id', $eventId)->where('status', '!=', 'cancelled')->exists()) {
            return response()->json(['message' => 'You are already registered for this event.'], 422);
        }

        // Generate QR token (expires 24h after event ends)
        $qrToken = Registration::generateQrToken($user->id, $eventId);
        $qrExpiresAt = \Carbon\Carbon::parse($event->event_date->format('Y-m-d') . ' ' . $event->end_time)
                        ->addHours(24);

        $registration = Registration::create([
            'user_id'        => $user->id,
            'event_id'       => $eventId,
            'qr_token'       => $qrToken,
            'qr_expires_at'  => $qrExpiresAt,
            'status'         => 'confirmed',
            'registered_at'  => now(),
        ]);

        // Confirmation notification
        AppNotification::create([
            'user_id' => $user->id,
            'type'    => 'registration_confirmation',
            'title'   => 'Registration Confirmed: ' . $event->title,
            'message' => "You have been registered for {$event->title} on {$event->event_date->format('M d, Y')}.",
            'data'    => ['event_id' => $eventId, 'registration_id' => $registration->id],
        ]);

        return response()->json([
            'status'       => 'success',
            'message'      => 'Registration successful! Your QR code is ready.',
            'registration' => $registration->load('event'),
        ], 201);
    }

    public function myRegistrations(Request $request)
    {
        $registrations = Registration::with(['event.organizer', 'attendance'])
            ->where('user_id', $request->user()->id)
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->select('registrations.*')
            ->orderBy('events.event_date', 'asc')
            ->orderBy('events.start_time', 'asc')
            ->get();

        return response()->json($registrations);
    }

    public function cancel(Request $request, $id)
    {
        $registration = Registration::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($registration->event->event_date <= now()->toDateString()) {
            return response()->json(['message' => 'Cannot cancel registration for past events.'], 422);
        }

        $registration->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Registration cancelled successfully.']);
    }

    public function show($id)
    {
        $registration = Registration::with(['event', 'user', 'attendance'])->findOrFail($id);
        return response()->json($registration);
    }
}
