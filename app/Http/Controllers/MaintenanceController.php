<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {

        $user = Auth::user();
        if (! $user || (! $user->isAdmin() && ! $user->isEngineer())) {
            abort(403, 'Only Administrators and Engineers can access maintenance schedules.');
        }
        $request->validate(['month' => ['nullable', 'date_format:Y-m']]);

        $query = $this->filteredMaintenanceSchedules($request);

        $maintenanceSchedules = $query->orderBy('scheduled_date', 'desc')->paginate(10);

        return view('maintenance.index', compact('maintenanceSchedules'));
    }

    public function create()
    {
        if (! Auth::user()->isEngineer()) {
            abort(403, 'Only Engineers can create maintenance schedules.');
        }

        return view('maintenance.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Auth::user()->isEngineer()) {
            abort(403, 'Only Engineers can create maintenance schedules.');
        }

        $validated = $request->validate([
            'scheduled_date' => 'required|date_format:Y-m-d',
            'activity' => 'required|string',
            'maintenance_in_charge' => 'nullable|string|max:100',
            'engineer_in_charge' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ]);

        $validated['month_year'] = Carbon::parse($validated['scheduled_date'])->format('Y-m');
        $validated['status'] = 'pending';

        return DB::transaction(function () use ($validated): RedirectResponse {
            $maintenanceSchedule = MaintenanceSchedule::create($validated);

            $admins = User::where('role', 'admin')->get();

            $formattedDate = Carbon::parse($validated['scheduled_date'])->format('F d, Y');

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'New Preventive Maintenance Schedule',
                    'message' => "A new preventive maintenance schedule has been added: {$validated['activity']} on {$formattedDate}.",
                    'detail' => "Date: {$formattedDate}\nActivity: {$validated['activity']}\nIn-Charge: ".($validated['maintenance_in_charge'] ?? 'Not assigned')."\nEngineer: ".($validated['engineer_in_charge'] ?? 'Not assigned'),
                    'type' => 'maintenance',
                    'is_read' => false,
                    'related_id' => $maintenanceSchedule->id,
                    'related_type' => MaintenanceSchedule::class,
                ]);
            }

            if (! empty($validated['maintenance_in_charge'])) {
                $assignedUsers = User::where('role', 'user')
                    ->where('name', 'like', '%'.$validated['maintenance_in_charge'].'%')
                    ->get();

                foreach ($assignedUsers as $p) {
                    Notification::create([
                        'user_id' => $p->id,
                        'title' => 'Maintenance Task Assigned',
                        'message' => "You have been assigned as Maintenance In-Charge for: {$validated['activity']} on {$formattedDate}.",
                        'detail' => "Date: {$formattedDate}\nActivity: {$validated['activity']}\nYour Role: Maintenance In-Charge",
                        'type' => 'assignment',
                        'is_read' => false,
                        'related_id' => $maintenanceSchedule->id,
                        'related_type' => MaintenanceSchedule::class,
                    ]);
                }
            }

            return redirect()->route('maintenance.index')
                ->with('success', 'Preventive maintenance schedule added successfully.');
        }, 5);
    }

    public function destroy(int $id): never
    {
        abort(403, 'Maintenance requests cannot be deleted.');
    }

    public function complete(int $id, Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isEngineer()) {
            abort(403, 'Only Engineers can complete maintenance reports.');
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($id, $user, $validated): RedirectResponse {
            $schedule = MaintenanceSchedule::query()->lockForUpdate()->findOrFail($id);
            abort_unless(in_array($schedule->status, ['pending', 'in_progress'], true), 409, 'Only scheduled or in-progress maintenance can be completed.');

            $schedule->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completion_notes' => $validated['completion_notes'] ?? null,
                'remarks' => $validated['completion_notes'] ?? $schedule->remarks,
            ]);

            $admins = User::where('role', 'admin')->get();
            $formattedDate = $schedule->scheduled_date ? Carbon::parse($schedule->scheduled_date)->format('F d, Y') : 'N/A';

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Maintenance Completed',
                    'message' => "Preventive maintenance has been completed: {$schedule->activity} on {$formattedDate}.",
                    'detail' => "Date: {$formattedDate}\nActivity: {$schedule->activity}\nCompleted by: {$user->name}\nCompletion Notes: ".($validated['completion_notes'] ?? 'No additional notes'),
                    'type' => 'success',
                    'is_read' => false,
                    'related_id' => $schedule->id,
                    'related_type' => MaintenanceSchedule::class,
                ]);
            }

            if (! empty($schedule->maintenance_in_charge) && $user->role !== 'user') {
                $assignedUsers = User::where('role', 'user')
                    ->where('name', 'like', '%'.$schedule->maintenance_in_charge.'%')
                    ->get();

                foreach ($assignedUsers as $p) {
                    Notification::create([
                        'user_id' => $p->id,
                        'title' => 'Task Completed',
                        'message' => "Your assigned maintenance task has been marked as completed: {$schedule->activity} on {$formattedDate}.",
                        'detail' => "Date: {$formattedDate}\nActivity: {$schedule->activity}\nStatus: Completed",
                        'type' => 'success',
                        'is_read' => false,
                        'related_id' => $schedule->id,
                        'related_type' => MaintenanceSchedule::class,
                    ]);
                }
            }

            return redirect()->route('maintenance.index')
                ->with('success', 'Maintenance marked as completed successfully.');
        }, 5);
    }

    public function show($id)
    {
        abort_unless(Auth::user()->isAdmin() || Auth::user()->isEngineer(), 403);
        $schedule = MaintenanceSchedule::findOrFail($id);

        return view('maintenance.show', compact('schedule'));
    }

    private function filteredMaintenanceSchedules(Request $request)
    {
        $query = MaintenanceSchedule::query();

        if ($request->filled('month')) {
            $month = Carbon::createFromFormat('Y-m', $request->month);

            $query->where(function ($maintenanceQuery) use ($request, $month) {
                $maintenanceQuery->where('month_year', $request->month)
                    ->orWhere(function ($dateQuery) use ($month) {
                        $dateQuery->whereYear('scheduled_date', $month->year)
                            ->whereMonth('scheduled_date', $month->month);
                    });
            });
        }

        return $query;
    }
}
