@extends('layouts.app')

@section('title', 'Edit Preventive Maintenance Schedule')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Edit Preventive Maintenance Schedule</h1>
        <p class="greeting-subtitle">PANGASINAN STATE UNIVERSITY - Asingan Campus</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem;">
            <form method="POST" action="{{ route('maintenance.update', $schedule->id) }}">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    {{-- Date / Schedule --}}
                    <div>
                        <label for="scheduled_date" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">DATE / SCHEDULE *</label>
                        <input type="date" name="scheduled_date" id="scheduled_date" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('scheduled_date', $schedule->scheduled_date) }}" required>
                        @error('scheduled_date')
                            <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Preventive Maintenance Activity --}}
                    <div style="grid-column: span 2;">
                        <label for="activity" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">PREVENTIVE MAINTENANCE ACTIVITY *</label>
                        <textarea name="activity" id="activity" rows="3" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" required>{{ old('activity', $schedule->activity) }}</textarea>
                        @error('activity')
                            <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Maintenance In-Charge --}}
                    <div>
                        <label for="maintenance_in_charge" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">MAINTENANCE IN-CHARGE</label>
                        <input type="text" name="maintenance_in_charge" id="maintenance_in_charge" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('maintenance_in_charge', $schedule->maintenance_in_charge) }}">
                        @error('maintenance_in_charge')
                            <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Engineer In-Charge --}}
                    <div>
                        <label for="engineer_in_charge" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">ENGINEER-IN CHARGE</label>
                        <input type="text" name="engineer_in_charge" id="engineer_in_charge" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('engineer_in_charge', $schedule->engineer_in_charge) }}">
                        @error('engineer_in_charge')
                            <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remarks field removed from main form --}}
                </div>

                {{-- Buttons --}}
                <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <button type="submit" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; border: none; cursor: pointer;">
                        Update Schedule
                    </button>
                    
                    @if($schedule->status !== 'completed')
                    <button type="button" onclick="showCompleteModal()" style="background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; border: none; cursor: pointer;">
                        ✓ Mark as Complete
                    </button>
                    @else
                    <span style="background: #d1fae5; color: #065f46; padding: 0.5rem 1rem; border-radius: 0.5rem;">
                        ✓ Already Completed
                    </span>
                    @endif
                    
                    <a href="{{ route('maintenance.index') }}" style="background: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Complete Modal with Remarks --}}
<div id="completeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; border-radius: 1rem; width: 500px; max-width: 90%; padding: 1.5rem;">
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Complete Maintenance</h3>
        <p style="color: #6b7280; margin-bottom: 1rem; font-size: 0.875rem;">This action will mark the maintenance schedule as completed.</p>
        
        <form method="POST" action="{{ route('maintenance.complete', $schedule->id) }}">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom: 1rem;">
                <label for="completion_notes" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">REMARKS / COMPLETION NOTES</label>
                <textarea name="completion_notes" id="completion_notes" rows="4" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;" placeholder="Enter completion details, findings, or additional notes..."></textarea>
                <small style="display: block; color: #6b7280; font-size: 0.75rem; margin-top: 0.25rem;">Optional: Add any remarks about the completed maintenance</small>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeCompleteModal()" style="background: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; border: none; cursor: pointer;">Cancel</button>
                <button type="submit" style="background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; border: none; cursor: pointer;">Confirm Complete</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCompleteModal() {
    document.getElementById('completeModal').style.display = 'flex';
}

function closeCompleteModal() {
    document.getElementById('completeModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('completeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCompleteModal();
    }
});
</script>
@endsection