<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InspectionReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = InspectionReport::with(['workRequest', 'workRequest.user']);

        if (! $user->isAdmin()) {
            $query->whereHas('workRequest', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                    ->orWhereHas('workRequest', function ($wr) use ($search) {
                        $wr->where('title', 'like', "%{$search}%")
                            ->orWhere('request_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('workRequest.user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $inspections = $query->orderByRaw("
            CASE
                WHEN status = 'pending' THEN 1
                WHEN status = 'in_progress' THEN 2
                WHEN status = 'approved' THEN 3
                WHEN status = 'completed' THEN 4
                ELSE 5
            END
        ")
            ->orderBy('scheduled_date', 'asc')
            ->paginate(10);

        $totalInspections = InspectionReport::when(! $user->isAdmin(), function ($q) use ($user) {
            return $q->whereHas('workRequest', function ($wr) use ($user) {
                $wr->where('user_id', $user->id);
            });
        })->count();

        $upcomingInspections = InspectionReport::where('status', 'pending')
            ->where('scheduled_date', '>', now())
            ->when(! $user->isAdmin(), function ($q) use ($user) {
                return $q->whereHas('workRequest', function ($wr) use ($user) {
                    $wr->where('user_id', $user->id);
                });
            })
            ->count();

        $approvedInspections = InspectionReport::where('status', 'approved')
            ->when(! $user->isAdmin(), function ($q) use ($user) {
                return $q->whereHas('workRequest', function ($wr) use ($user) {
                    $wr->where('user_id', $user->id);
                });
            })
            ->count();

        return view('inspection-reports.index', compact(
            'inspections',
            'totalInspections',
            'upcomingInspections',
            'approvedInspections'
        ));
    }

    public function show($id)
    {
        $inspectionReport = InspectionReport::with(['workRequest', 'workRequest.user', 'inspector'])
            ->findOrFail($id);

        $user = Auth::user();

        if (! $user->isAdmin() && $inspectionReport->workRequest->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('inspection-reports.show', compact('inspectionReport'));
    }

    public function exportAllPdf(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('date_from') ? ['after_or_equal:date_from'] : [])],
        ]);
        $user = Auth::user();

        $query = InspectionReport::with(['workRequest', 'workRequest.user', 'inspector']);

        if (! $user->isAdmin()) {
            $query->whereHas('workRequest', function ($workRequestQuery) use ($user) {
                $workRequestQuery->where('user_id', $user->id);
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('scheduled_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('scheduled_date', '<=', $filters['date_to']);
        }
        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($query) use ($search): void {
                $query->where('report_number', 'like', $search)
                    ->orWhereHas('workRequest', function ($workRequest) use ($search): void {
                        $workRequest->where('title', 'like', $search)
                            ->orWhere('request_number', 'like', $search);
                    })
                    ->orWhereHas('workRequest.user', function ($user) use ($search): void {
                        $user->where('name', 'like', $search);
                    });
            });
        }

        $inspectionReports = $query
            ->where('status', InspectionReport::STATUS_APPROVED)
            ->orderByRaw('COALESCE(actual_inspection_date, scheduled_date) asc')
            ->get();

        $pdf = Pdf::loadView('inspection-reports.export-pdf', compact('inspectionReports'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('inspection-reports-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function completeForm($id)
    {
        $inspectionReport = InspectionReport::with('workRequest')->findOrFail($id);

        if (! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($inspectionReport->status === 'approved') {
            return redirect()->route('inspections.show', $inspectionReport->id)
                ->with('error', 'This inspection report is already approved.');
        }

        return view('inspection-reports.complete', compact('inspectionReport'));
    }

    public function complete(Request $request, $id)
    {
        $inspectionReport = InspectionReport::with('workRequest')->findOrFail($id);

        if (! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'findings' => 'required|string',
            'observation' => 'required|string',
            'recommendations' => 'required|string',
            'status' => 'required|in:approved',
        ]);

        try {

            $inspectionReport->update([
                'findings' => $validated['findings'],
                'observation' => $validated['observation'],
                'recommendations' => $validated['recommendations'],
                'status' => $validated['status'],
                'actual_inspection_date' => now(),
                'inspected_by' => Auth::id(),
            ]);

            $inspectionReport->workRequest->update([
                'status' => 'approved',
            ]);

            if ($inspectionReport->workRequest->user_id !== null) {
                Notification::create([
                    'user_id' => $inspectionReport->workRequest->user_id,
                    'title' => 'Work Request Approved',
                    'message' => "Your work request #{$inspectionReport->workRequest->id}: '{$inspectionReport->workRequest->title}' has been approved after inspection.",
                    'type' => 'success',
                    'is_read' => false,
                ]);
            }

            return redirect()->route('inspections.show', $inspectionReport->id)
                ->with('success', 'Inspection report approved and work request approved successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve inspection report: '.$e->getMessage());
        }
    }
}
