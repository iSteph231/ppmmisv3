<?php

namespace App\Http\Controllers;

use App\Http\Middleware\PreventDuplicateRequestSubmission;
use App\Models\InspectionReport;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkRequest;
use App\Services\FacilityImageStorage;
use App\Services\WorkRequestNumberAllocator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkRequestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware(PreventDuplicateRequestSubmission::class.':work-requests.index', only: ['store'])];
    }

    public function __construct(private FacilityImageStorage $images) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = WorkRequest::with('user', 'inspectionReport');

        if ($user->isAdmin()) {

        } else {

            $query->where('user_id', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('request_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $workRequests = $query->orderBy('created_at', 'desc')->paginate(10);

        $newRequestIds = $user->isAdmin()
            ? Notification::where('user_id', $user->id)->unread()
                ->where('related_type', WorkRequest::class)->where('title', 'New Work Request')
                ->whereIn('related_id', $workRequests->modelKeys())->pluck('related_id')->all()
            : [];

        return view('work-requests.index', compact('workRequests', 'newRequestIds'));
    }

    public function create()
    {
        return view('work-requests.create');
    }

    public function store(Request $request)
    {
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
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $data = [
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => null,
            'department' => $request->department,
            'building_name' => $request->building_name,
            'office_room' => $request->office_room,
            'work_type' => $request->request_type,
            'status' => 'pending',
        ];

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

        Log::info('Inserting work request:', $data);

        $photoPath = null;

        try {
            if ($request->hasFile('photo')) {
                $photoPath = $this->images->store($request->file('photo'), 'work-request-photos');
                if ($photoPath === false) {
                    throw new \RuntimeException('Unable to store the request photo.');
                }
                $data['photo_path'] = $photoPath;
            }

            $month = now()->format('Ym');
            DB::transaction(function () use ($data, $month): void {
                $data['request_number'] = app(WorkRequestNumberAllocator::class)->allocate($month);
                $workRequest = WorkRequest::create($data);
                Log::info('Work request created successfully with ID: '.$workRequest->id);

                Notification::create([
                    'user_id' => Auth::id(),
                    'title' => 'Work Request Created',
                    'message' => "Your work request #{$workRequest->id}: '{$workRequest->title}' has been submitted successfully.",
                    'type' => 'info',
                    'is_read' => false,
                ]);

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

            }, 5);

            return redirect()->route('work-requests.index')
                ->with('success', 'Work request created successfully.');
        } catch (\Throwable $e) {
            if ($photoPath) {
                try {
                    $this->images->delete($photoPath);
                } catch (\Throwable $cleanupException) {
                    report($cleanupException);
                }
            }
            Log::error('Error creating work request: '.$e->getMessage());

            return back()->with('error', 'Unable to submit your work request right now. Please try again.')->withInput();
        }
    }

    public function photo(WorkRequest $workRequest): StreamedResponse
    {
        abort_unless(Auth::user()->isAdmin() || Auth::id() === $workRequest->user_id, 403);
        abort_unless($workRequest->photo_path && $this->images->exists($workRequest->photo_path), 404);

        return $this->images->response($workRequest->photo_path);
    }

    public function show(WorkRequest $workRequest)
    {
        $user = Auth::user();

        $workRequest->load('inspectionReport', 'user');

        if ($user->isAdmin()) {
            Notification::where('user_id', $user->id)->unread()
                ->where('related_type', WorkRequest::class)->where('related_id', $workRequest->id)
                ->where('title', 'New Work Request')->update(['is_read' => true]);

            return view('work-requests.show', compact('workRequest'));
        }

        if ($user->id === $workRequest->user_id) {
            return view('work-requests.show', compact('workRequest'));
        }

        abort(403, 'Unauthorized action.');
    }

    public function edit(WorkRequest $workRequest): never
    {
        abort(403, 'Submitted work requests cannot be edited.');
    }

    public function update(Request $request, WorkRequest $workRequest): never
    {
        abort(403, 'Submitted work requests cannot be edited.');
    }

    public function schedule(Request $request, WorkRequest $workRequest)
    {

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

            if ($workRequest->inspectionReport) {
                return back()->with('error', 'An inspection report already exists for this work request. Cannot schedule another inspection.');
            }

            $workRequest->update([
                'scheduled_date' => $validated['scheduled_date'],
                'inspection_notes' => $validated['inspection_notes'] ?? null,
            ]);

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

            if ($workRequest->user_id !== null) {
                Notification::create([
                    'user_id' => $workRequest->user_id,
                    'title' => 'Inspection Scheduled',
                    'message' => "Your work request #{$workRequest->id}: '{$workRequest->title}' has been scheduled for inspection on ".Carbon::parse($validated['scheduled_date'])->format('F d, Y h:i A').". Inspection Report #{$inspectionReport->report_number} has been created and is pending completion.",
                    'type' => 'info',
                    'is_read' => false,
                ]);
            }

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
