<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParticipationAnalyticsService
{
    public function categoryPopularity(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Event::select('category', DB::raw('count(id) as events_count'), DB::raw('sum(capacity) as total_capacity'))
            ->whereNotNull('category');
            
        if ($dateFrom) $query->whereDate('event_date', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('event_date', '<=', $dateTo);
        
        $categories = $query->groupBy('category')->get();
        
        $results = [];
        foreach ($categories as $cat) {
            $regQuery = Registration::join('events', 'registrations.event_id', '=', 'events.id')
                ->where('events.category', $cat->category);
            if ($dateFrom) $regQuery->whereDate('events.event_date', '>=', $dateFrom);
            if ($dateTo) $regQuery->whereDate('events.event_date', '<=', $dateTo);
            $registrations = $regQuery->count();
            
            $attQuery = Attendance::join('registrations', 'attendances.registration_id', '=', 'registrations.id')
                ->join('events', 'registrations.event_id', '=', 'events.id')
                ->where('events.category', $cat->category);
            if ($dateFrom) $attQuery->whereDate('events.event_date', '>=', $dateFrom);
            if ($dateTo) $attQuery->whereDate('events.event_date', '<=', $dateTo);
            $attendances = $attQuery->count();
            
            $rate = $registrations > 0 ? round(($attendances / $registrations) * 100, 1) : 0.0;
            
            $results[] = [
                'category' => $cat->category,
                'registrations' => $registrations,
                'attendances' => $attendances,
                'rate' => $rate
            ];
        }
        
        usort($results, fn($a, $b) => $b['registrations'] <=> $a['registrations']);
        
        return $results;
    }

    public function participationTrends(int $months = 12): array
    {
        $results = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStr = $date->format('Y-m');
            $label = $date->format('M Y');
            
            $registrations = Registration::join('events', 'registrations.event_id', '=', 'events.id')
                ->whereYear('events.event_date', $date->year)
                ->whereMonth('events.event_date', $date->month)
                ->count();
                
            $attendances = Attendance::join('registrations', 'attendances.registration_id', '=', 'registrations.id')
                ->join('events', 'registrations.event_id', '=', 'events.id')
                ->whereYear('events.event_date', $date->year)
                ->whereMonth('events.event_date', $date->month)
                ->count();
                
            $results[] = [
                'month' => $monthStr,
                'label' => $label,
                'registrations' => $registrations,
                'attendances' => $attendances
            ];
        }
        return $results;
    }

    public function courseVsCategory(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Registration::join('users', 'registrations.user_id', '=', 'users.id')
            ->join('courses', 'users.course_id', '=', 'courses.id')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->select('courses.code as course', 'events.category', 
                     DB::raw('count(registrations.id) as registrations'));
                     
        if ($dateFrom) $query->whereDate('events.event_date', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('events.event_date', '<=', $dateTo);
        
        $data = $query->groupBy('courses.code', 'events.category')->get();
        
        $results = [];
        foreach ($data as $item) {
            if (!$item->category) continue;
            
            $attQuery = Attendance::join('registrations', 'attendances.registration_id', '=', 'registrations.id')
                ->join('users', 'registrations.user_id', '=', 'users.id')
                ->join('courses', 'users.course_id', '=', 'courses.id')
                ->join('events', 'registrations.event_id', '=', 'events.id')
                ->where('courses.code', $item->course)
                ->where('events.category', $item->category);
                
            if ($dateFrom) $attQuery->whereDate('events.event_date', '>=', $dateFrom);
            if ($dateTo) $attQuery->whereDate('events.event_date', '<=', $dateTo);
            
            $attendances = $attQuery->count();
            
            $results[] = [
                'course' => $item->course,
                'category' => $item->category,
                'registrations' => $item->registrations,
                'attendances' => $attendances
            ];
        }
        
        return $results;
    }

    public function registrationVsAttendance(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Event::select('id', 'title', 'capacity', 'event_date');
        
        if ($dateFrom) $query->whereDate('event_date', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('event_date', '<=', $dateTo);
        
        $events = $query->get();
        
        $results = [];
        foreach ($events as $event) {
            $registered = Registration::where('event_id', $event->id)->count();
            $attended = Attendance::join('registrations', 'attendances.registration_id', '=', 'registrations.id')
                ->where('registrations.event_id', $event->id)
                ->count();
                
            $rate = $registered > 0 ? round(($attended / $registered) * 100, 1) : 0.0;
            
            if ($registered > 0) {
                $results[] = [
                    'event_id' => $event->id,
                    'title' => $event->title,
                    'registered' => $registered,
                    'attended' => $attended,
                    'rate' => $rate
                ];
            }
        }
        
        usort($results, fn($a, $b) => $b['registered'] <=> $a['registered']);
        return $results;
    }

    public function eventEngagementScores(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Event::select('id', 'title', 'capacity', 'event_date');
        
        if ($dateFrom) $query->whereDate('event_date', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('event_date', '<=', $dateTo);
        
        $events = $query->get();
        
        $results = [];
        foreach ($events as $event) {
            $capacity = $event->capacity ?: 1;
            $registered = Registration::where('event_id', $event->id)->count();
            $attended = Attendance::join('registrations', 'attendances.registration_id', '=', 'registrations.id')
                ->where('registrations.event_id', $event->id)
                ->count();
                
            $fill_rate = min($registered / $capacity, 1.0);
            $attendance_rate = $registered > 0 ? ($attended / $registered) : 0.0;
            
            $score = round(($fill_rate * 0.4 + $attendance_rate * 0.6) * 100, 1);
            
            if ($registered > 0) {
                $results[] = [
                    'event_id'         => $event->id,
                    'title'            => $event->title,
                    'score'            => $score,
                    'engagement_score' => $score,
                    'fill_rate'        => round($fill_rate * 100, 1),
                    'attendance_rate'  => round($attendance_rate * 100, 1)
                ];
            }
        }
        
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        return $results;
    }

    public function peakParticipationTimes(): array
    {
        $events = Event::whereNotNull('start_time')->whereNotNull('event_date')->get();
        $times = [];
        
        foreach ($events as $event) {
            $registered = Registration::where('event_id', $event->id)->count();
            if ($registered > 0 && $event->event_date) {
                $day = Carbon::parse($event->event_date)->format('D'); // 'Mon', 'Tue', etc.
                $hour = (int) Carbon::parse($event->start_time)->format('G'); 
                
                $key = $day . '_' . $hour;
                if (!isset($times[$key])) {
                    $times[$key] = [
                        'day' => $day,
                        'hour' => $hour,
                        'count' => 0
                    ];
                }
                $times[$key]['count'] += $registered;
            }
        }
        
        return array_values($times);
    }

    public function studentProfile(int $userId): array
    {
        $registrations = Registration::with('event')->where('user_id', $userId)->get();
        
        $eventsCount = $registrations->count();
        $attendedCount = Attendance::join('registrations', 'attendances.registration_id', '=', 'registrations.id')
            ->where('registrations.user_id', $userId)
            ->count();
            
        $categories = [];
        foreach ($registrations as $reg) {
            if ($reg->event) {
                $cat = $reg->event->category;
                if ($cat) {
                    if (!isset($categories[$cat])) {
                        $categories[$cat] = 0;
                    }
                    $categories[$cat]++;
                }
            }
        }
        
        arsort($categories);
        $keys = array_keys($categories);
        $mostCategory = count($keys) > 0 ? $keys[0] : null;
        $secondary = count($keys) > 1 ? $keys[1] : null;
        
        $attendanceRate = $eventsCount > 0 ? round(($attendedCount / $eventsCount) * 100, 1) : 0.0;
        
        return [
            'most_category' => $mostCategory,
            'secondary' => $secondary,
            'events_count' => $eventsCount,
            'attendance_rate' => $attendanceRate,
            'categories' => $categories
        ];
    }

    public function underparticipatedEvents(): array
    {
        $events = Event::where('status', '!=', 'cancelled')->get();
        $results = [];
        
        foreach ($events as $event) {
            $capacity = $event->capacity ?: 1;
            $registered = Registration::where('event_id', $event->id)->count();
            $attended = Attendance::join('registrations', 'attendances.registration_id', '=', 'registrations.id')
                ->where('registrations.event_id', $event->id)
                ->count();
                
            $fillRate = ($registered / $capacity) * 100;
            $attendanceRate = $registered > 0 ? ($attended / $registered) * 100 : 0.0;
            
            if ($registered > 0 && ($fillRate < 25 || $attendanceRate < 40)) {
                $results[] = [
                    'event_id'        => $event->id,
                    'title'           => $event->title,
                    'event_date'      => $event->event_date,
                    'fill_rate'       => round($fillRate, 1),
                    'attendance_rate' => round($attendanceRate, 1),
                    'registered'      => $registered,
                    'attended'        => $attended,
                    'capacity'        => $capacity,
                    'issue'           => $fillRate < 25 ? 'Low Registration' : 'Low Attendance'
                ];
            }
        }
        
        return $results;
    }

    public function overviewStats(): array
    {
        $totalEvents = Event::count();
        $totalRegistrations = Registration::count();
        $totalAttendances = Attendance::count();
        $activeStudents = Registration::distinct('user_id')->count('user_id');
        
        $overallAttendanceRate = $totalRegistrations > 0 ? round(($totalAttendances / $totalRegistrations) * 100, 1) : 0.0;
        $avgRegistrationsPerEvent = $totalEvents > 0 ? round($totalRegistrations / $totalEvents, 1) : 0.0;
        $mostPopularCategory = Event::whereNotNull('category')
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->value('category') ?? 'N/A';
        
        return [
            'total_events'                => $totalEvents,
            'total_registrations'         => $totalRegistrations,
            'total_attendances'           => $totalAttendances,
            'overall_attendance_rate'     => $overallAttendanceRate,
            'avg_registrations_per_event' => $avgRegistrationsPerEvent,
            'most_popular_category'       => $mostPopularCategory,
            'active_students'             => $activeStudents
        ];
    }
}
