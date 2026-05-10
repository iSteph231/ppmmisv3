<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orWhereNull('user_id')
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
        
        $unreadCount = Notification::where('user_id', Auth::id())
            ->orWhereNull('user_id')
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
    
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->orWhereNull('user_id')
            ->findOrFail($id);
        
        $notification->update(['is_read' => true]);
        
        return response()->json(['success' => true]);
    }
    
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->orWhereNull('user_id')
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json(['success' => true]);
    }
}