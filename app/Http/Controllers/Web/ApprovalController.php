<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventApproval;
use App\Models\VenueReservation;
use App\Models\VenueReservationApproval;
use App\Models\FileHuntingSignatory;
use App\Models\User;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ApprovalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                $role = Auth::user()->role;
                if (!in_array($role, ['adviser', 'department_head', 'dean', 'executive_director', 'student_development', 'program_chair'])) {
                    abort(403, 'Access denied. Approver roles only.');
                }
                return $next($request);
            }),
        ];
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        // --- Event Approvals ---
        $eventStatus = 'pending_' . $user->role;
        if ($user->role === 'department_head') {
            $eventStatus = 'pending_dept_head';
        }
        $pendingEvents = Event::where('status', $eventStatus)->with('organizer')->orderBy('created_at', 'desc')->get();
        $historyEvents = EventApproval::where('approver_id', $user->id)->with('event.organizer')->orderBy('created_at', 'desc')->get();

        // --- Venue Reservations (Dynamic Chain) ---
        $venueStatus = 'pending_' . $user->role;
        $legacyMap = [
            'student_development' => 'pending_student_dev',
            'program_chair' => 'pending_program_chair',
            'executive_director' => 'pending_director',
        ];

        $pendingVenues = VenueReservation::where(function($q) use ($venueStatus, $user, $legacyMap) {
            $q->where('status', $venueStatus);
            if (isset($legacyMap[$user->role])) {
                $q->orWhere('status', $legacyMap[$user->role]);
            }
        })->with(['reservedBy', 'approvals'])->orderBy('created_at', 'desc')->get();
        $historyVenues = VenueReservationApproval::where('approver_id', $user->id)->with('venueReservation.reservedBy')->orderBy('created_at', 'desc')->get();

        return view('approver.dashboard', compact('pendingEvents', 'historyEvents', 'pendingVenues', 'historyVenues', 'user'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('approver.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'e_signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('e_signature')) {
            if ($user->e_signature_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->e_signature_path);
            }
            $user->e_signature_path = $request->file('e_signature')->store('signatures', 'public');
            $user->save();
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * POST /approver/venues/{id}/open
     * Signatory clicks "Open This Document" — records opened_at timestamp.
     */
    public function openDocument($id)
    {
        $res  = VenueReservation::findOrFail($id);
        $user = Auth::user();

        $expectedStatus = 'pending_' . $user->role;
        $legacyMap = [
            'student_development' => 'pending_student_dev',
            'program_chair'       => 'pending_program_chair',
            'executive_director'  => 'pending_director',
        ];
        $expectedLegacy = $legacyMap[$user->role] ?? null;

        if ($res->status !== $expectedStatus && $res->status !== $expectedLegacy) {
            return back()->with('error', 'Document is not currently assigned to your queue.');
        }

        $currentSig = FileHuntingSignatory::where('role', $user->role)->where('is_active', 1)->first();
        if (!$currentSig) {
            return back()->with('error', 'You are not an active signatory for venue reservations.');
        }

        // Create or retrieve the approval record for tracking open state
        $approval = VenueReservationApproval::firstOrCreate(
            [
                'venue_reservation_id' => $res->id,
                'approver_id'          => $user->id,
                'role_level'           => $user->role,
            ],
            [
                'status'               => 'pending',
                'opened_at'            => now(),
            ]
        );

        // If it already exists but hasn't been opened yet, stamp it now
        if (!$approval->opened_at) {
            $approval->update(['opened_at' => now()]);
        }

        return back()->with('success', 'Document marked as opened. You may now Approve or Reject.');
    }

    public function approveEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = Auth::user();

        if (!$user->e_signature_path) {
            return back()->with('error', 'Please upload your E-Signature in your profile before approving.');
        }

        $request->validate(['comments' => 'nullable|string']);

        $currentStatus = $event->status;
        $nextStatus = '';

        if ($user->role === 'adviser' && $currentStatus === 'pending_adviser') {
            $nextStatus = 'pending_dept_head';
        } elseif ($user->role === 'department_head' && $currentStatus === 'pending_dept_head') {
            $nextStatus = 'pending_dean';
        } elseif ($user->role === 'dean' && $currentStatus === 'pending_dean') {
            $nextStatus = 'pending_director';
        } elseif ($user->role === 'executive_director' && $currentStatus === 'pending_director') {
            $nextStatus = 'published';
        } else {
            return back()->with('error', 'You cannot approve this event at this stage.');
        }

        // Record Approval
        EventApproval::create([
            'event_id'         => $event->id,
            'approver_id'      => $user->id,
            'role_level'       => $user->role,
            'status'           => 'approved',
            'comments'         => $request->comments,
            'e_signature_used' => $user->e_signature_path,
        ]);

        $event->update(['status' => $nextStatus]);

        // When fully approved and published, notify all students
        if ($nextStatus === 'published') {
            $students = User::where('role', 'student')->pluck('id');
            $notifs = $students->map(fn($uid) => [
                'user_id'    => $uid,
                'type'       => 'new_event',
                'title'      => 'New Event: ' . $event->title,
                'message'    => "A new event has been posted: {$event->title} on {$event->event_date->format('M d, Y')} at {$event->venue}.",
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();
            AppNotification::insert($notifs);
        }

        return back()->with('success', 'Event approved successfully.');
    }

    public function rejectEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = Auth::user();

        $request->validate(['comments' => 'required|string'], [
            'comments.required' => 'Please provide a reason for rejection (feedback).'
        ]);

        $currentStatus = $event->status;
        if (($user->role === 'adviser' && $currentStatus !== 'pending_adviser') ||
            ($user->role === 'department_head' && $currentStatus !== 'pending_dept_head') ||
            ($user->role === 'dean' && $currentStatus !== 'pending_dean') ||
            ($user->role === 'executive_director' && $currentStatus !== 'pending_director')) {
            return back()->with('error', 'You cannot reject this event because it is not in your queue.');
        }

        // Record Rejection
        EventApproval::create([
            'event_id'         => $event->id,
            'approver_id'      => $user->id,
            'role_level'       => $user->role,
            'status'           => 'rejected',
            'comments'         => $request->comments,
            'e_signature_used' => $user->e_signature_path,
        ]);

        $event->update(['status' => 'rejected']);

        return back()->with('success', 'Event rejected.');
    }

    // ══ Venue Reservation Approvals ═══════════════════════════
    
    public function approveVenue(Request $request, $id)
    {
        $res = VenueReservation::findOrFail($id);
        $user = Auth::user();

        if (!$user->e_signature_path) {
            return back()->with('error', 'Please upload your E-Signature in your profile before approving.');
        }

        $request->validate(['comments' => 'nullable|string']);

        $expectedStatus = 'pending_' . $user->role;
        $legacyMap = [
            'student_development' => 'pending_student_dev',
            'program_chair'       => 'pending_program_chair',
            'executive_director'  => 'pending_director',
        ];
        $expectedLegacy = $legacyMap[$user->role] ?? null;

        if ($res->status !== $expectedStatus && $res->status !== $expectedLegacy) {
            return back()->with('error', 'You cannot approve this reservation at this stage.');
        }

        $currentSig = FileHuntingSignatory::where('role', $user->role)->where('is_active', 1)->first();
        if (!$currentSig) {
            return back()->with('error', 'You are no longer an active signatory.');
        }

        $nextSig = FileHuntingSignatory::where('is_active', 1)
                    ->where('step_order', '>', $currentSig->step_order)
                    ->orderBy('step_order')
                    ->first();

        $nextStatus = $nextSig ? 'pending_' . $nextSig->role : 'approved';

        VenueReservationApproval::updateOrCreate(
            [
                'venue_reservation_id' => $res->id,
                'approver_id'          => $user->id,
                'role_level'           => $user->role,
            ],
            [
                'status'           => 'approved',
                'comments'         => $request->comments,
                'e_signature_used' => $user->e_signature_path,
            ]
        );

        $res->update(['status' => $nextStatus]);

        return back()->with('success', 'Venue reservation approved.');
    }

    public function rejectVenue(Request $request, $id)
    {
        $res = VenueReservation::findOrFail($id);
        $user = Auth::user();

        $request->validate(['comments' => 'required|string'], [
            'comments.required' => 'Please provide a reason for rejection (feedback).'
        ]);

        $expectedStatus = 'pending_' . $user->role;
        $legacyMap = [
            'student_development' => 'pending_student_dev',
            'program_chair'       => 'pending_program_chair',
            'executive_director'  => 'pending_director',
        ];
        $expectedLegacy = $legacyMap[$user->role] ?? null;

        if ($res->status !== $expectedStatus && $res->status !== $expectedLegacy) {
            return back()->with('error', 'You cannot reject this reservation because it is not in your queue.');
        }

        VenueReservationApproval::updateOrCreate(
            [
                'venue_reservation_id' => $res->id,
                'approver_id'          => $user->id,
                'role_level'           => $user->role,
            ],
            [
                'status'           => 'rejected',
                'comments'         => $request->comments,
                'e_signature_used' => $user->e_signature_path,
            ]
        );

        $res->update(['status' => 'rejected']);

        return back()->with('success', 'Venue reservation rejected.');
    }

    public function showPermissionForm($id)
    {
        $res = \App\Models\VenueReservation::with(['event', 'reservedBy', 'approvals.approver'])->findOrFail($id);
        
        $user = Auth::user();
        
        // Authorization check: Only approvers who are somehow involved can view it
        // Check if the user has an approval record for this venue, or is admin
        $hasApproval = \App\Models\VenueReservationApproval::where('venue_reservation_id', $id)
            ->where('approver_id', $user->id)
            ->exists();
            
        if (!$hasApproval && $user->role !== 'admin' && $user->role !== 'student_department' && $user->role !== 'organizer') {
            abort(403, 'Unauthorized access to this document.');
        }

        return view('student_department.permission-form', compact('res'));
    }
}
