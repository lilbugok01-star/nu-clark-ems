<?php

namespace App\Services;

use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use Carbon\Carbon;

class EventRecommendationService
{
    /**
     * Map course codes to target keywords & categories.
     */
    protected static array $courseMap = [
        'BSIT'     => ['IT', 'Hackathon', 'Coding', 'Technology', 'Software', 'AI', 'Cybersecurity', 'Web', 'Mobile'],
        'BSIT-MWA' => ['IT', 'Hackathon', 'Coding', 'Technology', 'Software', 'AI', 'Cybersecurity', 'Web', 'Mobile', 'App'],
        'BSCS'     => ['IT', 'Hackathon', 'Coding', 'Computer', 'Algorithms', 'AI', 'Data', 'Software'],
        'BSA'      => ['Accountancy', 'Audit', 'Tax', 'Finance', 'Accounting', 'Business', 'Commerce', 'Financial'],
        'BSMA'     => ['Management', 'Accounting', 'Finance', 'Audit', 'Business', 'Commerce', 'Financial'],
        'BSTM'     => ['Tourism', 'Hospitality', 'Travel', 'Expo', 'Culture', 'Hotel', 'Event'],
        'BSP'      => ['Psychology', 'Mental Health', 'Wellness', 'Behavior', 'Counseling', 'Awareness', 'Talk'],
        'BACOMM'   => ['Communication', 'Journalism', 'Media', 'Film', 'Arts', 'Public Speaking', 'Workshop'],
        'BAPOLSCI' => ['Political', 'Governance', 'Leadership', 'Law', 'Congress', 'Debate', 'Social'],
        'BSCPE'    => ['Engineering', 'Computer', 'Hardware', 'Robotics', 'IoT', 'Circuit', 'Tech'],
        'BSCE'     => ['Engineering', 'Civil', 'Construction', 'Structure', 'Workshop', 'Building'],
        'BSEE'     => ['Engineering', 'Electrical', 'Power', 'Circuit', 'Energy', 'Workshop'],
        'BSBA'     => ['Business', 'Seminar', 'Marketing', 'Finance', 'Entrepreneurship', 'Management', 'Pitching'],
        'BSBA-MM'  => ['Business', 'Marketing', 'Seminar', 'Finance', 'Entrepreneurship', 'Management', 'Pitching', 'Sales'],
        'BSARCH'   => ['Architecture', 'Design', 'Exhibit', 'Drafting', 'Building', 'Arts', 'Model'],
    ];

    /**
     * Recommend events tailored to the student's academic course, behavioral interest history, and schedule availability.
     */
    public function getRecommendedEvents(User $user, int $limit = 6)
    {
        // 1. Get user's active registrations (to exclude already registered events & detect schedule conflicts)
        $userRegistrations = Registration::with('event')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        $registeredEventIds = $userRegistrations->pluck('event_id')->toArray();

        // 2. Build Behavioral Interest Profile based on past registered/attended categories
        $pastCategories = [];
        foreach ($userRegistrations as $reg) {
            if ($reg->event && $reg->event->category) {
                $pastCategories[] = $reg->event->category;
            }
        }
        $categoryCounts = array_count_values($pastCategories);

        // 3. Query upcoming published events (with N+1 query optimization via withCount)
        $query = Event::with('organizer')
            ->withCount(['registrations as reg_count' => fn($q) => $q->where('status', '!=', 'cancelled')])
            ->where('status', 'published')
            ->where('event_date', '>=', now()->toDateString())
            ->whereNotIn('id', $registeredEventIds);

        $events = $query->get();

        // 4. Resolve Course Keywords
        $courseCode = strtoupper($user->course?->code ?? '');
        $keywords = self::$courseMap[$courseCode] ?? [];

        // Fallback matching if exact code not found in map
        if (empty($keywords) && !empty($courseCode)) {
            if (str_contains($courseCode, 'IT') || str_contains($courseCode, 'CS')) {
                $keywords = self::$courseMap['BSIT'];
            } elseif (str_contains($courseCode, 'ACC') || str_contains($courseCode, 'ACT') || str_contains($courseCode, 'A')) {
                $keywords = self::$courseMap['BSA'];
            } elseif (str_contains($courseCode, 'BA') || str_contains($courseCode, 'BUS') || str_contains($courseCode, 'MKT')) {
                $keywords = self::$courseMap['BSBA'];
            } elseif (str_contains($courseCode, 'TOUR') || str_contains($courseCode, 'TM') || str_contains($courseCode, 'HM')) {
                $keywords = self::$courseMap['BSTM'];
            } elseif (str_contains($courseCode, 'PSY')) {
                $keywords = self::$courseMap['BSP'];
            } elseif (str_contains($courseCode, 'ENG') || str_contains($courseCode, 'CE') || str_contains($courseCode, 'CPE')) {
                $keywords = self::$courseMap['BSCE'];
            } elseif (str_contains($courseCode, 'ARCH')) {
                $keywords = self::$courseMap['BSARCH'];
            }
        }

        // 5. Intelligent Multi-Factor Scoring Engine
        $scoredEvents = $events->filter(function ($event) use ($userRegistrations) {
            // Schedule Conflict Filter: Skip events that directly overlap in date & time with an event the user registered for
            foreach ($userRegistrations as $userReg) {
                if (!$userReg->event) continue;
                if ($userReg->event->event_date->toDateString() === $event->event_date->toDateString()) {
                    $uStart = Carbon::parse($userReg->event->start_time);
                    $uEnd   = Carbon::parse($userReg->event->end_time);
                    $eStart = Carbon::parse($event->start_time);
                    $eEnd   = Carbon::parse($event->end_time);

                    if ($eStart->lt($uEnd) && $eEnd->gt($uStart)) {
                        return false; // Direct time conflict — exclude from recommendations
                    }
                }
            }
            return true;
        })->map(function ($event) use ($keywords, $courseCode, $categoryCounts) {
            $score = 0;
            $primaryReason = null;

            // Factor A: Academic Curriculum Match (Weight: 15)
            $curriculumMatched = false;
            if ($event->category) {
                foreach ($keywords as $kw) {
                    if (stripos($event->category, $kw) !== false) {
                        $score += 15;
                        $curriculumMatched = true;
                        $primaryReason = "🎯 Matches your " . ($courseCode ?: 'academic') . " program";
                        break;
                    }
                }
            }

            // Title / Description / Tags keyword match (Weight: 8)
            $fullText = implode(' ', [$event->title, $event->description, $event->tags ?? '']);
            foreach ($keywords as $kw) {
                if (stripos($fullText, $kw) !== false) {
                    $score += 8;
                    if (!$primaryReason) {
                        $primaryReason = "💡 Related to " . $kw;
                    }
                    break;
                }
            }

            // Factor B: Behavioral Preference (Past Attendance) (Weight: 10)
            if ($event->category && isset($categoryCounts[$event->category])) {
                $bonus = min(20, $categoryCounts[$event->category] * 10);
                $score += $bonus;
                if (!$primaryReason || !$curriculumMatched) {
                    $primaryReason = "⭐ Based on your interest in " . $event->category;
                }
            }

            // Factor C: Featured Event Bonus (Weight: 5)
            if ($event->is_featured) {
                $score += 5;
                if (!$primaryReason) {
                    $primaryReason = "🌟 Featured Campus Event";
                }
            }

            // Factor D: Date Proximity Urgency (Weight: 3 to 10)
            $daysUntil = max(0, (int) now()->diffInDays($event->event_date, false));
            if ($daysUntil <= 3) {
                $score += 10;
                if (!$primaryReason) $primaryReason = "⚡ Happening Soon";
            } elseif ($daysUntil <= 7) {
                $score += 6;
            } elseif ($daysUntil <= 14) {
                $score += 3;
            }

            // Factor E: Seat Availability & Urgency (Weight: 1 to 8)
            $registeredCount = $event->reg_count ?? 0;
            $capacity = $event->capacity ?? 100;
            $openSpots = max(0, $capacity - $registeredCount);

            if ($openSpots > 0 && $openSpots <= 15) {
                $score += 8; // Urgency boost — almost sold out
                if (!$primaryReason) $primaryReason = "🔥 Almost Full (Only {$openSpots} spots left)";
            } elseif ($openSpots > 15) {
                $score += 2;
            } else {
                $score -= 20; // Full event — heavy penalty
            }

            $event->recommendation_score = $score;
            $event->recommendation_reason = $primaryReason ?? "📅 Recommended Campus Event";

            return $event;
        });

        // 6. Sort by recommendation score DESC, then event_date ASC
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
