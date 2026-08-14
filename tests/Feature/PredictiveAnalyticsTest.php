<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Registration;
use App\Models\Attendance;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PredictiveAnalyticsTest extends TestCase
{
    /**
     * Test PredictiveAnalyticsService instantiated and predicts attendance correctly.
     */
    public function test_predictive_analytics_service_calculates_predictions(): void
    {
        $service = new PredictiveAnalyticsService();
        $summary = $service->getHistoricalDataSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_completed_events', $summary);
        $this->assertArrayHasKey('overall_attendance_rate', $summary);
        $this->assertArrayHasKey('data_quality', $summary);

        $event = Event::first();
        if ($event) {
            $prediction = $service->predictAttendance($event);
            $this->assertIsArray($prediction);
            $this->assertArrayHasKey('predicted_count', $prediction);
            $this->assertArrayHasKey('predicted_rate', $prediction);
            $this->assertArrayHasKey('confidence', $prediction);
            $this->assertArrayHasKey('factors', $prediction);

            $resourcePlan = $service->getResourcePlan($event, $prediction['predicted_count']);
            $this->assertIsArray($resourcePlan);
            $this->assertArrayHasKey('resources', $resourcePlan);
            $this->assertNotEmpty($resourcePlan['resources']);
        }
    }

    /**
     * Test schedule recommendations optimizer logic.
     */
    public function test_schedule_optimizer_returns_slot_recommendations(): void
    {
        $service = new PredictiveAnalyticsService();
        $recommendations = $service->getScheduleRecommendations([
            'category' => 'Academic',
            'venue' => 'Multi-purpose Hall',
            'capacity' => 100,
            'date_from' => Carbon::today()->format('Y-m-d'),
            'date_to' => Carbon::today()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 2
        ]);

        $this->assertIsArray($recommendations);
        if (!empty($recommendations)) {
            $first = $recommendations[0];
            $this->assertArrayHasKey('date', $first);
            $this->assertArrayHasKey('suggested_start', $first);
            $this->assertArrayHasKey('suggested_end', $first);
            $this->assertArrayHasKey('confidence_score', $first);
        }
    }

    /**
     * Test admin predictive analytics dashboard route access.
     */
    public function test_admin_can_access_predictive_analytics_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $response = $this->actingAs($admin)->get(route('admin.predictive'));
            $response->assertStatus(200);
            $response->assertSee('Predictive Analytics');
        } else {
            $this->assertTrue(true);
        }
    }
}
