<?php

namespace App\Http\Controllers;

use App\Models\WorkRequest;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        // If not logged in, redirect to login
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get recent notifications for the user
        $notifications = $this->getUserNotifications($user);
        
        // Get monthly chart data for ALL users (overall stats)
        $monthlyData = $this->getMonthlyChartData();
        
        // Check user role and return appropriate dashboard
        if ($user->isAdmin()) {
            return $this->adminDashboard($notifications, $monthlyData);
        } elseif ($user->isPersonnel()) {
            return $this->personnelDashboard($notifications, $monthlyData);
        } else {
            return $this->userDashboard($notifications, $monthlyData);
        }
    }

    /**
     * Get user notifications from database
     */
    private function getUserNotifications($user)
    {
        $notifications = Notification::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereNull('user_id');
            })
            ->latest()
            ->take(10)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time' => $notification->created_at->diffForHumans(),
                    'read' => (bool)($notification->is_read ?? false),
                    'created_at' => $notification->created_at
                ];
            });
        
        if ($notifications->isEmpty()) {
            return $this->createDefaultNotifications($user);
        }
        
        return $notifications;
    }

    /**
     * Create default notifications if none exist
     */
    private function createDefaultNotifications($user)
    {
        $notifications = collect();
        
        $existingWelcome = Notification::where('user_id', $user->id)
            ->where('title', 'Welcome to PPMMIS')
            ->first();
            
        if (!$existingWelcome) {
            $welcomeNotif = Notification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to PPMMIS',
                'message' => 'Your account has been created successfully. You can now create maintenance requests.',
                'type' => 'info',
                'is_read' => false
            ]);
            
            $notifications->push([
                'id' => $welcomeNotif->id,
                'title' => $welcomeNotif->title,
                'message' => $welcomeNotif->message,
                'time' => $welcomeNotif->created_at->diffForHumans(),
                'read' => false,
                'created_at' => $welcomeNotif->created_at
            ]);
        }
        
        $existingSystem = Notification::where('user_id', $user->id)
            ->where('title', 'System Ready')
            ->first();
            
        if (!$existingSystem) {
            $systemNotif = Notification::create([
                'user_id' => $user->id,
                'title' => 'System Ready',
                'message' => 'The maintenance system is now fully operational. Submit your requests anytime.',
                'type' => 'success',
                'is_read' => false
            ]);
            
            $notifications->push([
                'id' => $systemNotif->id,
                'title' => $systemNotif->title,
                'message' => $systemNotif->message,
                'time' => $systemNotif->created_at->diffForHumans(),
                'read' => false,
                'created_at' => $systemNotif->created_at
            ]);
        }
        
        return $notifications;
    }

    /**
     * Get monthly chart data from database
     */
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

    /**
     * Admin Dashboard Data - Sees ALL work requests from all campuses
     */
    private function adminDashboard($notifications, $monthlyData)
    {
        $userId = Auth::id();
        
        // Get statistics from database (all requests)
        $totalRequests = WorkRequest::count();
        $pendingRequests = WorkRequest::where('status', 'pending')->count();
        $completedRequests = WorkRequest::where('status', 'completed')->count();
        $activePersonnel = User::where('role', 'personnel')->where('is_active', true)->count();
        
        // ADMIN sees ALL work requests (no filters)
        $workRequests = WorkRequest::with('user')
            ->latest()
            ->take(10)
            ->get();
        
        return view('dashboard.index', compact(
            'totalRequests',
            'pendingRequests',
            'completedRequests',
            'activePersonnel',
            'monthlyData',
            'workRequests',
            'notifications'
        ));
    }

    /**
     * Personnel Dashboard Data - Sees work requests ONLY from their assigned campus
     */
    private function personnelDashboard($notifications, $monthlyData)
    {
        $user = Auth::user();
        $assignedCampus = $user->assigned_campus;
        
        // If personnel has no assigned campus, show empty data
        if (!$assignedCampus) {
            $totalRequests = 0;
            $pendingRequests = 0;
            $completedRequests = 0;
            $workRequests = collect();
            
            return view('dashboard.index', compact(
                'totalRequests',
                'pendingRequests',
                'completedRequests',
                'monthlyData',
                'workRequests',
                'notifications'
            ));
        }
        
        // Statistics for personnel's assigned campus only
        $totalRequests = WorkRequest::where('campus', $assignedCampus)->count();
        $pendingRequests = WorkRequest::where('campus', $assignedCampus)
            ->where('status', 'pending')
            ->count();
        $completedRequests = WorkRequest::where('campus', $assignedCampus)
            ->where('status', 'completed')
            ->count();
        
        // Work requests from personnel's assigned campus only
        $workRequests = WorkRequest::with('user')
            ->where('campus', $assignedCampus)
            ->latest()
            ->take(10)
            ->get();
        
        return view('dashboard.index', compact(
            'totalRequests',
            'pendingRequests',
            'completedRequests',
            'monthlyData',
            'workRequests',
            'notifications'
        ));
    }

    /**
     * User Dashboard Data - Sees ONLY their OWN work requests
     */
    private function userDashboard($notifications, $monthlyData)
    {
        $userId = Auth::id();
        
        // User specific statistics (only their own requests)
        $myRequests = WorkRequest::where('user_id', $userId)->count();
        $myPendingRequests = WorkRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();
        $myCompletedRequests = WorkRequest::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();
        
        // User ONLY sees their OWN work requests
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

    /**
     * Get dashboard statistics for AJAX refresh
     */
    public function getStats()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        if ($user->isAdmin()) {
            $data = [
                'totalRequests' => WorkRequest::count(),
                'pendingRequests' => WorkRequest::where('status', 'pending')->count(),
                'completedRequests' => WorkRequest::where('status', 'completed')->count(),
                'activePersonnel' => User::where('role', 'personnel')->where('is_active', true)->count(),
            ];
        } elseif ($user->isPersonnel()) {
            $assignedCampus = $user->assigned_campus;
            $data = [
                'totalRequests' => WorkRequest::where('campus', $assignedCampus)->count(),
                'pendingRequests' => WorkRequest::where('campus', $assignedCampus)->where('status', 'pending')->count(),
                'completedRequests' => WorkRequest::where('campus', $assignedCampus)->where('status', 'completed')->count(),
                'activePersonnel' => null,
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

    /**
     * Get chart data for AJAX refresh
     */
    public function getChartData()
    {
        $chartData = $this->getMonthlyChartData();
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => $chartData
        ]);
    }
}