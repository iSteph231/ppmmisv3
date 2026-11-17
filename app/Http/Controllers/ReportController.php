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

        \Log::info('Current user: ', ['id' => $user->id, 'role' => $user->role, 'name' => $user->name]);

        $totalWorkRequests = WorkRequest::count();
        $completedRequests = WorkRequest::where('status', 'completed')->count();
        $pendingRequests = WorkRequest::where('status', 'pending')->count();
        $approvedRequests = WorkRequest::where('status', 'approved')->count();

        \Log::info('Work Request Counts: ', [
            'total' => $totalWorkRequests,
            'completed' => $completedRequests,
            'pending' => $pendingRequests,
            'approved' => $approvedRequests,
        ]);

        $allWorkRequests = WorkRequest::all();
        \Log::info('All Work Requests: ', $allWorkRequests->toArray());

        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create(null, $month, 1)->format('M');
        });

        $monthlyWorkRequests = collect(range(1, 12))->map(function ($month) {
            return WorkRequest::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
        });

        \Log::info('Monthly Work Requests: ', $monthlyWorkRequests->toArray());

        $requestTypesLabels = ['Ocular Inspection', 'Installation', 'Repair', 'Replacement', 'Others'];
        $requestTypesData = [
            WorkRequest::where('work_type', 'ocular_inspection')->count(),
            WorkRequest::where('work_type', 'installation')->count(),
            WorkRequest::where('work_type', 'repair')->count(),
            WorkRequest::where('work_type', 'replacement')->count(),
            WorkRequest::where('work_type', 'others')->count(),
        ];

        \Log::info('Work Types Data: ', $requestTypesData);

        $totalMaintenance = MaintenanceSchedule::count();
        $completedMaintenance = MaintenanceSchedule::where('status', 'completed')->count();
        $upcomingMaintenance = MaintenanceSchedule::where('scheduled_date', '>', now())->count();

        $activeUsers = User::where('role', 'user')->where('is_active', true)->count();

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
            'activeUsers',
            'completionRate'
        ));
    }

    public function workRequestsReport(Request $request)
    {
        $workRequests = $this->filteredWorkRequests($request)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalRequests = WorkRequest::count();
        $pendingCount = WorkRequest::where('status', 'pending')->count();
        $completedCount = WorkRequest::where('status', 'completed')->count();
        $approvedCount = WorkRequest::where('status', 'approved')->count();

        return view('reports.work-requests', compact('workRequests', 'totalRequests', 'pendingCount', 'completedCount', 'approvedCount'));
    }

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

    public function maintenanceReport(Request $request)
    {
        $maintenanceSchedules = $this->filteredMaintenanceSchedules($request)
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

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

    public function exportMaintenancePdf(Request $request)
    {
        $maintenanceSchedules = $this->filteredMaintenanceSchedules($request, false)
            ->where('status', 'completed')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $month = $request->filled('month')
            ? Carbon::createFromFormat('!Y-m', $request->input('month'))
            : ($request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : optional($maintenanceSchedules->first())->scheduled_date ?? now());

        $pdf = Pdf::loadView('maintenance.export-pdf', [
            'maintenanceSchedules' => $maintenanceSchedules,
            'monthName' => Carbon::parse($month)->format('F'),
            'yearSuffix' => Carbon::parse($month)->format('y'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('completed-maintenance-report-'.now()->format('Y-m-d-His').'.pdf');
    }

    private function filteredMaintenanceSchedules(Request $request, bool $includeStatus = true)
    {
        $rules = [
            'month' => ['nullable', 'date_format:Y-m'],
            'status' => ['nullable', 'in:all,pending,in_progress,completed,cancelled'],
        ];
        if (! $request->filled('month')) {
            $rules['date_from'] = ['nullable', 'date_format:Y-m-d'];
            $rules['date_to'] = ['nullable', 'date_format:Y-m-d'];
            if ($request->filled('date_from')) {
                $rules['date_to'][] = 'after_or_equal:date_from';
            }
        }
        $request->validate($rules);
        $query = MaintenanceSchedule::query();

        if ($includeStatus && $request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $month = Carbon::createFromFormat('!Y-m', $request->input('month'));

            return $query->where('scheduled_date', '>=', $month->toDateString())
                ->where('scheduled_date', '<', $month->copy()->addMonth()->toDateString());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        return $query;
    }

    public function performanceReport()
    {

        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create(null, $month, 1)->format('M');
        });

        $monthlyWorkRequests = collect(range(1, 12))->map(function ($month) {
            return WorkRequest::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
        });

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

        $requestTypesLabels = ['Ocular Inspection', 'Installation', 'Repair', 'Replacement', 'Others'];
        $requestTypesData = [
            WorkRequest::where('work_type', 'ocular_inspection')->count(),
            WorkRequest::where('work_type', 'installation')->count(),
            WorkRequest::where('work_type', 'repair')->count(),
            WorkRequest::where('work_type', 'replacement')->count(),
            WorkRequest::where('work_type', 'others')->count(),
        ];

        $totalWorkRequests = WorkRequest::count();
        $completedRequests = WorkRequest::where('status', 'completed')->count();
        $pendingRequests = WorkRequest::where('status', 'pending')->count();
        $approvedRequests = WorkRequest::where('status', 'approved')->count();

        $completionRate = $totalWorkRequests > 0 ? round(($completedRequests / $totalWorkRequests) * 100) : 0;

        $avgCompletionTime = WorkRequest::whereNotNull('completed_at')
            ->get()
            ->avg(function ($request) {
                return $request->created_at->diffInDays($request->completed_at);
            }) ?? 0;

        $activeUsers = User::where('role', 'user')->where('is_active', true)->count();

        $topRequesters = WorkRequest::with('user')
            ->selectRaw('user_id, requester_name, count(*) as total')
            ->groupBy('user_id', 'requester_name')
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
            'activeUsers',
            'topRequesters'
        ));
    }
}
