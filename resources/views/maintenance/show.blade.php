@extends('layouts.app')

@section('title', 'Maintenance Schedule Details')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Maintenance Schedule Details</h1>
        <p class="greeting-subtitle">Completed maintenance information</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem;">
            {{-- Status Banner --}}
            <div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.5rem; background: #d1fae5;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <strong>Status:</strong> 
                        <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem; background: #059669; color: white;">
                            Completed
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
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
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