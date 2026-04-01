<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Attendance;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // All queries are done inline in welcome.blade.php using @php
        return view('welcome');
    }

    public function events(Request $request)
    {
        $query = Event::with('organizer')->published();

        if ($request->search)   $query->search($request->search);
        if ($request->category) $query->where('category', $request->category);
        if ($request->date)     $query->whereDate('event_date', $request->date);

        $events     = $query->orderBy('event_date')->paginate(12);
        $categories = Event::published()->whereNotNull('category')->distinct()->pluck('category');

        return view('home.events', compact('events', 'categories'));
    }

    public function showEvent($id)
    {
        $event = Event::with('organizer')->findOrFail($id);
        $event->registered_count = $event->registeredCount();
        $event->attended_count   = $event->attendedCount();
        $event->is_full          = $event->isFull();

        $isRegistered = false;
        if (Auth::check()) {
            $isRegistered = Registration::where('user_id', Auth::id())
                ->where('event_id', $id)
                ->where('status', '!=', 'cancelled')->exists();
        }

        return view('home.event-detail', compact('event', 'isRegistered'));
    }
}
