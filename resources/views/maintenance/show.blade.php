@extends('layouts.app')

@section('title', 'Maintenance Record')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Maintenance Record</h1>
        <p class="greeting-subtitle">Completed maintenance details</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem;">
            {{-- Status Banner --}}
            <div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.5rem; background: #d1fae5;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>Status:</strong> 
                        <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem; background: #059669; color: white;">
                            Completed
                        </span>
                    </div>
                    <div>
                        <strong>Completed Date:</strong> {{ $record->completed_at ? \Carbon\Carbon::parse($record->completed_at)->format('F d, Y h:i A') : 'N/A' }}
                    </div>
                </div>
            </div>

            {{-- Request Information --}}
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Request Information</h2>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div><strong>Request #:</strong> {{ $record->request_number ?? 'N/A' }}</div>
                    <div><strong>Title:</strong> {{ $record->title ?? 'N/A' }}</div>
                    <div><strong>Requester:</strong> {{ $record->user->name ?? 'N/A' }}</div>
                    <div><strong>Work Type:</strong> {{ ucfirst(str_replace('_', ' ', $record->work_type ?? 'N/A')) }}</div>
                </div>
            </div>
            
            {{-- Location --}}
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Location</h2>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                    <div><strong>Department:</strong> {{ $record->department ?? 'Not specified' }}</div>
                    <div style="margin-top: 0.5rem;"><strong>Building:</strong> {{ $record->building_name ?? 'Not specified' }}</div>
                    <div style="margin-top: 0.5rem;"><strong>Office/Room:</strong> {{ $record->office_room ?? 'Not specified' }}</div>
                </div>
            </div>
            
            {{-- Description --}}
            @if($record->description)
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Description</h2>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                    <p>{{ $record->description }}</p>
                </div>
            </div>
            @endif
            
            {{-- Back Button --}}
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <a href="{{ route('maintenance.index') }}" class="btn-cancel" style="background: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                    ← Back to Maintenance Records
                </a>
                <a href="{{ route('work-requests.show', $record->id) }}" class="btn-create" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                    View Work Request
                </a>
            </div>
        </div>
    </div>
</div>
@endsection