<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InspectionReportController extends Controller
{
    /**
     * Display a listing of inspection reports.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Base query for inspection reports with work request and user
        $query = InspectionReport::with(['workRequest', 'workRequest.user']);
        
        // If not admin, only show inspections for their own work requests
        if (!$user->isAdmin()) {
            $query->whereHas('workRequest', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Apply date from filter
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }
        
        // Apply date to filter
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                  ->orWhereHas('workRequest', function($wr) use ($search) {
                      $wr->where('title', 'like', "%{$search}%")
                         ->orWhere('request_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('workRequest.user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // FIXED: Order by status (pending first) then by scheduled_date ascending
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
        
        // Calculate statistics
        $totalInspections = InspectionReport::when(!$user->isAdmin(), function($q) use ($user) {
            return $q->whereHas('workRequest', function($wr) use ($user) {
                $wr->where('user_id', $user->id);
            });
        })->count();
        
        $upcomingInspections = InspectionReport::where('status', 'pending')
            ->where('scheduled_date', '>', now())
            ->when(!$user->isAdmin(), function($q) use ($user) {
                return $q->whereHas('workRequest', function($wr) use ($user) {
                    $wr->where('user_id', $user->id);
                });
            })
            ->count();
        
        $approvedInspections = InspectionReport::where('status', 'approved')
            ->when(!$user->isAdmin(), function($q) use ($user) {
                return $q->whereHas('workRequest', function($wr) use ($user) {
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
    
    /**
     * Display the specified inspection report.
     */
    public function show($id)
    {
        $inspectionReport = InspectionReport::with(['workRequest', 'workRequest.user', 'inspector'])
            ->findOrFail($id);
        
        $user = Auth::user();
        
        // Check authorization
        if (!$user->isAdmin() && $inspectionReport->workRequest->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('inspection-reports.show', compact('inspectionReport'));
    }
    
    /**
     * Show form to approve inspection report.
     */
    public function completeForm($id)
    {
        $inspectionReport = InspectionReport::with('workRequest')->findOrFail($id);
        
        // Only admin can approve inspection reports
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($inspectionReport->status === 'approved') {
            return redirect()->route('inspections.show', $inspectionReport->id)
                ->with('error', 'This inspection report is already approved.');
        }
        
        return view('inspection-reports.complete', compact('inspectionReport'));
    }
    
    /**
     * Approve the inspection report and approve the work request.
     */
    public function complete(Request $request, $id)
    {
        $inspectionReport = InspectionReport::with('workRequest')->findOrFail($id);
        
        // Only admin can approve inspection reports
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'findings' => 'required|string',
            'recommendations' => 'required|string',
            'status' => 'required|in:approved,cancelled',
        ]);
        
        try {
            // Update the inspection report to 'approved'
            $inspectionReport->update([
                'findings' => $validated['findings'],
                'recommendations' => $validated['recommendations'],
                'status' => $validated['status'],
                'actual_inspection_date' => now(),
                'inspected_by' => Auth::id(),
            ]);
            
            // Update work request status to 'approved' when inspection is approved
            if ($validated['status'] === 'approved') {
                $inspectionReport->workRequest->update([
                    'status' => 'approved'
                ]);
                
                // Create notification for the requester
                Notification::create([
                    'user_id' => $inspectionReport->workRequest->user_id,
                    'title' => 'Work Request Approved',
                    'message' => "Your work request #{$inspectionReport->workRequest->id}: '{$inspectionReport->workRequest->title}' has been approved after inspection.",
                    'type' => 'success',
                    'is_read' => false
                ]);
            } else {
                // If cancelled, notify the requester
                Notification::create([
                    'user_id' => $inspectionReport->workRequest->user_id,
                    'title' => 'Inspection Cancelled',
                    'message' => "The inspection for your work request #{$inspectionReport->workRequest->id}: '{$inspectionReport->workRequest->title}' has been cancelled.",
                    'type' => 'warning',
                    'is_read' => false
                ]);
            }
            
            return redirect()->route('inspections.show', $inspectionReport->id)
                ->with('success', 'Inspection report approved and work request approved successfully.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve inspection report: ' . $e->getMessage());
        }
    }
}