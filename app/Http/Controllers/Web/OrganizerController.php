<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Attendance;
use App\Models\AppNotification;
use App\Exports\AttendanceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OrganizerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                if (!Auth::check() || !in_array(Auth::user()->role, ['organizer', 'student_development', 'admin'])) {
                    abort(403, 'Access denied. Organizers only.');
                }
                return $next($request);
            }),
        ];
    }

    public function dashboard()
    {
        $user       = Auth::user();
        $myEvents   = Event::where('organizer_id', $user->id)->orderByDesc('event_date')->take(5)->get()
            ->map(function ($e) { $e->reg_count = $e->registeredCount(); return $e; });
        $stats = [
            'total_events'    => Event::where('organizer_id', $user->id)->count(),
            'upcoming_events' => Event::where('organizer_id', $user->id)->upcoming()->count(),
            'total_regs'      => Registration::whereHas('event', fn($q) => $q->where('organizer_id', $user->id))->count(),
            'verified_att'    => Attendance::whereHas('registration.event', fn($q) => $q->where('organizer_id', $user->id))
                ->where('status', 'verified')->count(),
        ];
        $pendingVerifications = Attendance::with(['registration.user', 'registration.event'])
            ->whereHas('registration.event', fn($q) => $q->where('organizer_id', $user->id))
            ->where('status', 'pending')->take(20)->get();

        return view('organizer.dashboard', compact('user', 'myEvents', 'stats', 'pendingVerifications'));
    }

    public function events()
    {
        $user = Auth::user();
        $events = Event::where('organizer_id', $user->id)
            ->withCount('registrations')
            ->orderByDesc('event_date')->paginate(10);
        return view('organizer.events', compact('events'));
    }

    public function createEvent()
    {
        return view('organizer.event-form', ['event' => null]);
    }

    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue'       => 'required|string',
            'event_date'  => 'required|date|after_or_equal:+15 days',
            'start_time'  => 'required|date_format:H:i|after_or_equal:08:00',
            'end_time'    => 'required|date_format:H:i|after:start_time|before_or_equal:22:00',
            'capacity'    => 'required|integer|min:1',
            'category'    => 'nullable|string',
            'is_featured' => 'boolean',
            'poster'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'event_date.after_or_equal' => 'Events must be created at least 15 days in advance.',
            'start_time.after_or_equal' => 'Event start time must be 08:00 AM or later to allow 1-hour ingress from 07:00 AM.',
            'end_time.before_or_equal'  => 'Event end time must be 10:00 PM or earlier to allow 1-hour egress until 11:00 PM.',
        ]);

        $poster_path = null;
        if ($request->hasFile('poster')) {
            $poster_path = $request->file('poster')->store('posters', 's3');
        }

        // Events are published immediately — no signatory or approval workflow required.
        $event = Event::create([
            ...$validated,
            'organizer_id'  => Auth::id(),
            'poster_path'   => $poster_path,
            'status'        => 'published',
            'is_featured'   => $request->boolean('is_featured'),
        ]);

        // Notify all students immediately since the event is published on creation.
        $students = \App\Models\User::where('role', 'student')->pluck('id');
        $notifs = $students->map(fn($uid) => [
            'user_id'    => $uid,
            'type'       => 'new_event',
            'title'      => 'New Event: ' . $event->title,
            'message'    => "A new event has been posted: {$event->title} on {$event->event_date->format('M d, Y')} at {$event->venue}.",
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();
        \App\Models\AppNotification::insert($notifs);

        return redirect()->route('organizer.events')->with('success', 'Event created successfully!');
    }

    public function editEvent($id)
    {
        $event = Event::where('organizer_id', Auth::id())->findOrFail($id);
        return view('organizer.event-form', compact('event'));
    }

    public function updateEvent(Request $request, $id)
    {
        $event = Event::where('organizer_id', Auth::id())->findOrFail($id);
        $validated = $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'venue'       => 'required|string',
            'event_date'  => 'required|date|after_or_equal:+15 days',
            'start_time'  => 'required|date_format:H:i|after_or_equal:08:00',
            'end_time'    => 'required|date_format:H:i|after:start_time|before_or_equal:22:00',
            'capacity'    => 'required|integer|min:1',
            'status'      => 'in:draft,pending_adviser,cancelled,published',
            'category'    => 'nullable|string',
            'poster'      => 'nullable|image|max:4096',
        ], [
            'event_date.after_or_equal' => 'Events must be created at least 15 days in advance.',
            'start_time.after_or_equal' => 'Event start time must be 08:00 AM or later to allow 1-hour ingress from 07:00 AM.',
            'end_time.before_or_equal'  => 'Event end time must be 10:00 PM or earlier to allow 1-hour egress until 11:00 PM.',
        ]);

        if ($request->hasFile('poster')) {
            if ($event->poster_path) \Illuminate\Support\Facades\Storage::disk('s3')->delete($event->poster_path);
            $validated['poster_path'] = $request->file('poster')->store('posters', 's3');
        }
        $event->update($validated);

        return redirect()->route('organizer.events')->with('success', 'Event updated!');
    }

    public function deleteEvent($id)
    {
        $event = Event::where('organizer_id', Auth::id())->findOrFail($id);
        $event->delete();
        return back()->with('success', 'Event deleted.');
    }

    public function attendees($id)
    {
        $event = Event::where('organizer_id', Auth::id())->findOrFail($id);
        $attendances = Attendance::with(['registration.user.course', 'registration.user.section', 'verifiedBy'])
            ->whereHas('registration', fn($q) => $q->where('event_id', $id))->get();
        $registrations = Registration::with('user.course', 'user.section')
            ->where('event_id', $id)->where('status', '!=', 'cancelled')->get();

        return view('organizer.attendees', compact('event', 'attendances', 'registrations'));
    }

    public function verify(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes'  => 'nullable|string|max:500',
        ]);

        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'status'      => $request->status,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'notes'       => $request->notes,
        ]);
        return back()->with('success', 'Attendance ' . $request->status . '.');
    }

    public function exportPdf($eventId)
    {
        $event       = Event::where('organizer_id', Auth::id())->findOrFail($eventId);
        $attendances = Attendance::with(['registration.user.course', 'registration.user.section'])
            ->whereHas('registration', fn($q) => $q->where('event_id', $eventId))->get();
        $pdf = Pdf::loadView('reports.attendance-pdf', compact('event', 'attendances'))->setPaper('a4');
        return $pdf->download("attendance-{$eventId}.pdf");
    }

    public function exportExcel($eventId)
    {
        Event::where('organizer_id', Auth::id())->findOrFail($eventId);
        return Excel::download(new AttendanceExport($eventId), "attendance-{$eventId}.xlsx");
    }

    public function allAttendees(Request $request)
    {
        $user = Auth::user();
        $myEventIds = Event::where('organizer_id', $user->id)->pluck('id');

        $query = Registration::with(['user.course', 'user.section', 'event', 'attendance'])
            ->whereIn('event_id', $myEventIds)
            ->where('status', '!=', 'cancelled');

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'attended') {
                $query->whereHas('attendance', fn($q) => $q->where('status', 'verified'));
            } elseif ($request->status === 'not_attended') {
                $query->whereDoesntHave('attendance', fn($q) => $q->where('status', 'verified'));
            }
        }

        $registrations = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $myEvents = Event::where('organizer_id', $user->id)->orderByDesc('event_date')->get();

        return view('organizer.all-attendees', compact('registrations', 'myEvents'));
    }

    public function analytics()
    {
        $user = Auth::user();
        $events = Event::where('organizer_id', $user->id)
            ->withCount(['registrations', 'attendances as verified_count' => fn($q) => $q->where('attendances.status', 'verified')])
            ->orderByDesc('event_date')->get();
        return view('organizer.analytics', compact('events'));
    }

    // Venue Reservations have been moved to StudentDepartmentController as per workflow update.

    /**
     * GET /organizer/scan/{token}
     * Universal camera-based QR check-in. Scans the student's QR code URL
     * and automatically marks attendance as verified.
     */
    public function scanQr($token)
    {
        $registration = Registration::with(['event', 'user'])->where('qr_token', $token)->first();

        if (!$registration) {
            return view('organizer.scan-result', ['status' => 'error', 'message' => 'Invalid QR Code. Registration not found.']);
        }

        if ($registration->isExpired()) {
            return view('organizer.scan-result', ['status' => 'error', 'message' => 'This QR Code has expired. Registration is no longer valid.']);
        }

        $event = $registration->event;
        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        $eventDate = $event->event_date instanceof \DateTimeInterface
            ? $event->event_date->format('Y-m-d')
            : \Carbon\Carbon::parse($event->event_date)->toDateString();

        if ($eventDate !== $today) {
            return view('organizer.scan-result', [
                'status' => 'error',
                'message' => 'Attendance can only be scanned on the day of the event (' . \Carbon\Carbon::parse($eventDate)->format('M d, Y') . ').',
            ]);
        }

        if ($currentTime < $event->start_time || $currentTime > $event->end_time) {
            $start = \Carbon\Carbon::parse($event->start_time)->format('h:i A');
            $end = \Carbon\Carbon::parse($event->end_time)->format('h:i A');
            return view('organizer.scan-result', [
                'status' => 'error',
                'message' => "Attendance is only open during the event window ({$start} – {$end}).",
            ]);
        }

        if ($registration->attendance) {
            return view('organizer.scan-result', [
                'status' => 'warning',
                'message' => 'This student has already checked in.',
                'registration' => $registration,
            ]);
        }

        Attendance::create([
            'registration_id' => $registration->id,
            'checked_in_at'   => now(),
            'status'          => 'verified',
        ]);

        return view('organizer.scan-result', [
            'status' => 'success',
            'message' => 'Successfully Checked In!',
            'registration' => $registration,
        ]);
    }
}
