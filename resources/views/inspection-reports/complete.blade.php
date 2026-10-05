@extends('layouts.app')

@section('title', 'Complete Inspection Report')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Complete Inspection Report</h1>
        <p class="greeting-subtitle">Enter findings, observations, and recommendations to approve this work request</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem;">
            <form method="POST" action="{{ route('inspections.complete', $inspectionReport->id) }}">
                @csrf
                
                {{-- Work Request Info --}}
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f9fafb; border-radius: 0.5rem;">
                    <h3 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Work Request Details</h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                        <div><strong>Request #:</strong> {{ $inspectionReport->workRequest->request_number }}</div>
                        <div><strong>Title:</strong> {{ $inspectionReport->workRequest->title }}</div>
                        <div><strong>Scheduled Date:</strong> {{ \Carbon\Carbon::parse($inspectionReport->scheduled_date)->format('F d, Y h:i A') }}</div>
                        <div><strong>Requester:</strong> {{ $inspectionReport->workRequest->user->name ?? 'N/A' }}</div>
                    </div>
                </div>
                
                {{-- Observation --}}
                <div style="margin-bottom: 1.5rem;">
                    <label for="observation" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Observation *</label>
                    <textarea name="observation" id="observation" rows="5" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-family: inherit;" required>{{ old('observation') }}</textarea>
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Record observations made during the inspection</p>
                </div>

                {{-- Findings --}}
                <div style="margin-bottom: 1.5rem;">
                    <label for="findings" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Findings *</label>
                    <textarea name="findings" id="findings" rows="5" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-family: inherit;" required>{{ old('findings') }}</textarea>
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Describe what was found during the inspection</p>
                </div>
                
                {{-- Recommendations --}}
                <div style="margin-bottom: 1.5rem;">
                    <label for="recommendations" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Recommendations *</label>
                    <textarea name="recommendations" id="recommendations" rows="5" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-family: inherit;" required>{{ old('recommendations') }}</textarea>
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Provide recommendations based on the findings</p>
                </div>
                
                <input type="hidden" name="status" value="approved">

                <div style="margin-bottom: 1.5rem; padding: 1rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.5rem; color: #065f46;">
                    <strong>Note:</strong> Submitting this report will mark the inspection report as "Approved" and the work request as "Approved".
                </div>
                
                {{-- Buttons --}}
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <button type="submit" style="background: #10b981; color: white; padding: 0.6rem 1.25rem; border-radius: 0.5rem; border: none; cursor: pointer; font-weight: 500;">
                        Submit Report & Approve Request
                    </button>
                    <a href="{{ route('inspections.show', $inspectionReport->id) }}" style="background: #6b7280; color: white; padding: 0.6rem 1.25rem; border-radius: 0.5rem; text-decoration: none;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
