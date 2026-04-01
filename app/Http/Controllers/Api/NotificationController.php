<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = AppNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = AppNotification::where('user_id', $request->user()->id)
            ->unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $notification = AppNotification::where('user_id', $request->user()->id)
            ->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function send(Request $request)
    {
        $request->validate([
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'role'       => 'nullable|in:admin,organizer,student',
            'type'       => 'required|string',
            'title'      => 'required|string|max:255',
            'message'    => 'required|string',
        ]);

        $query = User::query();
        if ($request->user_ids) {
            $query->whereIn('id', $request->user_ids);
        } elseif ($request->role) {
            $query->where('role', $request->role);
        }

        $userIds = $query->pluck('id');

        $notifications = $userIds->map(fn($uid) => [
            'user_id'    => $uid,
            'type'       => $request->type,
            'title'      => $request->title,
            'message'    => $request->message,
            'data'       => json_encode($request->data ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        AppNotification::insert($notifications);

        return response()->json(['message' => "Notification sent to {$userIds->count()} users."]);
    }
}
