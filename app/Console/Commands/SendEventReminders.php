<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\Registration;
use App\Models\AppNotification;
use Carbon\Carbon;

class SendEventReminders extends Command
{
    protected $signature   = 'events:send-reminders';
    protected $description = 'Send event reminders to registered students 24 hours before the event';

    public function handle(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $events = Event::where('event_date', $tomorrow)
            ->where('status', 'published')
            ->get();

        $this->info("Found {$events->count()} event(s) happening tomorrow.");

        foreach ($events as $event) {
            $registrations = Registration::with('user')
                ->where('event_id', $event->id)
                ->where('status', 'confirmed')
                ->get();

            $notifications = $registrations->map(fn($reg) => [
                'user_id'    => $reg->user_id,
                'type'       => 'event_reminder',
                'title'      => '⏰ Event Tomorrow: ' . $event->title,
                'message'    => "Don't forget! You're registered for {$event->title} tomorrow ({$event->event_date->format('M d, Y')}) at {$event->venue}. Bring your QR code for check-in.",
                'data'       => json_encode(['event_id' => $event->id]),
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            AppNotification::insert($notifications);

            $this->info("Sent {$registrations->count()} reminder(s) for: {$event->title}");
        }

        $this->info('Event reminders sent successfully.');
    }
}
