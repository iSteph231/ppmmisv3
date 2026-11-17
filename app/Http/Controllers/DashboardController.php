<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\WorkRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $notifications = $this->getUserNotifications($user);

        $monthlyData = $this->getMonthlyChartData();

        if ($user->isAdmin()) {
            return $this->adminDashboard($notifications, $monthlyData);
        } else {
            return $this->userDashboard($notifications, $monthlyData);
        }
    }

    private function getUserNotifications($user)
    {
        $notifications = Notification::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereNull('user_id');
        })
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time' => $notification->created_at->diffForHumans(),
                    'read' => (bool) ($notification->is_read ?? false),
                    'created_at' => $notification->created_at,
                ];
            });

        if ($notifications->isEmpty()) {
            return $this->createDefaultNotifications($user);
        }

        return $notifications;
    }

    private function createDefaultNotifications($user)
    {
        $notifications = collect();

        $existingWelcome = Notification::where('user_id', $user->id)
            ->where('title', 'Welcome to PPMMIS')
            ->first();

        if (! $existingWelcome) {
            $welcomeNotif = Notification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to PPMMIS',
                'message' => 'Your account has been created successfully. You can now create maintenance requests.',
                'type' => 'info',
                'is_read' => false,
            ]);

            $notifications->push([
                'id' => $welcomeNotif->id,
                'title' => $welcomeNotif->title,
                'message' => $welcomeNotif->message,
                'time' => $welcomeNotif->created_at->diffForHumans(),
                'read' => false,
                'created_at' => $welcomeNotif->created_at,
            ]);
        }

        $existingSystem = Notification::where('user_id', $user->id)
            ->where('title', 'System Ready')
            ->first();

        if (! $existingSystem) {
            $systemNotif = Notification::create([
                'user_id' => $user->id,
                'title' => 'System Ready',
                'message' => 'The maintenance system is now fully operational. Submit your requests anytime.',
                'type' => 'success',
                'is_read' => false,
            ]);

            $notifications->push([
                'id' => $systemNotif->id,
                'title' => $systemNotif->title,
                'message' => $systemNotif->message,
                'time' => $systemNotif->created_at->diffForHumans(),
                'read' => false,
                'created_at' => $systemNotif->created_at,
            ]);
        }

        return $notifications;
    }

    private function getMonthlyChartData()
    {
        $monthlyRequests = WorkRequest::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlyRequests[$i] ?? 0;
        }

        return $chartData;
    }

    private function adminDashboard($notifications, $monthlyData)
    {
        $userId = Auth::id();

        $totalRequests = WorkRequest::count();
        $pendingRequests = WorkRequest::where('status', 'pending')->count();
        $completedRequests = WorkRequest::where('status', 'completed')->count();
        $activeUsers = User::where('role', 'user')->where('is_active', true)->count();

        $workRequests = WorkRequest::with('user')
            ->latest()
            ->take(10)
            ->get();

        $newRequestIds = Notification::where('user_id', $userId)->unread()
            ->where('related_type', WorkRequest::class)->where('title', 'New Work Request')
            ->whereIn('related_id', $workRequests->modelKeys())->pluck('related_id')->all();

        return view('dashboard.index', compact(
            'totalRequests',
            'pendingRequests',
            'completedRequests',
            'activeUsers',
            'monthlyData',
            'workRequests',
            'newRequestIds',
            'notifications'
        ));
    }

    private function userDashboard($notifications, $monthlyData)
    {
        $userId = Auth::id();

        $myRequests = WorkRequest::where('user_id', $userId)->count();
        $myPendingRequests = WorkRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();
        $myCompletedRequests = WorkRequest::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        $workRequests = WorkRequest::where('user_id', $userId)
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.index', compact(
            'myRequests',
            'myPendingRequests',
            'myCompletedRequests',
            'workRequests',
            'notifications',
            'monthlyData'
        ));
    }

    public function getStats()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if ($user->isAdmin()) {
            $data = [
                'totalRequests' => WorkRequest::count(),
                'pendingRequests' => WorkRequest::where('status', 'pending')->count(),
                'completedRequests' => WorkRequest::where('status', 'completed')->count(),
                'activeUsers' => User::where('role', 'user')->where('is_active', true)->count(),
            ];
        } else {
            $data = [
                'myRequests' => WorkRequest::where('user_id', $user->id)->count(),
                'myPendingRequests' => WorkRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
                'myCompletedRequests' => WorkRequest::where('user_id', $user->id)->where('status', 'completed')->count(),
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getChartData()
    {
        $chartData = $this->getMonthlyChartData();
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => $chartData,
        ]);
    }
}
