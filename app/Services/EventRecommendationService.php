<?php

namespace App\Services;

use App\Models\User;
use App\Models\Event;
use App\Models\Registration;

class EventRecommendationService
{
    /**
     * Map course codes/names to target keywords & categories.
     */
    protected static array $courseMap = [
        'BSIT' => ['IT', 'Hackathon', 'Coding', 'Technology', 'Software', 'AI', 'Cybersecurity', 'Web'],
        'BSCS' => ['IT', 'Hackathon', 'Coding', 'Computer', 'Algorithms', 'AI'],
        'BSBA' => ['Business', 'Seminar', 'Marketing', 'Finance', 'Entrepreneurship', 'Management'],
        'BSN'  => ['Nursing', 'Medical', 'Health', 'Mission', 'First Aid', 'Care'],
        'BSCE' => ['Engineering', 'Workshop', 'Civil', 'Construction', 'Structure'],
        'BSEE' => ['Engineering', 'Workshop', 'Electrical', 'Power', 'Circuit'],
        'BSCpE'=> ['Engineering', 'Tech', 'Computer', 'Hardware', 'Robotics'],
    ];

    /**
     * Recommend events tailored to the student's academic course, interests, and participation history.
     */
    public function getRecommendedEvents(User $user, int $limit = 6)
    {
        $registeredEventIds = Registration::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->pluck('event_id');

        $query = Event::with('organizer')
            ->where('status', 'published')
            ->where('event_date', '>=', now()->toDateString())
            ->whereNotIn('id', $registeredEventIds);

        $events = $query->get();

        $courseCode = strtoupper($user->course->code ?? '');
        $keywords = self::$courseMap[$courseCode] ?? [];

        // If no direct course mapping found, try matching course code letters
        if (empty($keywords) && !empty($courseCode)) {
            if (str_contains($courseCode, 'IT') || str_contains($courseCode, 'CS')) {
                $keywords = self::$courseMap['BSIT'];
            } elseif (str_contains($courseCode, 'BA') || str_contains($courseCode, 'BUS')) {
                $keywords = self::$courseMap['BSBA'];
            } elseif (str_contains($courseCode, 'NUR') || str_contains($courseCode, 'N')) {
                $keywords = self::$courseMap['BSN'];
            } elseif (str_contains($courseCode, 'ENG') || str_contains($courseCode, 'CE') || str_contains($courseCode, 'EE')) {
                $keywords = self::$courseMap['BSCE'];
            }
        }

        // Score each event
        $scoredEvents = $events->map(function ($event) use ($keywords) {
            $score = 0;

            // Category match
            if ($event->category) {
                foreach ($keywords as $kw) {
                    if (stripos($event->category, $kw) !== false) {
                        $score += 10;
                    }
                }
            }

            // Title / Description / Tags match
            $fullText = implode(' ', [$event->title, $event->description, $event->tags ?? '']);
            foreach ($keywords as $kw) {
                if (stripos($fullText, $kw) !== false) {
                    $score += 5;
                }
            }

            // Featured bonus
            if ($event->is_featured) {
                $score += 3;
            }

            // Date proximity bonus — events happening sooner score higher
            $daysUntil = max(0, (int) now()->diffInDays($event->event_date, false));
            if ($daysUntil <= 3) {
                $score += 8;
            } elseif ($daysUntil <= 7) {
                $score += 5;
            } elseif ($daysUntil <= 14) {
                $score += 3;
            }

            // Capacity availability bonus — events with open spots are preferred
            $registeredCount = $event->registrations()->where('status', '!=', 'cancelled')->count();
            $openSpots = max(0, ($event->capacity ?? 100) - $registeredCount);
            if ($openSpots > 0 && $openSpots <= 10) {
                $score += 4; // Almost full — urgency boost
            } elseif ($openSpots > 10) {
                $score += 1;
            }

            $event->recommendation_score = $score;
            return $event;
        });

        // Sort by recommendation score DESC, then event_date ASC
        return $scoredEvents
            ->sort(function ($a, $b) {
                if ($a->recommendation_score === $b->recommendation_score) {
                    return strtotime($a->event_date) <=> strtotime($b->event_date);
                }
                return $b->recommendation_score <=> $a->recommendation_score;
            })
            ->take($limit)
            ->values();
    }
}
