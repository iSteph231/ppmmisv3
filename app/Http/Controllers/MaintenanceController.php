<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of preventive maintenance schedules.
     */
    public function index(Request $request)
    {
        // Check if user is admin or personnel
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'personnel'])) {
            abort(403, 'Unauthorized access. Only Admin and Personnel can access maintenance schedules.');
        }
        
        $query = MaintenanceSchedule::query();
        
        // Filter by month/year
        if ($request->filled('month')) {
            $monthYear = $request->month;
            $query->where('month_year', $monthYear)
                  ->orWhereYear('scheduled_date', substr($monthYear, 0, 4))
                  ->whereMonth('scheduled_date', substr($monthYear, 5, 2));
        }
        
        $maintenanceSchedules = $query->orderBy('scheduled_date', 'desc')->paginate(10);
        
        return view('maintenance.index', compact('maintenanceSchedules'));
    }
    
    /**
     * Show form to create new schedule.
     * Only Admin can create.
     */
    public function create()
    {
        // Only admin can create
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only Administrators can create maintenance schedules.');
        }
        
        return view('maintenance.create');
    }
    
    /**
     * Store a new schedule.
     * Only Admin can store.
     */
    public function store(Request $request)
    {
        // Only admin can store
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only Administrators can create maintenance schedules.');
        }
        
        $validated = $request->validate([
            'scheduled_date' => 'required|date',
            'activity' => 'required|string',
            'maintenance_in_charge' => 'nullable|string',
            'engineer_in_charge' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);
        
        $validated['month_year'] = Carbon::parse($validated['scheduled_date'])->format('Y-m');
        $validated['status'] = 'pending';
        
        $maintenanceSchedule = MaintenanceSchedule::create($validated);
        
        // Get all admin users
        $admins = User::where('role', 'admin')->get();
        
        // Format the scheduled date
        $formattedDate = Carbon::parse($validated['scheduled_date'])->format('F d, Y');
        
        // Create notification for each admin
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Preventive Maintenance Schedule',
                'message' => "A new preventive maintenance schedule has been added: {$validated['activity']} on {$formattedDate}.",
                'detail' => "Date: {$formattedDate}\nActivity: {$validated['activity']}\nIn-Charge: " . ($validated['maintenance_in_charge'] ?? 'Not assigned') . "\nEngineer: " . ($validated['engineer_in_charge'] ?? 'Not assigned'),
                'type' => 'maintenance',
                'is_read' => false,
                'related_id' => $maintenanceSchedule->id,
                'related_type' => MaintenanceSchedule::class,
            ]);
        }
        
        // Also notify personnel users if specified
        if (!empty($validated['maintenance_in_charge'])) {
            $personnel = User::where('role', 'personnel')
                ->where('name', 'like', '%' . $validated['maintenance_in_charge'] . '%')
                ->get();
            
            foreach ($personnel as $p) {
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
    }
    
    /**
     * Show form to edit schedule.
     * Only Admin can edit.
     */
    public function edit($id)
    {
        // Only admin can edit
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only Administrators can edit maintenance schedules.');
        }
        
        $schedule = MaintenanceSchedule::findOrFail($id);
        return view('maintenance.edit', compact('schedule'));
    }
    
    /**
     * Update schedule.
     * Only Admin can update.
     */
    public function update(Request $request, $id)
    {
        // Only admin can update
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only Administrators can update maintenance schedules.');
        }
        
        $schedule = MaintenanceSchedule::findOrFail($id);
        
        $validated = $request->validate([
            'scheduled_date' => 'required|date',
            'activity' => 'required|string',
            'maintenance_in_charge' => 'nullable|string',
            'engineer_in_charge' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);
        
        $validated['month_year'] = Carbon::parse($validated['scheduled_date'])->format('Y-m');
        
        $oldData = $schedule->getOriginal();
        $schedule->update($validated);
        
        // Get all admin users
        $admins = User::where('role', 'admin')->get();
        $formattedDate = Carbon::parse($validated['scheduled_date'])->format('F d, Y');
        
        // Check what changed
        $changes = [];
        if (isset($oldData['scheduled_date']) && $oldData['scheduled_date'] != $validated['scheduled_date']) {
            $changes[] = "Date: " . Carbon::parse($oldData['scheduled_date'])->format('F d, Y') . " → {$formattedDate}";
        }
        if (($oldData['activity'] ?? '') != $validated['activity']) {
            $changes[] = "Activity: {$oldData['activity']} → {$validated['activity']}";
        }
        if (($oldData['maintenance_in_charge'] ?? '') != $validated['maintenance_in_charge']) {
            $changes[] = "In-Charge: {$oldData['maintenance_in_charge']} → {$validated['maintenance_in_charge']}";
        }
        if (($oldData['engineer_in_charge'] ?? '') != $validated['engineer_in_charge']) {
            $changes[] = "Engineer: {$oldData['engineer_in_charge']} → {$validated['engineer_in_charge']}";
        }
        
        $changeMessage = implode(", ", $changes);
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Maintenance Schedule Updated',
                'message' => "A preventive maintenance schedule has been updated: {$changeMessage}",
                'detail' => "Schedule ID: {$schedule->id}\nChanges: {$changeMessage}",
                'type' => 'maintenance',
                'is_read' => false,
                'related_id' => $schedule->id,
                'related_type' => MaintenanceSchedule::class,
            ]);
        }
        
        // Notify the new maintenance in-charge
        if (!empty($validated['maintenance_in_charge']) && ($oldData['maintenance_in_charge'] ?? '') != $validated['maintenance_in_charge']) {
            $personnel = User::where('role', 'personnel')
                ->where('name', 'like', '%' . $validated['maintenance_in_charge'] . '%')
                ->get();
            
            foreach ($personnel as $p) {
                Notification::create([
                    'user_id' => $p->id,
                    'title' => 'Maintenance Task Reassigned',
                    'message' => "You have been assigned as the new Maintenance In-Charge for: {$validated['activity']} on {$formattedDate}.",
                    'detail' => "Date: {$formattedDate}\nActivity: {$validated['activity']}\nYour Role: Maintenance In-Charge",
                    'type' => 'assignment',
                    'is_read' => false,
                    'related_id' => $schedule->id,
                    'related_type' => MaintenanceSchedule::class,
                ]);
            }
        }
        
        return redirect()->route('maintenance.index')
            ->with('success', 'Preventive maintenance schedule updated successfully.');
    }
    
    /**
     * Delete schedule.
     * Only Admin can delete.
     */
    public function destroy($id)
    {
        // Only admin can delete
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only Administrators can delete maintenance schedules.');
        }
        
        $schedule = MaintenanceSchedule::findOrFail($id);
        
        // Get admins to notify about deletion
        $admins = User::where('role', 'admin')->get();
        $formattedDate = $schedule->scheduled_date ? Carbon::parse($schedule->scheduled_date)->format('F d, Y') : 'N/A';
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Maintenance Schedule Removed',
                'message' => "A preventive maintenance schedule has been removed: {$schedule->activity} on {$formattedDate}.",
                'detail' => "Date: {$formattedDate}\nActivity: {$schedule->activity}\nReason: Schedule deleted from system",
                'type' => 'maintenance',
                'is_read' => false,
                'related_id' => $schedule->id,
                'related_type' => MaintenanceSchedule::class,
            ]);
        }
        
        $schedule->delete();
        
        return redirect()->route('maintenance.index')
            ->with('success', 'Preventive maintenance schedule deleted successfully.');
    }
    
    /**
     * Mark maintenance as completed.
     * Both Admin and assigned Personnel can mark as completed.
     */
    public function complete($id, Request $request)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        $user = Auth::user();
        
        // Check if user is admin OR the assigned maintenance in-charge
        $isAuthorized = ($user->role === 'admin') || 
                        ($user->role === 'personnel' && 
                         $schedule->maintenance_in_charge && 
                         stripos($schedule->maintenance_in_charge, $user->name) !== false);
        
        if (!$isAuthorized) {
            abort(403, 'Unauthorized. Only Administrators or the assigned Maintenance In-Charge can mark this as completed.');
        }
        
        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
        ]);
        
        $schedule->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $validated['completion_notes'] ?? null,
        ]);
        
        // Notify admins about completion
        $admins = User::where('role', 'admin')->get();
        $formattedDate = $schedule->scheduled_date ? Carbon::parse($schedule->scheduled_date)->format('F d, Y') : 'N/A';
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Maintenance Completed',
                'message' => "Preventive maintenance has been completed: {$schedule->activity} on {$formattedDate}.",
                'detail' => "Date: {$formattedDate}\nActivity: {$schedule->activity}\nCompleted by: {$user->name}\nCompletion Notes: " . ($validated['completion_notes'] ?? 'No additional notes'),
                'type' => 'success',
                'is_read' => false,
                'related_id' => $schedule->id,
                'related_type' => MaintenanceSchedule::class,
            ]);
        }
        
        // Notify the maintenance in-charge (if different from completer)
        if (!empty($schedule->maintenance_in_charge) && $user->role !== 'personnel') {
            $personnel = User::where('role', 'personnel')
                ->where('name', 'like', '%' . $schedule->maintenance_in_charge . '%')
                ->get();
            
            foreach ($personnel as $p) {
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
            ->with('success', 'Maintenance marked as completed.');
    }
}