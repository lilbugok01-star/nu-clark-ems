<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\Registration;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalStudents      = User::where('role', 'student')->count();
        $totalOrganizers    = User::where('role', 'organizer')->count();
        $totalEvents        = Event::count();
        $upcomingEvents     = Event::upcoming()->count();
        $totalRegistrations = Registration::where('status', '!=', 'cancelled')->count();
        $totalAttendance    = Attendance::where('status', 'verified')->count();
        $overallRate        = $totalRegistrations > 0
            ? round(($totalAttendance / $totalRegistrations) * 100, 1)
            : 0;

        return response()->json([
            'total_students'       => $totalStudents,
            'total_organizers'     => $totalOrganizers,
            'total_events'         => $totalEvents,
            'upcoming_events'      => $upcomingEvents,
            'total_registrations'  => $totalRegistrations,
            'total_attendances'    => $totalAttendance,
            'overall_attendance_rate' => $overallRate . '%',
        ]);
    }

    public function charts()
    {
        // Monthly registrations (last 6 months)
        $monthlyRegistrations = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyRegistrations[] = [
                'month' => $month->format('M Y'),
                'count' => Registration::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)->count(),
            ];
        }

        // Event popularity (top 10 events by registrations)
        $eventPopularity = Event::withCount('registrations')
            ->orderByDesc('registrations_count')
            ->take(10)
            ->pluck('registrations_count', 'title');

        // Attendance status breakdown
        $attendanceBreakdown = [
            'verified' => Attendance::where('status', 'verified')->count(),
            'pending'  => Attendance::where('status', 'pending')->count(),
            'rejected' => Attendance::where('status', 'rejected')->count(),
        ];

        // Category breakdown
        $categoryBreakdown = Event::whereNotNull('category')
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        // Attendance heatmap by day of week + hour
        $heatmap = Attendance::selectRaw('DAYOFWEEK(checked_in_at) as day, HOUR(checked_in_at) as hour, COUNT(*) as count')
            ->whereNotNull('checked_in_at')
            ->groupBy('day', 'hour')
            ->get();

        return response()->json([
            'monthly_registrations' => $monthlyRegistrations,
            'event_popularity'      => $eventPopularity,
            'attendance_breakdown'  => $attendanceBreakdown,
            'category_breakdown'    => $categoryBreakdown,
            'attendance_heatmap'    => $heatmap,
        ]);
    }

    public function studentStats(Request $request)
    {
        $user = $request->user();
        $registrations   = Registration::where('user_id', $user->id)->count();
        $attended        = Registration::where('user_id', $user->id)
            ->whereHas('attendance', fn($q) => $q->where('status', 'verified'))->count();
        $upcoming        = Registration::where('user_id', $user->id)
            ->whereHas('event', fn($q) => $q->where('event_date', '>=', now()->toDateString()))
            ->count();

        return response()->json([
            'total_registrations'   => $registrations,
            'events_attended'       => $attended,
            'upcoming_events'       => $upcoming,
            'participation_rate'    => $registrations > 0 ? round(($attended / $registrations) * 100, 1) . '%' : '0%',
        ]);
    }

    public function organizerStats(Request $request)
    {
        $user            = $request->user();
        $myEvents        = Event::where('organizer_id', $user->id)->count();
        $totalAttendees  = Registration::whereHas('event', fn($q) => $q->where('organizer_id', $user->id))->count();
        $verified        = Attendance::whereHas('registration.event', fn($q) => $q->where('organizer_id', $user->id))
            ->where('status', 'verified')->count();

        return response()->json([
            'my_events'             => $myEvents,
            'total_registrations'   => $totalAttendees,
            'verified_attendances'  => $verified,
        ]);
    }
}
