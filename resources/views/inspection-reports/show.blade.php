@extends('layouts.app')

@section('title', 'Inspection Report')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Inspection Report #{{ $inspectionReport->report_number ?? 'N/A' }}</h1>
        <p class="greeting-subtitle">Detailed inspection information</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem;">
<div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.5rem; {{ $inspectionReport->status === 'approved' ? 'background: #d1fae5;' : ($inspectionReport->status === 'pending' ? 'background: #fef3c7;' : 'background: #fee2e2;') }}">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong>Report Status:</strong> 
            <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem; {{ $inspectionReport->status === 'approved' ? 'background: #059669; color: white;' : ($inspectionReport->status === 'pending' ? 'background: #d97706; color: white;' : 'background: #dc2626; color: white;') }}">
                {{ $inspectionReport->status === 'approved' ? 'Approved' : ucfirst($inspectionReport->status) }}
            </span>
        </div>
        <div>
            <strong>Report #:</strong> {{ $inspectionReport->report_number }}
        </div>
    </div>
</div>

            {{-- Request Information --}}
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Request Information</h2>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div><strong>Request #:</strong> {{ $inspectionReport->workRequest->request_number ?? 'N/A' }}</div>
                    <div><strong>Title:</strong> {{ $inspectionReport->workRequest->title ?? 'N/A' }}</div>
                    <div><strong>Requester:</strong> {{ $inspectionReport->workRequest->user->name ?? 'N/A' }}</div>
                    <div><strong>Work Request Status:</strong> {{ ucfirst($inspectionReport->workRequest->status ?? 'N/A') }}</div>
                </div>
            </div>
            
            {{-- Inspection Details --}}
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Inspection Details</h2>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div><strong>Scheduled Date:</strong> {{ $inspectionReport->scheduled_date ? \Carbon\Carbon::parse($inspectionReport->scheduled_date)->format('F d, Y h:i A') : 'N/A' }}</div>
                        <div><strong>Work Type:</strong> {{ ucfirst(str_replace('_', ' ', $inspectionReport->workRequest->work_type ?? 'N/A')) }}</div>
                        @if($inspectionReport->actual_inspection_date)
                        <div><strong>Actual Inspection Date:</strong> {{ \Carbon\Carbon::parse($inspectionReport->actual_inspection_date)->format('F d, Y h:i A') }}</div>
                        @endif
                        @if($inspectionReport->inspector)
                        <div><strong>Inspected By:</strong> {{ $inspectionReport->inspector->name }}</div>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Location --}}
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Location</h2>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                    <div><strong>Department:</strong> {{ $inspectionReport->workRequest->department ?? 'Not specified' }}</div>
                    <div style="margin-top: 0.5rem;"><strong>Building:</strong> {{ $inspectionReport->workRequest->building_name ?? 'Not specified' }}</div>
                    <div style="margin-top: 0.5rem;"><strong>Office/Room:</strong> {{ $inspectionReport->workRequest->office_room ?? 'Not specified' }}</div>
                </div>
            </div>
            
            {{-- Inspection Notes --}}
            @if($inspectionReport->inspection_notes)
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Inspection Notes</h2>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                    <p>{{ $inspectionReport->inspection_notes }}</p>
                </div>
            </div>
            @endif
            
            {{-- Work Request Description --}}
            @if($inspectionReport->workRequest->description)
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Work Request Description</h2>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                    <p>{{ $inspectionReport->workRequest->description }}</p>
                </div>
            </div>
            @endif
            
            {{-- Findings (if completed) --}}
            @if($inspectionReport->findings)
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Findings</h2>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                    <p>{{ $inspectionReport->findings }}</p>
                </div>
            </div>
            @endif
            
            {{-- Recommendations (if completed) --}}
            @if($inspectionReport->recommendations)
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Recommendations</h2>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                    <p>{{ $inspectionReport->recommendations }}</p>
                </div>
            </div>
            @endif
            
            {{-- Admin Notes (Admin only) --}}
            @if(Auth::user()->isAdmin() && $inspectionReport->workRequest->admin_notes)
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Admin Notes</h2>
                <div style="background: #fef3c7; padding: 1rem; border-radius: 0.5rem;">
                    <p>{{ $inspectionReport->workRequest->admin_notes }}</p>
                </div>
            </div>
            @endif
            
            {{-- Action Buttons --}}
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <a href="{{ route('inspections.index') }}" class="btn-cancel" style="background: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                    ← Back to Inspections
                </a>
                <a href="{{ route('work-requests.show', $inspectionReport->workRequest->id) }}" class="btn-create" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                    View Full Request
                </a>
                @if(Auth::user()->isAdmin() && $inspectionReport->status === 'pending')
                <a href="{{ route('inspections.complete-form', $inspectionReport->id) }}" class="btn-create" style="background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                    Complete Inspection
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection