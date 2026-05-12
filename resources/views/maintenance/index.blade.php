@extends('layouts.app')

@section('title', 'Preventive Maintenance Schedule')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Preventive Maintenance Schedule</h1>
        <p class="greeting-subtitle">PANGASINAN STATE UNIVERSITY - Asingan Campus</p>
    </div>

    {{-- Month Selection --}}
    <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <form method="GET" action="{{ route('maintenance.index') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">For the month of</label>
                <input type="month" name="month" class="search-input" style="padding: 0.5rem 1rem;" value="{{ request('month', now()->format('Y-m')) }}" onchange="this.form.submit()">
            </div>
            
            @if(request()->anyFilled(['month']))
                <a href="{{ route('maintenance.index') }}" class="btn-cancel" style="background: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">Clear Filters</a>
            @endif
        </form>
        
        <div style="margin-left: auto;">
            <a href="{{ route('maintenance.create') }}" class="btn-create" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                + Add Schedule
            </a>
        </div>
    </div>

    {{-- Preventive Maintenance Schedule Table --}}
    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 0.875rem 1rem; text-align: left;">DATE / SCHEDULE</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">PREVENTIVE MAINTENANCE ACTIVITY</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">MAINTENANCE IN-CHARGE</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">ENGINEER-IN CHARGE</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">STATUS</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">REMARKS</th>
                        <th style="padding: 0.875rem 1rem; text-align: center;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenanceSchedules as $schedule)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.875rem 1rem;">
                            {{ $schedule->scheduled_date ? \Carbon\Carbon::parse($schedule->scheduled_date)->format('F d, Y') : 'N/A' }}
                        </td>
                        <td style="padding: 0.875rem 1rem;">{{ $schedule->activity ?? 'N/A' }}</td>
                        <td style="padding: 0.875rem 1rem;">{{ $schedule->maintenance_in_charge ?? 'N/A' }}</td>
                        <td style="padding: 0.875rem 1rem;">{{ $schedule->engineer_in_charge ?? 'N/A' }}</td>
                        <td style="padding: 0.875rem 1rem;">
                            @if($schedule->status == 'completed')
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #d1fae5; color: #065f46;">
                                    ✓ Completed
                                </span>
                            @elseif($schedule->status == 'in_progress')
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #dbeafe; color: #1e40af;">
                                    In Progress
                                </span>
                            @elseif($schedule->status == 'cancelled')
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #fee2e2; color: #991b1b;">
                                    Cancelled
                                </span>
                            @else
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #fef3c7; color: #92400e;">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td style="padding: 0.875rem 1rem; max-width: 200px; word-wrap: break-word;">
                            @if($schedule->status == 'completed')
                                {{ $schedule->completion_notes ?? $schedule->remarks ?? 'No remarks' }}
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                        <td style="padding: 0.875rem 1rem; text-align: center;">
                            @if($schedule->status == 'completed')
                                {{-- View Button for Completed --}}
                                <a href="{{ route('maintenance.show', $schedule->id) }}" class="action-btn view-btn" title="View" style="color: #8b5cf6; margin-right: 0.5rem;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            @else
                                {{-- Edit Button for Pending/In Progress --}}
                                <a href="{{ route('maintenance.edit', $schedule->id) }}" class="action-btn edit-btn" title="Edit" style="color: #3b82f6; margin-right: 0.5rem;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                        <path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4L18.5 2.5z"/>
                                    </svg>
                                </a>
                            @endif
                            
                            {{-- Delete Button (only for admin) --}}
                            @if(Auth::user()->role === 'admin')
                            <form method="POST" action="{{ route('maintenance.destroy', $schedule->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; cursor: pointer; color: #ef4444;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.5;">
                                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p style="margin: 0;">No preventive maintenance schedules found.</p>
                                <p style="margin: 0; font-size: 0.75rem;">Click "Add Schedule" to create one.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($maintenanceSchedules) && method_exists($maintenanceSchedules, 'links'))
        <div style="padding: 0.75rem 1.5rem; border-top: 1px solid #e5e7eb; background: #f9fafb;">
            {{ $maintenanceSchedules->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection