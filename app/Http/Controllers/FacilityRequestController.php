<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacilityEvaluationRequest;
use App\Http\Requests\StoreFacilityRequest;
use App\Models\FacilityRequest;
use App\Models\Notification;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacilityRequestController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'finished', 'declined'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $user = Auth::user();
        $query = FacilityRequest::with('user')->latest();

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $summary = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'finished' => (clone $query)->where('status', 'finished')->count(),
            'declined' => (clone $query)->where('status', 'declined')->count(),
        ];

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($query) use ($search): void {
                $query->where('request_number', 'like', '%'.$search.'%')
                    ->orWhere('facility', 'like', '%'.$search.'%');
            });
        }
        $facilityRequests = $query->paginate(10)->withQueryString();

        $outstandingRequest = $user->isUser()
            ? FacilityRequest::where('user_id', $user->id)->where('status', 'approved')->first()
            : null;

        return view('request-facility', compact('facilityRequests', 'summary', 'outstandingRequest'));
    }

    public function show(FacilityRequest $facilityRequest): View
    {
        abort_unless(Auth::user()->isAdmin() || $facilityRequest->user_id === Auth::id(), 403);

        $facilityRequest->load('user');

        return view('facility-requests.show', compact('facilityRequest'));
    }

    public function exportPdf(FacilityRequest $facilityRequest): Response
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_unless($facilityRequest->status === 'finished', 409, 'Only finished facility requests can be exported.');

        $facilityRequest->load('user');
        $logoPath = public_path('images/inventory/convenience-outlet.png');
        $logo = is_file($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
        $photos = [];
        $disk = Storage::disk('local');
        foreach (['before', 'after'] as $stage) {
            $path = $facilityRequest->{$stage.'_photo_path'};
            $photos[$stage] = $path && $disk->exists($path)
                ? 'data:'.$disk->mimeType($path).';base64,'.base64_encode($disk->get($path))
                : null;
        }

        return Pdf::loadView('facility-requests.export-pdf', compact('facilityRequest', 'logo', 'photos'))
            ->setPaper('a4')
            ->download('facility-request-'.$facilityRequest->request_number.'.pdf')
            ->header('Cache-Control', 'private, no-store');
    }

    public function approve(FacilityRequest $facilityRequest): RedirectResponse
    {
        DB::transaction(function () use ($facilityRequest): void {
            User::whereKey($facilityRequest->user_id)->lockForUpdate()->firstOrFail();
            $facilityRequest = FacilityRequest::whereKey($facilityRequest->id)->lockForUpdate()->firstOrFail();
            abort_unless($facilityRequest->status === 'pending', 409, 'This request has already been reviewed.');

            $facilityRequest->update(['status' => 'approved']);
            Notification::create([
                'user_id' => $facilityRequest->user_id,
                'title' => 'Facility Request Approved',
                'message' => "Your facility request {$facilityRequest->request_number} for {$facilityRequest->facility} has been approved.",
                'type' => 'info',
                'is_read' => false,
                'related_id' => $facilityRequest->id,
                'related_type' => FacilityRequest::class,
            ]);
        });

        return redirect()->route('request-facility.show', $facilityRequest)
            ->with('success', 'Facility request approved successfully.');
    }

    public function create(): View|RedirectResponse
    {
        $outstanding = FacilityRequest::where('user_id', Auth::id())->where('status', 'approved')->first();
        if ($outstanding) {
            return redirect()->route('request-facility.photos', $outstanding)
                ->with('error', 'Upload both photos and complete the facility evaluation before requesting another facility.');
        }

        return view('facility-requests.create');
    }

    public function photos(FacilityRequest $facilityRequest): View
    {
        abort_unless($facilityRequest->user_id === Auth::id(), 403);
        abort_unless(in_array($facilityRequest->status, ['approved', 'finished'], true), 409);

        return view('facility-requests.photos', compact('facilityRequest'));
    }

    public function programImage(FacilityRequest $facilityRequest): StreamedResponse
    {
        abort_unless(Auth::user()->isAdmin() || $facilityRequest->user_id === Auth::id(), 403);
        $path = $facilityRequest->program_image_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, no-store']);
    }

    public function photo(FacilityRequest $facilityRequest, string $stage): StreamedResponse
    {
        abort_unless(Auth::user()->isAdmin() || $facilityRequest->user_id === Auth::id(), 403);
        abort_unless(in_array($stage, ['before', 'after'], true), 404);
        $path = $facilityRequest->{$stage.'_photo_path'};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, no-store']);
    }

    public function uploadPhotos(Request $request, FacilityRequest $facilityRequest): RedirectResponse
    {
        abort_unless($facilityRequest->user_id === Auth::id(), 403);
        abort_unless($facilityRequest->status === 'approved', 409);
        $request->validate([
            'before_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,bmp', 'max:5120'],
            'after_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,bmp', 'max:5120'],
        ]);
        if (! $request->hasFile('before_photo') && ! $request->hasFile('after_photo')) {
            throw ValidationException::withMessages(['before_photo' => 'Select at least one photo to upload.']);
        }

        $storedPaths = [];
        try {
            DB::transaction(function () use ($request, $facilityRequest, &$storedPaths): void {
                User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
                $facilityRequest = FacilityRequest::whereKey($facilityRequest->id)->lockForUpdate()->firstOrFail();
                abort_unless($facilityRequest->status === 'approved', 409);

                foreach (['before', 'after'] as $stage) {
                    if ($request->hasFile($stage.'_photo') && $facilityRequest->{$stage.'_photo_path'}) {
                        throw ValidationException::withMessages([$stage.'_photo' => 'This photo has already been uploaded. Only one photo per stage is allowed.']);
                    }
                }
                foreach (['before', 'after'] as $stage) {
                    if ($request->hasFile($stage.'_photo')) {
                        $photo = $request->file($stage.'_photo');
                        $filename = $facilityRequest->request_number.'-'.$stage.'.'.$photo->extension();
                        $path = $photo->storeAs($facilityRequest->photoDirectory(), $filename, 'local');
                        if (! $path) {
                            throw new \RuntimeException('Unable to store the facility photo.');
                        }
                        $storedPaths[] = $path;
                        $facilityRequest->{$stage.'_photo_path'} = $path;
                    }
                }
                $facilityRequest->save();
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        return redirect()->route('request-facility.photos', $facilityRequest)
            ->with('success', 'Photos saved. '.($facilityRequest->fresh()->status === 'finished' ? 'Your request is finished.' : 'After both photos are uploaded, complete the facility evaluation before requesting another facility.'));
    }

    public function storeEvaluation(StoreFacilityEvaluationRequest $request, FacilityRequest $facilityRequest): RedirectResponse
    {
        DB::transaction(function () use ($request, $facilityRequest): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $facilityRequest = FacilityRequest::whereKey($facilityRequest->id)->lockForUpdate()->firstOrFail();
            abort_unless($facilityRequest->status === 'approved' && $facilityRequest->before_photo_path && $facilityRequest->after_photo_path && ! $facilityRequest->evaluation, 409);

            $facilityRequest->update([
                'evaluation' => $request->validated() + ['submitted_at' => now()->toIso8601String()],
                'status' => 'finished',
            ]);
        });

        return redirect()->route('request-facility.photos', $facilityRequest)
            ->with('success', 'Evaluation submitted. Your request is finished. You can now request another facility.');
    }

    public function store(StoreFacilityRequest $request): RedirectResponse
    {
        $validated = $request->safe()->except('program_image');
        $storedPath = null;

        try {
            $facilityRequest = DB::transaction(function () use ($request, $validated, &$storedPath): FacilityRequest {
                User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                if (FacilityRequest::where('user_id', $request->user()->id)->where('status', 'approved')->exists()) {
                    throw ValidationException::withMessages(['facility' => 'Upload both photos and complete the facility evaluation before submitting another request.']);
                }

                $prefix = 'FR-'.now()->format('Ym').'-';
                $lastRequest = FacilityRequest::where('request_number', 'like', $prefix.'%')->latest('id')->first();
                $nextNumber = $lastRequest ? ((int) substr($lastRequest->request_number, -4)) + 1 : 1;

                if ($request->hasFile('program_image')) {
                    $storedPath = $request->file('program_image')->store('facility-requests/programs', 'local');
                    if (! $storedPath) {
                        throw new \RuntimeException('Unable to store the program or event planner image.');
                    }
                }

                $facilityRequest = FacilityRequest::create($validated + [
                    'user_id' => $request->user()->id,
                    'category' => 'Facility Use',
                    'request_number' => $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT),
                    'program_image_path' => $storedPath,
                    'status' => $storedPath ? 'pending' : 'declined',
                    'decline_reason' => $storedPath ? null : 'Automatically declined because no program or event planner image was attached.',
                ]);

                if ($facilityRequest->status === 'declined') {
                    Notification::create([
                        'user_id' => $request->user()->id,
                        'title' => 'Facility Request Declined',
                        'message' => "Your facility request {$facilityRequest->request_number} was automatically declined because no program or event planner image was attached. Submit a new request with the image.",
                        'type' => 'info',
                        'is_read' => false,
                        'related_id' => $facilityRequest->id,
                        'related_type' => FacilityRequest::class,
                    ]);
                } else {
                    foreach (User::where('role', 'admin')->get(['id']) as $admin) {
                        Notification::create([
                            'user_id' => $admin->id,
                            'title' => 'New Facility Request',
                            'message' => "Facility request {$facilityRequest->request_number} for {$facilityRequest->facility} from ".$request->user()->name,
                            'type' => 'info',
                            'is_read' => false,
                            'related_id' => $facilityRequest->id,
                            'related_type' => FacilityRequest::class,
                        ]);
                    }
                }

                return $facilityRequest;
            });
        } catch (\Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            throw $exception;
        }

        return redirect()->route('request-facility.index')
            ->with($facilityRequest->status === 'declined' ? 'error' : 'success',
                $facilityRequest->status === 'declined'
                    ? $facilityRequest->decline_reason.' Please submit a new request with the image.'
                    : 'Facility request submitted successfully.');
    }
}
