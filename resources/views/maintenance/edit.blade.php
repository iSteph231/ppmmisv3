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

                    {{-- Remarks --}}
                    <div style="grid-column: span 2;">
                        <label for="remarks" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">REMARKS</label>
                        <textarea name="remarks" id="remarks" rows="2" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">{{ old('remarks', $schedule->remarks) }}</textarea>
                        @error('remarks')
                            <p style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <button type="submit" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; border: none; cursor: pointer;">
                        Update Schedule
                    </button>
                    <a href="{{ route('maintenance.index') }}" style="background: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection