<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\VenueReservation;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;

class PredictiveAnalyticsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                if (!in_array(Auth::user()->role, ['admin', 'organizer', 'student_development'])) {
                    abort(403, 'Access denied.');
                }
                return $next($request);
            }),
        ];
    }

    /**
     * Predictive Analytics Dashboard — Overview of all predictions.
     */
    public function dashboard(PredictiveAnalyticsService $service)
    {
        $dataSummary    = $service->getHistoricalDataSummary();
        $predictions    = $service->predictAllUpcoming();
        $categoryRates  = $service->getCategoryAttendanceRates();
        $venueRates     = $service->getVenueAttendanceRates();
        $dayPatterns    = $service->getDayOfWeekPatterns();

        return view('admin.predictive-dashboard', compact(
            'dataSummary', 'predictions', 'categoryRates', 'venueRates', 'dayPatterns'
        ));
    }

    /**
     * Per-event prediction detail page.
     */
    public function eventPrediction($id, PredictiveAnalyticsService $service)
    {
        $event = Event::with('organizer', 'venueReservation')->findOrFail($id);
        $prediction   = $service->predictAttendance($event);
        $resourcePlan = $service->getResourcePlan($event, $prediction['predicted_count']);

        return view('admin.predictive-event', compact('event', 'prediction', 'resourcePlan'));
    }

    /**
     * Scheduling Optimization tool.
     */
    public function scheduleOptimizer(Request $request, PredictiveAnalyticsService $service)
    {
        $recommendations = null;
        $params = null;

        // Get venue names for the dropdown
        $venues = VenueReservation::venueNames();

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $params = $request->only(['category', 'venue', 'capacity', 'date_from', 'date_to', 'duration_hours']);
            $params['capacity'] = (int) ($params['capacity'] ?? 100);
            $params['duration_hours'] = (int) ($params['duration_hours'] ?? 2);

            $recommendations = $service->getScheduleRecommendations($params);
        }

        return view('admin.schedule-optimizer', compact('recommendations', 'params', 'venues'));
    }

    /**
     * Resource Planning page for a specific event.
     */
    public function resourcePlanner($id, PredictiveAnalyticsService $service)
    {
        $event = Event::with('organizer')->findOrFail($id);
        $prediction   = $service->predictAttendance($event);
        $resourcePlan = $service->getResourcePlan($event, $prediction['predicted_count']);

        return view('admin.resource-planner', compact('event', 'resourcePlan'));
    }

    /**
     * Export Predictive Analytics report as PDF.
     */
    public function exportPdf(PredictiveAnalyticsService $service)
    {
        $dataSummary   = $service->getHistoricalDataSummary();
        $predictions   = $service->predictAllUpcoming();
        $categoryRates = $service->getCategoryAttendanceRates();
        $generatedAt   = now()->format('F j, Y g:i A');

        $pdf = Pdf::loadView('reports.predictive-pdf', compact(
            'dataSummary', 'predictions', 'categoryRates', 'generatedAt'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('predictive-analytics-report.pdf');
    }
}
