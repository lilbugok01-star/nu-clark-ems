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
use App\Exports\EventReportExport;
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
        if ($request->role)   $query->where('role', $request->role);
        if ($request->search) $query->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"));
        $users   = $query->paginate(20);
        $courses = Course::where('is_active', true)->get();
        $sections= Section::where('is_active', true)->with('course')->get();
        return view('admin.users', compact('users', 'courses', 'sections'));
    }

    public function storeUser(Request $request)
    {
        $rules = [
            'name'       => 'required|string',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:8',
            'role'       => 'required|in:admin,organizer,student',
            'student_id' => 'nullable|unique:users,student_id',
            'course_id'  => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:sections,id',
        ];

        if ($request->role === 'student') {
            $rules['student_id'] = 'required|string|unique:users,student_id';
            $rules['course_id']  = 'required|exists:courses,id';
            $rules['section_id'] = 'required|exists:sections,id';
        }

        $v = $request->validate($rules);
        User::create([...$v, 'password' => Hash::make($v['password'])]);
        return back()->with('success', 'User created successfully!');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $rules = [
            'name'      => 'sometimes|string',
            'role'      => 'sometimes|in:admin,organizer,student',
            'is_active' => 'sometimes|boolean',
            'student_id'=> 'sometimes|nullable|string|unique:users,student_id,' . $id,
            'course_id' => 'sometimes|nullable|exists:courses,id',
            'section_id'=> 'sometimes|nullable|exists:sections,id',
        ];

        $role = $request->input('role', $user->role);
        if ($role === 'student') {
            $rules['student_id'] = 'required|string|unique:users,student_id,' . $id;
            $rules['course_id']  = 'required|exists:courses,id';
            $rules['section_id'] = 'required|exists:sections,id';
        } else {
            // Nullify these if changing from student to admin/organizer
            $request->merge(['student_id' => null, 'course_id' => null, 'section_id' => null]);
            $rules['student_id'] = 'nullable';
            $rules['course_id']  = 'nullable';
            $rules['section_id'] = 'nullable';
        }

        $v = $request->validate($rules);
        $user->update($v);
        return back()->with('success', 'User updated!');
    }

    public function deleteUser($id)
    {
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
            'role'    => 'nullable|in:admin,organizer,student',
            'title'   => 'required|string',
            'message' => 'required|string',
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
        $pending  = VenueReservation::where('status', 'pending')->count();
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
        // Notify organizer
        AppNotification::create([
            'user_id' => $res->reserved_by,
            'type'    => 'venue_reservation',
            'title'   => "Venue Reservation {$msg}: {$res->venue_name}",
            'message' => $v['notes'] ?? "Your reservation for {$res->venue_name} on {$res->reserved_date->format('M d')} has been {$v['status']}.",
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
