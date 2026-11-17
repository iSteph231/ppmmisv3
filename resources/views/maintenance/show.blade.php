@extends('layouts.app')

@section('title', 'Maintenance Schedule Details')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Maintenance Schedule Details</h1>
        <p class="greeting-subtitle">Maintenance schedule and completion information</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem;">
            {{-- Status Banner --}}
            <div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.5rem; background: {{ match($schedule->status) { 'completed' => '#d1fae5', 'in_progress' => '#dbeafe', 'cancelled' => '#fee2e2', default => '#fef3c7' } }};">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <strong>Status:</strong> 
                        <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem; background: {{ match($schedule->status) { 'completed' => '#059669', 'in_progress' => '#1e40af', 'cancelled' => '#991b1b', default => '#92400e' } }}; color: white;">
                            {{ match($schedule->status) { 'completed' => 'Completed', 'in_progress' => 'In Progress', 'cancelled' => 'Cancelled', default => 'Scheduled' } }}
                        </span>
                    </div>
                    <div>
                        <strong>Completed Date:</strong> {{ $schedule->completed_at ? \Carbon\Carbon::parse($schedule->completed_at)->format('F d, Y h:i A') : 'N/A' }}
                    </div>
                </div>
            </div>

            {{-- Schedule Information --}}
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Schedule Information</h2>
                <div class="responsive-grid-two" style="display: grid; gap: 1rem;">
                    <div><strong>Scheduled Date:</strong> {{ $schedule->scheduled_date ? \Carbon\Carbon::parse($schedule->scheduled_date)->format('F d, Y') : 'N/A' }}</div>
                    <div><strong>Activity:</strong> {{ $schedule->activity ?? 'N/A' }}</div>
                    <div><strong>Maintenance In-Charge:</strong> {{ $schedule->maintenance_in_charge ?? 'N/A' }}</div>
                    <div><strong>Engineer In-Charge:</strong> {{ $schedule->engineer_in_charge ?? 'N/A' }}</div>
                </div>
            </div>
            
            {{-- Completion Notes / Remarks --}}
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Completion Notes / Remarks</h2>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                    <p>{{ $schedule->completion_notes ?? $schedule->remarks ?? 'No completion notes provided.' }}</p>
                </div>
            </div>
            
            @if(Auth::user()->isEngineer() && in_array($schedule->status, ['pending', 'in_progress'], true))
                <button type="button" class="btn-create" data-open-maintenance-completion aria-haspopup="dialog" aria-controls="maintenance-completion-modal">Mark as Complete</button>

                <dialog id="maintenance-completion-modal" class="maintenance-completion-modal" aria-labelledby="maintenance-completion-title" aria-describedby="maintenance-completion-description" @if($errors->has('completion_notes')) data-reopen @endif>
                    <form method="POST" action="{{ route('maintenance.complete', $schedule->id) }}">
                        @csrf
                        @method('PUT')
                        <h2 id="maintenance-completion-title">Complete maintenance report?</h2>
                        <p id="maintenance-completion-description">Confirm that this maintenance has been finished. The report will be marked as completed and the completion date will be recorded.</p>
                        <label for="completion_notes">Completion notes <span>(optional)</span></label>
                        <textarea id="completion_notes" name="completion_notes" maxlength="1000" rows="5" placeholder="Describe the work completed or add any remarks." autofocus @error('completion_notes') aria-invalid="true" aria-describedby="completion-notes-error" @enderror>{{ old('completion_notes') }}</textarea>
                        @error('completion_notes')
                            <p id="completion-notes-error" class="maintenance-completion-error" role="alert">{{ $message }}</p>
                        @enderror
                        <p class="maintenance-completion-hint">Up to 1,000 characters.</p>
                        <div class="maintenance-completion-actions">
                            <button type="button" class="maintenance-completion-cancel" data-close-maintenance-completion>Cancel</button>
                            <button type="submit" class="maintenance-completion-confirm">Confirm Completion</button>
                        </div>
                    </form>
                </dialog>

                @push('styles')
                    <link rel="stylesheet" href="{{ asset('css/maintenance-completion.css').'?v='.substr(hash_file('sha256', public_path('css/maintenance-completion.css')), 0, 16) }}">
                @endpush
                @push('scripts')
                    <script defer src="{{ asset('js/maintenance-completion.js').'?v='.substr(hash_file('sha256', public_path('js/maintenance-completion.js')), 0, 16) }}"></script>
                @endpush
            @endif

            {{-- Back Button --}}
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <a href="{{ route('maintenance.index') }}" style="background: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                    ← Back to Maintenance Records
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
