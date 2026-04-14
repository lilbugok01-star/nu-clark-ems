<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Attendance;
use App\Models\AppNotification;
use App\Models\VenueReservation;
use App\Models\FileHuntingSignatory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                if (!Auth::check() || Auth::user()->role !== 'admin') {
                    abort(403, 'Access denied. Admin only.');
                }
                return $next($request);
            }),
        ];
    }


    public function dashboard()
    {
        $stats = [
            'total_students'      => User::where('role', 'student')->count(),
            'total_organizers'    => User::where('role', 'organizer')->count(),
            'total_events'        => Event::count(),
            'upcoming_events'     => Event::upcoming()->count(),
            'total_registrations' => Registration::where('status', '!=', 'cancelled')->count(),
            'total_attendances'   => Attendance::where('status', 'verified')->count(),
        ];

        $recentUsers  = User::latest()->take(5)->get();
        $recentEvents = Event::with('organizer')->latest()->take(5)->get();

        // Monthly registrations for chart
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthlyData[] = [
                'month' => $m->format('M'),
                'count' => Registration::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
            ];
        }

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentEvents', 'monthlyData'));
    }

    public function users(Request $request)
    {
        $query = User::with('course', 'section');
        if ($request->role) $query->where('role', $request->role);
        
        if ($request->search) {
            $search = trim($request->search);
            // Replace percent and underscore to prevent LIKE wildcards abuse
            $search = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        
        $users   = $query->paginate(20);
        $courses = Course::where('is_active', true)->get();
        $sections= Section::where('is_active', true)->with('course')->get();
        return view('admin.users', compact('users', 'courses', 'sections'));
    }

    public function storeUser(Request $request)
    {
        $allRoles = 'admin,organizer,student,adviser,department_head,dean,executive_director,student_development,program_chair,student_department';

        $rules = [
            'name'       => 'required|string',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:8',
            'role'       => 'required|in:' . $allRoles,
            'student_id' => 'nullable|string|unique:users,student_id',
            'course_id'  => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:sections,id',
        ];

        if ($request->role === 'student') {
            $rules['email'] = [
                'required', 'email', 'unique:users',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@student.nu-clark.edu.ph')) {
                        $fail('Student accounts must use an official NU Clark email (@student.nu-clark.edu.ph).');
                    }
                },
            ];
            $rules['student_id'] = 'required|string|unique:users,student_id';
            $rules['course_id']  = 'required|exists:courses,id';
            $rules['section_id'] = 'required|exists:sections,id';
        }

        $v = $request->validate($rules);
        $v['password'] = Hash::make($v['password']);

        // Handle optional e-signature upload
        if ($request->hasFile('e_signature')) {
            $v['e_signature_path'] = $request->file('e_signature')->store('signatures', 's3');
        }

        User::create($v);
        return back()->with('success', 'User created successfully!');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $allRoles = 'admin,organizer,student,adviser,department_head,dean,executive_director,student_development,program_chair,student_department';

        $rules = [
            'name'      => 'sometimes|string',
            'role'      => 'sometimes|in:' . $allRoles,
            'is_active' => 'sometimes|boolean',
        ];

        $role = $request->input('role', $user->role);

        // Only validate student-specific fields if the role is student
        // AND those fields were actually submitted (so simple deactivation works)
        if ($role === 'student' && $request->has('student_id')) {
            $rules['student_id'] = 'required|string|unique:users,student_id,' . $id;
            $rules['course_id']  = 'required|exists:courses,id';
            $rules['section_id'] = 'required|exists:sections,id';
        } elseif ($role !== 'student') {
            // Clear student fields when switching away from student role
            $request->merge(['student_id' => null, 'course_id' => null, 'section_id' => null]);
            $rules['student_id'] = 'nullable';
            $rules['course_id']  = 'nullable';
            $rules['section_id'] = 'nullable';
        }

        $v = $request->validate($rules);

        // Track if role changed
        $oldRole = $user->role;

        $user->update($v);

        // If role changed, force the user to re-login so they see their new dashboard
        if (isset($v['role']) && $v['role'] !== $oldRole) {
            // Invalidate remember token to force re-authentication
            $user->update(['remember_token' => null]);

            // If the admin is editing themselves, don't log themselves out
            if ((int)$id !== Auth::id()) {
                // Clear all sessions for this user by cycling their password hash
                // This is a lightweight approach — the user will need to re-login
                \Illuminate\Support\Facades\DB::table('sessions')
                    ->where('user_id', $id)
                    ->delete();
            }
        }

        return back()->with('success', 'User updated!');
    }

    public function deleteUser($id)
    {
        if ((int)$id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        User::findOrFail($id)->delete();
        return back()->with('success', 'User deleted.');
    }

    public function courses()
    {
        $courses = Course::with('sections')->get();
        return view('admin.courses', compact('courses'));
    }

    public function storeCourse(Request $request)
    {
        $v = $request->validate(['code' => 'required|unique:courses', 'name' => 'required']);
        Course::create($v);
        return back()->with('success', 'Course added!');
    }

    public function reports()
    {
        $events = Event::with('organizer')
            ->withCount(['registrations', 'attendances as verified_count' => fn($q) => $q->where('attendances.status', 'verified')])
            ->orderByDesc('event_date')->paginate(15);
        return view('admin.reports', compact('events'));
    }

    public function exportEventsPdf()
    {
        $events = Event::with('organizer')->withCount('registrations')->orderByDesc('event_date')->get();
        $pdf = Pdf::loadView('reports.events-pdf', compact('events'))->setPaper('a4', 'landscape');
        return $pdf->download('nu-clark-events-report.pdf');
    }

    public function notifications()
    {
        $users = User::select('id', 'name', 'email', 'role')->get();
        return view('admin.notifications', compact('users'));
    }

    public function sendNotification(Request $request)
    {
        $v = $request->validate([
            'role'    => 'nullable|in:admin,organizer,student,adviser,department_head,dean,executive_director,student_development,program_chair,student_department',
            'title'   => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $query = User::query();
        if ($v['role']) $query->where('role', $v['role']);
        $userIds = $query->pluck('id');

        $notifs = $userIds->map(fn($uid) => [
            'user_id'    => $uid,
            'type'       => 'admin_broadcast',
            'title'      => $v['title'],
            'message'    => $v['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        AppNotification::insert($notifs);

        return back()->with('success', "Notification sent to {$userIds->count()} users.");
    }

    // ─── Venue Management (Admin) ────────────────
    public function venues(Request $request)
    {
        $query = VenueReservation::with(['event', 'reservedBy'])->orderByDesc('created_at');
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        $reservations = $query->paginate(20);
        $total    = VenueReservation::count();
        $pending  = VenueReservation::where('status', 'like', 'pending_%')->count();
        $approved = VenueReservation::where('status', 'approved')->count();
        $rejected = VenueReservation::where('status', 'rejected')->count();
        return view('admin.venues', compact('reservations','total','pending','approved','rejected'));
    }

    public function updateVenueStatus(Request $request, $id)
    {
        $res = VenueReservation::findOrFail($id);
        $v = $request->validate(['status' => 'required|in:approved,rejected', 'notes' => 'nullable|string']);
        $res->update(['status' => $v['status'], 'notes' => $v['notes'] ?? null]);
        $msg = $v['status'] === 'approved' ? 'approved ✓' : 'rejected ✗';
        $safeNotes = strip_tags($v['notes'] ?? '');
        // Notify organizer
        AppNotification::create([
            'user_id' => $res->reserved_by,
            'type'    => 'venue_reservation',
            'title'   => "Venue Reservation {$msg}: {$res->venue_name}",
            'message' => $safeNotes ? "Your reservation for {$res->venue_name} on {$res->reserved_date->format('M d')} has been {$v['status']}. Notes: {$safeNotes}" : "Your reservation for {$res->venue_name} on {$res->reserved_date->format('M d')} has been {$v['status']}.",
        ]);
        return back()->with('success', "Venue reservation {$msg}!");
    }

    public function deleteVenue($id)
    {
        VenueReservation::findOrFail($id)->delete();
        return back()->with('success', 'Venue reservation deleted.');
    }

    // ─── File Hunting Module ─────────────────────

    public function fileHunting()
    {
        $signatories = FileHuntingSignatory::orderBy('step_order')->get();
        $availableRoles = [
            'student_development' => 'Student Development Officer',
            'program_chair'       => 'Program Chair',
            'dean'                => 'College Dean',
            'executive_director'  => 'Executive Director',
            'adviser'             => 'Adviser',
            'department_head'     => 'Department Head',
        ];
        return view('admin.file-hunting', compact('signatories', 'availableRoles'));
    }

    public function saveSignatories(Request $request)
    {
        $request->validate([
            'signatories'                  => 'required|array|min:1',
            'signatories.*.role'           => 'required|string',
            'signatories.*.position_label' => 'required|string|max:100',
            'signatories.*.is_active'      => 'sometimes|boolean',
        ]);

        // Wipe existing and re-insert in order
        FileHuntingSignatory::truncate();

        foreach ($request->signatories as $i => $sig) {
            FileHuntingSignatory::create([
                'step_order'     => $i + 1,
                'role'           => $sig['role'],
                'position_label' => $sig['position_label'],
                'is_active'      => isset($sig['is_active']) ? 1 : 0,
            ]);
        }

        return back()->with('success', 'Signing chain updated successfully.');
    }
}
