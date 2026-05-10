@extends('layouts.app')

@section('title', 'Work Request Details')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Work Request Details</h1>
        <p class="greeting-subtitle">View request information</p>
    </div>

    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1.5rem;">
            {{-- Request Information Grid --}}
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <strong style="color: #6b7280; font-size: 0.875rem;">Request #:</strong>
                    <p style="margin-top: 0.25rem; font-size: 1rem; font-family: monospace;">{{ $workRequest->request_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <strong style="color: #6b7280; font-size: 0.875rem;">Status:</strong>
                    <p style="margin-top: 0.25rem;">
                        @php
                            $statusClass = match($workRequest->status) {
                                'pending' => 'badge-pending',
                                'approved' => 'badge-approved',
                                'completed' => 'badge-completed',
                                default => 'badge-pending'
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}" style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                            {{ ucfirst($workRequest->status) }}
                        </span>
                    </p>
                </div>
                <div>
                    <strong style="color: #6b7280; font-size: 0.875rem;">Title:</strong>
                    <p style="margin-top: 0.25rem; font-size: 1rem;">{{ $workRequest->title }}</p>
                </div>
                <div>
                    <strong style="color: #6b7280; font-size: 0.875rem;">Work Type:</strong>
                    <p style="margin-top: 0.25rem; font-size: 1rem;">
                        @php
                            $workTypeLabels = [
                                'ocular_inspection' => 'Ocular Inspection',
                                'installation' => 'Installation',
                                'repair' => 'Repair',
                                'replacement' => 'Replacement',
                                'others' => 'Others'
                            ];
                        @endphp
                        {{ $workTypeLabels[$workRequest->work_type] ?? ucfirst(str_replace('_', ' ', $workRequest->work_type ?? 'N/A')) }}
                    </p>
                </div>
                <div>
                    <strong style="color: #6b7280; font-size: 0.875rem;">Submitted By:</strong>
                    <p style="margin-top: 0.25rem; font-size: 1rem;">{{ $workRequest->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <strong style="color: #6b7280; font-size: 0.875rem;">Date Submitted:</strong>
                    <p style="margin-top: 0.25rem; font-size: 1rem;">{{ $workRequest->created_at->format('F d, Y h:i A') }}</p>
                </div>
            </div>
            
            {{-- Scheduled Inspection Date - Shows for both Admin and Requester --}}
            @if($workRequest->scheduled_date)
            <div style="margin-bottom: 1.5rem; background: {{ Auth::user()->isAdmin() ? '#ecfdf5' : '#eff6ff' }}; padding: 1rem; border-radius: 0.5rem; border: 1px solid {{ Auth::user()->isAdmin() ? '#a7f3d0' : '#bfdbfe' }};">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{{ Auth::user()->isAdmin() ? '#059669' : '#2563eb' }}" stroke-width="2">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <strong style="color: {{ Auth::user()->isAdmin() ? '#065f46' : '#1e40af' }};">Scheduled Inspection:</strong>
                    <span style="color: {{ Auth::user()->isAdmin() ? '#065f46' : '#1e40af' }};">{{ \Carbon\Carbon::parse($workRequest->scheduled_date)->format('F d, Y h:i A') }}</span>
                </div>
                @if($workRequest->inspection_notes && Auth::user()->isAdmin())
                <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #a7f3d0;">
                    <strong style="color: #065f46; font-size: 0.75rem;">Inspection Notes:</strong>
                    <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #065f46;">{{ $workRequest->inspection_notes }}</p>
                </div>
                @endif
            </div>
            @endif
            
            {{-- Location Information Section --}}
            <div style="margin-bottom: 1.5rem;">
                <strong style="color: #6b7280; font-size: 0.875rem; display: block; margin-bottom: 0.75rem;">Location Information:</strong>
                <div style="background: #f9fafb; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div>
                            <strong style="color: #6b7280; font-size: 0.75rem;">Department:</strong>
                            <p style="margin-top: 0.25rem; font-size: 0.875rem;">{{ $workRequest->department ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <strong style="color: #6b7280; font-size: 0.75rem;">Building Name:</strong>
                            <p style="margin-top: 0.25rem; font-size: 0.875rem;">{{ $workRequest->building_name ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <strong style="color: #6b7280; font-size: 0.75rem;">Office / Room:</strong>
                            <p style="margin-top: 0.25rem; font-size: 0.875rem;">{{ $workRequest->office_room ?? 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Work Type Details --}}
            <div style="margin-bottom: 1.5rem;">
                <strong style="color: #6b7280; font-size: 0.875rem;">Request Details:</strong>
                <div style="margin-top: 0.5rem; background: #f9fafb; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                    @php
                        $details = '';
                        if ($workRequest->work_type === 'ocular_inspection') {
                            $details = $workRequest->ocular_details;
                        } elseif ($workRequest->work_type === 'installation') {
                            $details = $workRequest->installation_details;
                        } elseif ($workRequest->work_type === 'repair') {
                            $details = $workRequest->repair_details;
                        } elseif ($workRequest->work_type === 'replacement') {
                            $details = $workRequest->replacement_details;
                        } elseif ($workRequest->work_type === 'others') {
                            $details = $workRequest->others_details;
                        }
                    @endphp
                    <p style="margin: 0; line-height: 1.6;">
                        <strong>Item/Specification:</strong> {{ $details ?? 'No details provided' }}
                    </p>
                </div>
            </div>
            
            {{-- Additional Description --}}
            @if($workRequest->description)
            <div style="margin-bottom: 1.5rem;">
                <strong style="color: #6b7280; font-size: 0.875rem;">Additional Description:</strong>
                <div style="margin-top: 0.5rem; background: #f9fafb; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                    <p style="margin: 0; line-height: 1.6;">{{ $workRequest->description }}</p>
                </div>
            </div>
            @endif
            
            {{-- Admin Notes - Only visible to Admin --}}
            @if($workRequest->admin_notes && Auth::user()->isAdmin())
            <div style="margin-bottom: 1.5rem;">
                <strong style="color: #6b7280; font-size: 0.875rem;">Admin Notes:</strong>
                <div style="margin-top: 0.5rem; background: #fef3c7; padding: 1rem; border-radius: 0.5rem; border: 1px solid #fde68a;">
                    <p style="margin: 0; line-height: 1.6;">{{ $workRequest->admin_notes }}</p>
                </div>
            </div>
            @endif
            
            {{-- Completed Date --}}
            @if($workRequest->completed_at)
            <div style="margin-bottom: 1.5rem;">
                <strong style="color: #6b7280; font-size: 0.875rem;">Completed Date:</strong>
                <p style="margin-top: 0.25rem; font-size: 1rem;">{{ $workRequest->completed_at->format('F d, Y h:i A') }}</p>
            </div>
            @endif
            
            {{-- Action Buttons --}}
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; flex-wrap: wrap;">
                <a href="{{ route('work-requests.index') }}" class="btn-cancel" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; text-decoration: none; background: #6b7280; color: white; border-radius: 0.5rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;">
                        <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to List
                </a>
                
                {{-- Schedule Inspection Button - ADMIN ONLY --}}
                @if(Auth::user()->isAdmin())
                <button type="button" onclick="openScheduleModal()" class="btn-create" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; text-decoration: none; background: #8b5cf6; border: none; cursor: pointer;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        <path d="M12 11v4m-2-2h4"/>
                    </svg>
                    Schedule Inspection
                </button>
                @endif
                
                {{-- Edit Button - ADMIN ONLY --}}
                @if(Auth::user()->isAdmin())
                <a href="{{ route('work-requests.edit', $workRequest->id) }}" class="btn-create" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; text-decoration: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;">
                        <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Request
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Schedule Inspection Modal - ADMIN ONLY --}}
@if(Auth::user()->isAdmin())
<div id="scheduleModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 1rem; width: 90%; max-width: 500px; margin: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <form method="POST" action="{{ route('work-requests.schedule', $workRequest->id) }}" style="padding: 1.5rem;">
            @csrf
            @method('PUT')
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 600; color: #111827; margin: 0;">Schedule Inspection</h2>
                <button type="button" onclick="closeScheduleModal()" style="background: none; border: none; cursor: pointer; color: #6b7280;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                    Inspection Date & Time <span style="color: #ef4444;">*</span>
                </label>
                <input type="datetime-local" name="scheduled_date" id="scheduled_date" class="search-input" style="width: 100%;" required>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                    Inspection Notes (Optional - Internal)
                </label>
                <textarea name="inspection_notes" rows="3" class="search-input" style="width: 100%;" placeholder="Add internal notes for the inspection..."></textarea>
                <p style="font-size: 0.7rem; color: #6b7280; margin-top: 0.25rem;">These notes will only be visible to admins.</p>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closeScheduleModal()" class="btn-cancel" style="background: #9ca3af; border: none; cursor: pointer; padding: 0.5rem 1rem; border-radius: 0.5rem; color: white;">
                    Cancel
                </button>
                <button type="submit" class="btn-create" style="background: #8b5cf6; border: none; cursor: pointer; padding: 0.5rem 1rem; border-radius: 0.5rem; color: white;">
                    Schedule Inspection
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openScheduleModal() {
        document.getElementById('scheduleModal').style.display = 'flex';
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        const minDateTime = now.toISOString().slice(0, 16);
        document.getElementById('scheduled_date').min = minDateTime;
    }
    
    function closeScheduleModal() {
        document.getElementById('scheduleModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('scheduleModal');
        if (event.target === modal) {
            closeScheduleModal();
        }
    }
</script>
@endif
@endsection