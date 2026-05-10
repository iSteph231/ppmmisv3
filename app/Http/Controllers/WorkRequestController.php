<?php

namespace App\Http\Controllers;

use App\Models\WorkRequest;
use App\Models\InspectionReport;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WorkRequestController extends Controller
{
    /**
     * Display a listing of the work requests.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = WorkRequest::with('user', 'inspectionReport');
        
        // ==============================================
        // FILTER BASED ON USER ROLE
        // ==============================================
        
        if ($user->isAdmin()) {
            // Admin sees ALL work requests
            // No filter needed
            
        } elseif ($user->isPersonnel()) {
            // Personnel sees ONLY their own work requests
            $query->where('user_id', $user->id);
            
        } else {
            // Regular user sees ONLY their own work requests
            $query->where('user_id', $user->id);
        }
        
        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Apply date from filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        // Apply date to filter
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('request_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $workRequests = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('work-requests.index', compact('workRequests'));
    }
    
    /**
     * Show the form for creating a new work request.
     */
    public function create()
    {
        return view('work-requests.create');
    }
    
    /**
     * Store a newly created work request in storage.
     */
    public function store(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('Store request data:', $request->all());
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'office_room' => 'nullable|string|max:255',
            'request_type' => 'required|string|max:50',
            'ocular_location' => 'required_if:request_type,ocular_inspection|nullable|string',
            'installation_item' => 'required_if:request_type,installation|nullable|string',
            'repair_item' => 'required_if:request_type,repair|nullable|string',
            'replacement_item' => 'required_if:request_type,replacement|nullable|string',
            'others_specify' => 'required_if:request_type,others|nullable|string',
            'additional_description' => 'nullable|string',
        ]);
        
        // Generate request number
        $yearMonth = now()->format('Ym');
        $lastRequest = WorkRequest::where('request_number', 'like', "WR-{$yearMonth}-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastRequest) {
            $lastNumber = intval(substr($lastRequest->request_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        // ==============================================
        // BUILD THE DESCRIPTION FROM REQUEST DETAILS
        // ==============================================
        
        // Build a detailed description combining all relevant information
        $descriptionParts = [];
        
        // Add location/office info
        if ($request->filled('building_name') || $request->filled('office_room')) {
            $location = [];
            if ($request->filled('building_name')) $location[] = "Building: " . $request->building_name;
            if ($request->filled('office_room')) $location[] = "Room/Office: " . $request->office_room;
            $descriptionParts[] = "LOCATION: " . implode(" | ", $location);
        }
        
        // Add request type specific details
        if ($request->request_type === 'ocular_inspection') {
            $descriptionParts[] = "REQUEST TYPE: Ocular Inspection";
            $descriptionParts[] = "Location to inspect: " . ($request->ocular_location ?? 'Not specified');
        } elseif ($request->request_type === 'installation') {
            $descriptionParts[] = "REQUEST TYPE: Installation";
            $descriptionParts[] = "Item to install: " . ($request->installation_item ?? 'Not specified');
        } elseif ($request->request_type === 'repair') {
            $descriptionParts[] = "REQUEST TYPE: Repair";
            $descriptionParts[] = "Item to repair: " . ($request->repair_item ?? 'Not specified');
        } elseif ($request->request_type === 'replacement') {
            $descriptionParts[] = "REQUEST TYPE: Replacement";
            $descriptionParts[] = "Item to replace: " . ($request->replacement_item ?? 'Not specified');
        } elseif ($request->request_type === 'others') {
            $descriptionParts[] = "REQUEST TYPE: Others";
            $descriptionParts[] = "Details: " . ($request->others_specify ?? 'Not specified');
        }
        
        // Add department info
        if ($request->filled('department')) {
            $descriptionParts[] = "Department: " . $request->department;
        }
        
        // Add any additional description if provided
        if ($request->filled('additional_description')) {
            $descriptionParts[] = "";
            $descriptionParts[] = "ADDITIONAL NOTES:";
            $descriptionParts[] = $request->additional_description;
        }
        
        // If no description parts were added, provide a default
        if (empty($descriptionParts)) {
            $descriptionParts[] = "Work request: " . $request->title;
        }
        
        // Combine all parts into one description
        $fullDescription = implode("\n", $descriptionParts);
        
        // Prepare data for insertion
        $data = [
            'request_number' => "WR-{$yearMonth}-{$newNumber}",
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $fullDescription,
            'department' => $request->department,
            'building_name' => $request->building_name,
            'office_room' => $request->office_room,
            'work_type' => $request->request_type,
            'status' => 'pending',
        ];
        
        // Add conditional fields if they exist in your table
        if ($request->request_type === 'ocular_inspection') {
            $data['ocular_details'] = $request->ocular_location;
        } elseif ($request->request_type === 'installation') {
            $data['installation_details'] = $request->installation_item;
        } elseif ($request->request_type === 'repair') {
            $data['repair_details'] = $request->repair_item;
        } elseif ($request->request_type === 'replacement') {
            $data['replacement_details'] = $request->replacement_item;
        } elseif ($request->request_type === 'others') {
            $data['others_details'] = $request->others_specify;
        }
        
        // Log the data being inserted
        Log::info('Inserting work request:', $data);
        
        try {
            $workRequest = WorkRequest::create($data);
            Log::info('Work request created successfully with ID: ' . $workRequest->id);
            
            // ==============================================
            // CREATE NOTIFICATIONS
            // ==============================================
            
            // 1. Create notification for the user who created the request
            Notification::create([
                'user_id' => Auth::id(),
                'title' => 'Work Request Created',
                'message' => "Your work request #{$workRequest->id}: '{$workRequest->title}' has been submitted successfully.",
                'type' => 'info',
                'is_read' => false
            ]);
            
            // 2. Create notification for all admin users
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'New Work Request',
                    'message' => "New work request #{$workRequest->id}: '{$workRequest->title}' from " . Auth::user()->name,
                    'type' => 'info',
                    'is_read' => false
                ]);
            }
            
            return redirect()->route('work-requests.index')
                ->with('success', 'Work request created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating work request: ' . $e->getMessage());
            return back()->with('error', 'Failed to create work request: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display the specified work request.
     */
    public function show(WorkRequest $workRequest)
    {
        $user = Auth::user();
        
        // Load the inspection report if it exists
        $workRequest->load('inspectionReport', 'user');
        
        // Admin can see any request
        if ($user->isAdmin()) {
            return view('work-requests.show', compact('workRequest'));
        }
        
        // Regular user can only see their own requests
        if ($user->id === $workRequest->user_id) {
            return view('work-requests.show', compact('workRequest'));
        }
        
        abort(403, 'Unauthorized action.');
    }
    
    /**
     * Show the form for editing the specified work request.
     */
    public function edit(WorkRequest $workRequest)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('work-requests.edit', compact('workRequest'));
    }
    
    /**
     * Update the specified work request in storage.
     */
    public function update(Request $request, WorkRequest $workRequest)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'department' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'office_room' => 'nullable|string|max:255',
            'status' => 'required|string|in:pending,approved,completed',
            'admin_notes' => 'nullable|string',
        ]);
        
        $oldStatus = $workRequest->status;
        $workRequest->update($validated);
        
        // Add notification when status changes
        if ($oldStatus !== $workRequest->status) {
            Notification::create([
                'user_id' => $workRequest->user_id,
                'title' => 'Work Request Updated',
                'message' => "Your work request #{$workRequest->id}: '{$workRequest->title}' status has been updated from " . ucfirst($oldStatus) . " to " . ucfirst($workRequest->status),
                'type' => 'info',
                'is_read' => false
            ]);
        }
        
        return redirect()->route('work-requests.index')
            ->with('success', 'Work request updated successfully.');
    }
    
    /**
     * Schedule inspection for the specified work request.
     * This will automatically create an inspection report in pending status.
     */
    public function schedule(Request $request, WorkRequest $workRequest)
    {
        // Only admin can schedule inspection
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'scheduled_date' => 'required|date|after:now',
            'inspection_notes' => 'nullable|string',
        ]);
        
        try {
            // Check if inspection report already exists
            if ($workRequest->inspectionReport) {
                return back()->with('error', 'An inspection report already exists for this work request. Cannot schedule another inspection.');
            }
            
            // Update the work request with scheduled date and inspection notes
            $workRequest->update([
                'scheduled_date' => $validated['scheduled_date'],
                'inspection_notes' => $validated['inspection_notes'] ?? null,
            ]);
            
            // ==============================================
            // CREATE INSPECTION REPORT AUTOMATICALLY
            // ==============================================
            
            // Generate report number
            $yearMonth = now()->format('Ym');
            $lastReport = InspectionReport::where('report_number', 'like', "IR-{$yearMonth}-%")
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastReport) {
                $lastNumber = intval(substr($lastReport->report_number, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
            
            // Create the inspection report with pending status
            $inspectionReport = InspectionReport::create([
                'work_request_id' => $workRequest->id,
                'report_number' => "IR-{$yearMonth}-{$newNumber}",
                'scheduled_date' => $validated['scheduled_date'],
                'inspection_notes' => $validated['inspection_notes'] ?? null,
                'status' => InspectionReport::STATUS_PENDING
            ]);
            
            Log::info('Inspection report created automatically', [
                'report_number' => $inspectionReport->report_number,
                'work_request_id' => $workRequest->id,
                'work_request_number' => $workRequest->request_number
            ]);
            
            // Create notification for the requester
            Notification::create([
                'user_id' => $workRequest->user_id,
                'title' => 'Inspection Scheduled',
                'message' => "Your work request #{$workRequest->id}: '{$workRequest->title}' has been scheduled for inspection on " . Carbon::parse($validated['scheduled_date'])->format('F d, Y h:i A') . ". Inspection Report #{$inspectionReport->report_number} has been created and is pending completion.",
                'type' => 'info',
                'is_read' => false
            ]);
            
            // Also notify all admins about the schedule
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Inspection Scheduled',
                    'message' => "Inspection scheduled for work request #{$workRequest->id}: '{$workRequest->title}' on " . Carbon::parse($validated['scheduled_date'])->format('F d, Y h:i A') . ". Report #{$inspectionReport->report_number} created and pending.",
                    'type' => 'info',
                    'is_read' => false
                ]);
            }
            
            return redirect()->route('work-requests.show', $workRequest->id)
                ->with('success', 'Inspection scheduled successfully. Inspection Report #' . $inspectionReport->report_number . ' has been created and is pending completion.');
                
        } catch (\Exception $e) {
            Log::error('Error scheduling inspection: ' . $e->getMessage());
            return back()->with('error', 'Failed to schedule inspection: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Remove the specified work request from storage.
     */
    public function destroy(WorkRequest $workRequest)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        $workRequest->delete();
        
        return redirect()->route('work-requests.index')
            ->with('success', 'Work request deleted successfully.');
    }
}