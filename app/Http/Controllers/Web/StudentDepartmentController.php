<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\VenueReservation;
use App\Models\VenueReservationApproval;
use App\Models\FileHuntingSignatory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StudentDepartmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                if (!in_array(Auth::user()->role, ['student_department', 'admin'])) {
                    abort(403, 'Access denied. Authorized personnel only.');
                }
                return $next($request);
            }),
        ];
    }

    public function dashboard()
    {
        $user = Auth::user();
        $allReservations = VenueReservation::with(['event', 'approvals'])
            ->where('reserved_by', $user->id)
            ->orderByDesc('created_at')
            ->get();
            
        $activeReservations = $allReservations->filter(fn($r) => str_starts_with($r->status, 'pending_'));
        $historyReservations = $allReservations->filter(fn($r) => in_array($r->status, ['approved', 'rejected', 'cancelled']));

        // Fetch user's approved/published events to link reservations if needed.
        $myEvents = Event::where('organizer_id', $user->id)
            ->orderByDesc('event_date')
            ->get();

        // Get active file hunting chain
        $signatories = FileHuntingSignatory::where('is_active', 1)->orderBy('step_order')->get();

        return view('student_department.dashboard', compact('activeReservations', 'historyReservations', 'myEvents', 'signatories'));
    }

    public function calendarEvents()
    {
        $reservations = VenueReservation::with('event')
            ->where(function ($query) {
                $query->where('status', 'like', 'pending_%')
                      ->orWhere('status', 'approved');
            })
            ->get();

        $events = [];
        foreach ($reservations as $r) {
            $eventTitle = $r->event ? $r->event->title : ($r->event_title ?: 'Reserved');
            $events[] = [
                'id'    => $r->id,
                'title' => $eventTitle . ' (' . $r->venue_name . ')',
                'start' => $r->reserved_date->format('Y-m-d') . 'T' . $r->start_time,
                'end'   => $r->reserved_date->format('Y-m-d') . 'T' . $r->end_time,
                'color' => $r->status === 'approved' ? '#28a745' : '#ffc107',
            ];
        }

        return response()->json($events);
    }

    public function storeVenueReservation(Request $request)
    {
        $v = $request->validate([
            'event_id'           => 'required|string',
            'event_title'        => 'nullable|string',
            'venue_name'         => 'required|string',
            'custom_venue_name'  => 'nullable|string',
            'reserved_date'      => 'required|date|after_or_equal:+15 days',
            'start_time'         => 'required|date_format:H:i:s,H:i|after_or_equal:08:00',
            'end_time'           => 'required|date_format:H:i:s,H:i|after:start_time|before_or_equal:22:00',
            'expected_attendees' => 'nullable|integer|min:1',
            'purpose'            => 'nullable|string',
        ], [
            'reserved_date.after_or_equal' => 'Reservations must be made at least 15 days in advance.',
            'start_time.after_or_equal' => 'Reservation start time must be 08:00 AM or later.',
            'end_time.before_or_equal'  => 'Reservation end time must be 10:00 PM or earlier.',
        ]);

        $finalEventId = ($v['event_id'] === 'custom') ? null : $v['event_id'];
        $finalEventTitle = ($v['event_id'] === 'custom') ? $v['event_title'] : null;
        $finalVenueName = ($v['venue_name'] === 'Other') ? $v['custom_venue_name'] : $v['venue_name'];

        if (!$finalVenueName) {
            return back()->with('error', 'Please provide a venue name.');
        }

        if ($finalEventId === null && empty($finalEventTitle)) {
            return back()->with('error', 'Please provide a custom event title.');
        }

        // Buffer: 1 hour ingress/egress check
        $conflict = VenueReservation::where('venue_name', $finalVenueName)
            ->where('reserved_date', $v['reserved_date'])
            ->where(function ($query) {
                $query->where('status', 'like', 'pending_%')
                      ->orWhere('status', 'approved');
            })
            ->where(function ($q) use ($v) {
                // Adjust times for 1-hour buffer
                $startWithIngress = \Carbon\Carbon::parse($v['start_time'])->subHour()->format('H:i');
                $endWithEgress    = \Carbon\Carbon::parse($v['end_time'])->addHour()->format('H:i');
                $q->whereBetween('start_time', [$startWithIngress, $endWithEgress])
                  ->orWhereBetween('end_time', [$startWithIngress, $endWithEgress])
                  ->orWhere(function ($q2) use ($startWithIngress, $endWithEgress) {
                      $q2->where('start_time', '<=', $startWithIngress)
                         ->where('end_time', '>=', $endWithEgress);
                  });
            })
            ->exists();

        if ($conflict) {
            return back()->with('error', 'The requested venue is unavailable at this time due to conflicting events or ingress/egress constraints.');
        }

        $firstSignatory = FileHuntingSignatory::where('is_active', 1)->orderBy('step_order')->first();

        VenueReservation::create([
            'event_id'           => $finalEventId,
            'event_title'        => $finalEventTitle,
            'venue_name'         => $finalVenueName,
            'reserved_date'      => $v['reserved_date'],
            'start_time'         => $v['start_time'],
            'end_time'           => $v['end_time'],
            'expected_attendees' => $v['expected_attendees'] ?? 50,
            'purpose'            => $v['purpose'] ?? null,
            'reserved_by'        => Auth::id(),
            'status'             => $firstSignatory ? 'pending_' . $firstSignatory->role : 'approved',
        ]);

        return back()->with('success', 'Venue reservation request submitted for review.');
    }

    public function deleteVenueReservation($id)
    {
        $res = VenueReservation::where('reserved_by', Auth::id())->findOrFail($id);
        if (!str_starts_with($res->status, 'pending_')) {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }
        $res->status = 'cancelled';
        $res->save();
        return back()->with('success', 'Reservation request cancelled successfully.');
    }

    public function showPermissionForm($id)
    {
        $res = VenueReservation::with(['event', 'reservedBy', 'approvals.approver'])->findOrFail($id);
        
        // Authorization check: Only requestor or approvers/admins can view this form
        $user = Auth::user();
        if ($user->role === 'student' && $res->reserved_by !== $user->id) {
            abort(403, 'Unauthorized access to this document.');
        }

        return view('student_department.permission-form', compact('res'));
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('signature')) {
            $user = Auth::user();
            if ($user->e_signature_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->e_signature_path);
            }

            $path = $request->file('signature')->store('signatures', 'public');
            $user->update(['e_signature_path' => $path]);
        }

        return back()->with('success', 'E-signature uploaded successfully!');
    }
}
