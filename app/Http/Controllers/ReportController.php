<?php

namespace App\Http\Controllers;

use App\Models\FacilityRequest;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\WorkRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function facilityRequestsReport(): View
    {
        $facilityRequests = FacilityRequest::with('user')
            ->where('status', 'finished')
            ->latest('updated_at')
            ->latest('id')
            ->paginate(10);

        return view('reports.facility-requests', compact('facilityRequests'));
    }
    public function index()
    {
        $user = Auth::user();

        // Debug: Log the user role
        \Log::info('Current user: ', ['id' => $user->id, 'role' => $user->role, 'name' => $user->name]);

        // Work Request Statistics
        $totalWorkRequests = WorkRequest::count();
        $completedRequests = WorkRequest::where('status', 'completed')->count();
        $pendingRequests = WorkRequest::where('status', 'pending')->count();
        $approvedRequests = WorkRequest::where('status', 'approved')->count();

        // Debug: Log work request counts
        \Log::info('Work Request Counts: ', [
            'total' => $totalWorkRequests,
            'completed' => $completedRequests,
            'pending' => $pendingRequests,
            'approved' => $approvedRequests,
        ]);

        // Get all work requests for debugging
        $allWorkRequests = WorkRequest::all();
        \Log::info('All Work Requests: ', $allWorkRequests->toArray());

        // Monthly Work Requests Data
        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create(null, $month, 1)->format('M');
        });

        $monthlyWorkRequests = collect(range(1, 12))->map(function ($month) {
            return WorkRequest::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
        });

        // Debug: Log monthly data
        \Log::info('Monthly Work Requests: ', $monthlyWorkRequests->toArray());

        // Requests by Type
        $requestTypesLabels = ['Ocular Inspection', 'Installation', 'Repair', 'Replacement', 'Others'];
        $requestTypesData = [
            WorkRequest::where('work_type', 'ocular_inspection')->count(),
            WorkRequest::where('work_type', 'installation')->count(),
            WorkRequest::where('work_type', 'repair')->count(),
            WorkRequest::where('work_type', 'replacement')->count(),
            WorkRequest::where('work_type', 'others')->count(),
        ];

        // Debug: Log work types
        \Log::info('Work Types Data: ', $requestTypesData);

        // Maintenance Statistics
        $totalMaintenance = MaintenanceSchedule::count();
        $completedMaintenance = MaintenanceSchedule::where('status', 'completed')->count();
        $upcomingMaintenance = MaintenanceSchedule::where('scheduled_date', '>', now())->count();

        // Personnel Statistics
        $activePersonnel = User::where('role', 'personnel')->where('is_active', true)->count();

        // Completion Rate
        $completionRate = $totalWorkRequests > 0 ? round(($completedRequests / $totalWorkRequests) * 100) : 0;

        return view('reports.index', compact(
            'totalWorkRequests',
            'completedRequests',
            'pendingRequests',
            'approvedRequests',
            'months',
            'monthlyWorkRequests',
            'requestTypesLabels',
            'requestTypesData',
            'totalMaintenance',
            'completedMaintenance',
            'upcomingMaintenance',
            'activePersonnel',
            'completionRate'
        ));
    }

    /**
     * Work Requests Report Page
     */
    public function workRequestsReport(Request $request)
    {
        $workRequests = $this->filteredWorkRequests($request)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get summary statistics
        $totalRequests = WorkRequest::count();
        $pendingCount = WorkRequest::where('status', 'pending')->count();
        $completedCount = WorkRequest::where('status', 'completed')->count();
        $approvedCount = WorkRequest::where('status', 'approved')->count();

        return view('reports.work-requests', compact('workRequests', 'totalRequests', 'pendingCount', 'completedCount', 'approvedCount'));
    }

    /**
     * Export filtered work requests as a PDF report.
     */
    public function exportWorkRequestsPdf(Request $request)
    {
        $workRequests = $this->filteredWorkRequests($request)
            ->where('status', WorkRequest::STATUS_APPROVED)
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('work-requests.export-pdf', [
            'workRequests' => $workRequests,
            'filters' => $request->only(['status', 'work_type', 'date_from', 'date_to']),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('work-requests-report-'.now()->format('Y-m-d-His').'.pdf');
    }

    /**
     * Export an individual work request as a PDF.
     */
    public function exportWorkRequestPdf(WorkRequest $workRequest)
    {
        if ($workRequest->status !== WorkRequest::STATUS_APPROVED) {
            return redirect()->route('reports.work-requests')
                ->with('error', 'Only approved work requests can be exported.');
        }

        $workRequest->load('user');

        $pdf = Pdf::loadView('work-requests.export-single-pdf', compact('workRequest'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('work-request-'.$workRequest->request_number.'.pdf');
    }

    /**
     * Build the filtered work request report query.
     */
    private function filteredWorkRequests(Request $request)
    {
        $query = WorkRequest::with('user');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('work_type') && $request->work_type !== 'all') {
            $query->where('work_type', $request->work_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * Maintenance Report Page
     */
    public function maintenanceReport(Request $request)
    {
        $maintenanceSchedules = $this->filteredMaintenanceSchedules($request)
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        // Add statistics for the view
        $totalSchedules = MaintenanceSchedule::count();
        $pendingSchedules = MaintenanceSchedule::where('status', 'pending')->count();
        $completedSchedules = MaintenanceSchedule::where('status', 'completed')->count();
        $inProgressSchedules = MaintenanceSchedule::where('status', 'in_progress')->count();

        return view('reports.maintenance', compact(
            'maintenanceSchedules',
            'totalSchedules',
            'pendingSchedules',
            'completedSchedules',
            'inProgressSchedules'
        ));
    }

    /**
     * Export completed maintenance schedules as a PDF report.
     */
    public function exportMaintenancePdf(Request $request)
    {
        $maintenanceSchedules = $this->filteredMaintenanceSchedules($request, false)
            ->where('status', 'completed')
            ->orderBy('scheduled_date', 'asc')
            ->get();

        $month = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : optional($maintenanceSchedules->first())->scheduled_date ?? now();

        $pdf = Pdf::loadView('maintenance.export-pdf', [
            'maintenanceSchedules' => $maintenanceSchedules,
            'monthName' => Carbon::parse($month)->format('F'),
            'yearSuffix' => Carbon::parse($month)->format('y'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('completed-maintenance-report-'.now()->format('Y-m-d-His').'.pdf');
    }

    /**
     * Build the filtered maintenance report query.
     */
    private function filteredMaintenanceSchedules(Request $request, bool $includeStatus = true)
    {
        $query = MaintenanceSchedule::query();

        if ($includeStatus && $request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * Performance Report Page
     */
    public function performanceReport()
    {
        // Get monthly completion data
        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create(null, $month, 1)->format('M');
        });

        // Monthly work requests count
        $monthlyWorkRequests = collect(range(1, 12))->map(function ($month) {
            return WorkRequest::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
        });

        // Monthly completed requests count
        $completedMonthly = collect(range(1, 12))->map(function ($month) {
            return WorkRequest::where('status', 'completed')
                ->whereYear('completed_at', now()->year)
                ->whereMonth('completed_at', $month)
                ->count();
        });

        $createdMonthly = collect(range(1, 12))->map(function ($month) {
            return WorkRequest::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
        });

        // Work Types Data
        $requestTypesLabels = ['Ocular Inspection', 'Installation', 'Repair', 'Replacement', 'Others'];
        $requestTypesData = [
            WorkRequest::where('work_type', 'ocular_inspection')->count(),
            WorkRequest::where('work_type', 'installation')->count(),
            WorkRequest::where('work_type', 'repair')->count(),
            WorkRequest::where('work_type', 'replacement')->count(),
            WorkRequest::where('work_type', 'others')->count(),
        ];

        // Status counts
        $totalWorkRequests = WorkRequest::count();
        $completedRequests = WorkRequest::where('status', 'completed')->count();
        $pendingRequests = WorkRequest::where('status', 'pending')->count();
        $approvedRequests = WorkRequest::where('status', 'approved')->count();

        // Completion rate
        $completionRate = $totalWorkRequests > 0 ? round(($completedRequests / $totalWorkRequests) * 100) : 0;

        // Average completion time (in days)
        $avgCompletionTime = WorkRequest::whereNotNull('completed_at')
            ->get()
            ->avg(function ($request) {
                return $request->created_at->diffInDays($request->completed_at);
            }) ?? 0;

        // Active personnel
        $activePersonnel = User::where('role', 'personnel')->where('is_active', true)->count();

        // Get top requesters
        $topRequesters = WorkRequest::with('user')
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return view('reports.performance', compact(
            'months',
            'monthlyWorkRequests',
            'completedMonthly',
            'createdMonthly',
            'requestTypesLabels',
            'requestTypesData',
            'totalWorkRequests',
            'completedRequests',
            'pendingRequests',
            'approvedRequests',
            'completionRate',
            'avgCompletionTime',
            'activePersonnel',
            'topRequesters'
        ));
    }
}
