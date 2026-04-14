<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('organizer')->published();

        if ($request->search)   $query->search($request->search);
        if ($request->category) $query->where('category', $request->category);
        if ($request->date)     $query->whereDate('event_date', $request->date);
        if ($request->venue) {
            $venue = str_replace(['%', '_'], ['\\%', '\\_'], $request->venue);
            $query->where('venue', 'like', "%{$venue}%");
        }

        $query->orderBy('event_date');

        return response()->json($query->paginate(12));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue'       => 'required|string',
            'event_date'  => 'required|date|after_or_equal:today',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'capacity'    => 'required|integer|min:1',
            'category'    => 'nullable|string',
            'tags'        => 'nullable|string',
            'is_featured' => 'boolean',
            'poster'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $poster_path = null;
        if ($request->hasFile('poster')) {
            $poster_path = $request->file('poster')->store('posters', 's3');
        }

        $event = Event::create([
            ...$validated,
            'organizer_id' => $request->user()->id,
            'poster_path'  => $poster_path,
            'status'       => 'pending_adviser',
        ]);

        // Notifications to students are sent only once the event is fully approved and published.
        // The approval chain handles this transition.

        return response()->json(['status' => 'success', 'event' => $event->load('organizer')], 201);
    }

    public function show($id)
    {
        $event = Event::with('organizer', 'registrations.user')->findOrFail($id);
        $event->registered_count = $event->registeredCount();
        $event->attended_count   = $event->attendedCount();
        $event->is_full          = $event->isFull();
        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        Gate::authorize('update', $event);

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'venue'       => 'sometimes|string',
            'event_date'  => 'sometimes|date',
            'start_time'  => 'sometimes|date_format:H:i',
            'end_time'    => 'sometimes|date_format:H:i',
            'capacity'    => 'sometimes|integer|min:1',
            'status'      => 'sometimes|in:draft,published,cancelled,completed',
            'category'    => 'sometimes|nullable|string',
            'is_featured' => 'sometimes|boolean',
            'poster'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($request->hasFile('poster')) {
            if ($event->poster_path) \Illuminate\Support\Facades\Storage::disk('s3')->delete($event->poster_path);
            $validated['poster_path'] = $request->file('poster')->store('posters', 's3');
        }

        $event->update($validated);

        return response()->json(['status' => 'success', 'event' => $event->fresh('organizer')]);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        Gate::authorize('delete', $event);
        $event->delete();
        return response()->json(['message' => 'Event deleted successfully']);
    }

    public function upcoming()
    {
        $events = Event::with('organizer')->upcoming()->take(10)->get()->map(function ($event) {
            $event->registered_count = $event->registeredCount();
            $event->is_full          = $event->isFull();
            return $event;
        });
        return response()->json($events);
    }

    private function notifyStudents(Event $event): void
    {
        $students = User::where('role', 'student')->pluck('id');
        $notifications = $students->map(fn($uid) => [
            'user_id'    => $uid,
            'type'       => 'new_event',
            'title'      => 'New Event: ' . $event->title,
            'message'    => "A new event has been posted: {$event->title} on {$event->event_date->format('M d, Y')} at {$event->venue}.",
            'data'       => json_encode(['event_id' => $event->id]),
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        AppNotification::insert($notifications);
    }
}
