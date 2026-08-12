<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ParticipationAnalyticsService;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                if (!Auth::check() || Auth::user()->role !== 'admin') {
                    abort(403);
                }
                return $next($request);
            }),
        ];
    }
    
    public function dashboard(Request $request, ParticipationAnalyticsService $analytics)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        $overview = $analytics->overviewStats();
        $categoryPopularity = $analytics->categoryPopularity($dateFrom, $dateTo);
        $trends = $analytics->participationTrends();
        $courseVsCategory = $analytics->courseVsCategory($dateFrom, $dateTo);
        $regVsAttendance = $analytics->registrationVsAttendance($dateFrom, $dateTo);
        $engagementScores = $analytics->eventEngagementScores($dateFrom, $dateTo);
        $peakTimes = $analytics->peakParticipationTimes();
        $underparticipated = $analytics->underparticipatedEvents();
        
        return view('admin.analytics', compact(
            'overview', 'categoryPopularity', 'trends', 'courseVsCategory',
            'regVsAttendance', 'engagementScores', 'peakTimes', 'underparticipated',
            'dateFrom', 'dateTo'
        ));
    }
    
    public function studentProfile(Request $request, $id, ParticipationAnalyticsService $analytics)
    {
        $student = User::with('course', 'section')->findOrFail($id);
        $profile = $analytics->studentProfile($id);
        return view('admin.student-analytics', compact('student', 'profile'));
    }

    public function exportPdf(Request $request, ParticipationAnalyticsService $analytics)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $overview = $analytics->overviewStats();
        $categoryPopularity = $analytics->categoryPopularity($dateFrom, $dateTo);
        $courseVsCategory = $analytics->courseVsCategory($dateFrom, $dateTo);
        $engagementScores = $analytics->eventEngagementScores($dateFrom, $dateTo);

        User::log('export_analytics_pdf', null, null, ['format' => 'pdf', 'date_from' => $dateFrom, 'date_to' => $dateTo]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.analytics-pdf', compact(
            'overview', 'categoryPopularity', 'courseVsCategory', 'engagementScores', 'dateFrom', 'dateTo'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('participation-pattern-analytics.pdf');
    }
}
