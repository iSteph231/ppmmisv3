<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function browser(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate(['after' => ['nullable', 'integer', 'min:0']]);
        $query = Notification::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('title', ['New Work Request', 'New Facility Request']);

        if (! isset($validated['after'])) {
            return response()->json([
                'user_id' => $request->user()->id,
                'cursor' => (int) $query->max('id'),
                'data' => [],
            ])->header('Cache-Control', 'no-store, private');
        }

        $notifications = $query->where('id', '>', $validated['after'])
            ->orderBy('id')->limit(50)->get();

        return response()->json([
            'user_id' => $request->user()->id,
            'cursor' => (int) ($notifications->last()?->id ?? $validated['after']),
            'data' => $notifications->map(fn (Notification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'url' => route($notification->title === 'New Work Request' ? 'work-requests.index' : 'request-facility.index'),
            ]),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function index(Request $request)
    {
        $notifications = Notification::forUser(Auth::id())
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time' => $notification->created_at->diffForHumans(),
                    'read' => (bool) $notification->is_read,
                ];
            });

        $unreadCount = Notification::forUser(Auth::id())
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::forUser(Auth::id())
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
