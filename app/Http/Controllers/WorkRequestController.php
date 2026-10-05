<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('request_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
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
            'request_type' => ['required', 'string', Rule::in(['ocular_inspection', 'installation', 'repair', 'replacement', 'others'])],
            'ocular_location' => 'required_if:request_type,ocular_inspection|nullable|string',
            'installation_item' => 'required_if:request_type,installation|nullable|string',
            'repair_item' => 'required_if:request_type,repair|nullable|string',
            'replacement_item' => 'required_if:request_type,replacement|nullable|string',
            'others_specify' => 'required_if:request_type,others|nullable|string',
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

        // Prepare data for insertion
        $data = [
            'request_number' => "WR-{$yearMonth}-{$newNumber}",
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => null,
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
            Log::info('Work request created successfully with ID: '.$workRequest->id);

            // ==============================================
            // CREATE NOTIFICATIONS
            // ==============================================

            // 1. Create notification for the user who created the request
            Notification::create([
                'user_id' => Auth::id(),
                'title' => 'Work Request Created',
                'message' => "Your work request #{$workRequest->id}: '{$workRequest->title}' has been submitted successfully.",
                'type' => 'info',
                'is_read' => false,
            ]);

            // 2. Create notification for all admin users
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'New Work Request',
                    'message' => "New work request #{$workRequest->id}: '{$workRequest->title}' from ".Auth::user()->name,
                    'type' => 'info',
                    'is_read' => false,
                ]);
            }

            return redirect()->route('work-requests.index')
                ->with('success', 'Work request created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating work request: '.$e->getMessage());

            return back()->with('error', 'Failed to create work request: '.$e->getMessage())->withInput();
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
        $user = Auth::user();

        if (! $user->isUser() || $user->id !== $workRequest->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('work-requests.edit', compact('workRequest'));
    }

    /**
     * Update the specified work request in storage.
     */
    public function update(Request $request, WorkRequest $workRequest)
    {
        $user = Auth::user();

        if (! $user->isUser() || $user->id !== $workRequest->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'office_room' => 'nullable|string|max:255',
            'request_type' => ['required', 'string', Rule::in(['ocular_inspection', 'installation', 'repair', 'replacement', 'others'])],
            'ocular_location' => 'required_if:request_type,ocular_inspection|nullable|string',
            'installation_item' => 'required_if:request_type,installation|nullable|string',
            'repair_item' => 'required_if:request_type,repair|nullable|string',
            'replacement_item' => 'required_if:request_type,replacement|nullable|string',
            'others_specify' => 'required_if:request_type,others|nullable|string',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => null,
            'department' => $validated['department'] ?? null,
            'building_name' => $validated['building_name'] ?? null,
            'office_room' => $validated['office_room'] ?? null,
            'work_type' => $validated['request_type'],
            'ocular_details' => null,
            'installation_details' => null,
            'repair_details' => null,
            'replacement_details' => null,
            'others_details' => null,
        ];

        if ($validated['request_type'] === 'ocular_inspection') {
            $data['ocular_details'] = $validated['ocular_location'] ?? null;
        } elseif ($validated['request_type'] === 'installation') {
            $data['installation_details'] = $validated['installation_item'] ?? null;
        } elseif ($validated['request_type'] === 'repair') {
            $data['repair_details'] = $validated['repair_item'] ?? null;
        } elseif ($validated['request_type'] === 'replacement') {
            $data['replacement_details'] = $validated['replacement_item'] ?? null;
        } elseif ($validated['request_type'] === 'others') {
            $data['others_details'] = $validated['others_specify'] ?? null;
        }

        $workRequest->update($data);

        return redirect()->route('work-requests.show', $workRequest)
            ->with('success', 'Work request updated successfully.');
    }

    /**
     * Schedule inspection for the specified work request.
     * This will automatically create an inspection report in pending status.
     */
    public function schedule(Request $request, WorkRequest $workRequest)
    {
        // Only admin can schedule inspection
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'scheduled_date' => 'required|date|after:now',
            'inspection_notes' => 'nullable|string',
        ]);

        try {
            if ($workRequest->isInspectionScheduled()) {
                return back()->with('error', 'This work request has already been scheduled for inspection.');
            }

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
                'status' => InspectionReport::STATUS_PENDING,
            ]);

            Log::info('Inspection report created automatically', [
                'report_number' => $inspectionReport->report_number,
                'work_request_id' => $workRequest->id,
                'work_request_number' => $workRequest->request_number,
            ]);

            // Create notification for the requester
            Notification::create([
                'user_id' => $workRequest->user_id,
                'title' => 'Inspection Scheduled',
                'message' => "Your work request #{$workRequest->id}: '{$workRequest->title}' has been scheduled for inspection on ".Carbon::parse($validated['scheduled_date'])->format('F d, Y h:i A').". Inspection Report #{$inspectionReport->report_number} has been created and is pending completion.",
                'type' => 'info',
                'is_read' => false,
            ]);

            // Also notify all admins about the schedule
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Inspection Scheduled',
                    'message' => "Inspection scheduled for work request #{$workRequest->id}: '{$workRequest->title}' on ".Carbon::parse($validated['scheduled_date'])->format('F d, Y h:i A').". Report #{$inspectionReport->report_number} created and pending.",
                    'type' => 'info',
                    'is_read' => false,
                ]);
            }

            return redirect()->route('work-requests.show', $workRequest->id)
                ->with('success', 'Inspection scheduled successfully. Inspection Report #'.$inspectionReport->report_number.' has been created and is pending completion.');

        } catch (\Exception $e) {
            Log::error('Error scheduling inspection: '.$e->getMessage());

            return back()->with('error', 'Failed to schedule inspection: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified work request from storage.
     */
    public function destroy(WorkRequest $workRequest)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $workRequest->delete();

        return redirect()->route('work-requests.index')
            ->with('success', 'Work request deleted successfully.');
    }
}
