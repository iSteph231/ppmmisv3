@extends('layouts.app')

@section('title', 'Preventive Maintenance Schedule')

@section('content')
    <div class="content-wrapper">
        <div class="greeting-section">
            <h1 class="greeting-title">Preventive Maintenance Schedule</h1>
            <p class="greeting-subtitle">PANGASINAN STATE UNIVERSITY - Asingan Campus</p>
        </div>

        <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <x-filter-bar :action="route('maintenance.index')">
                <div>
                    <label for="filter-month">For the month of</label>
                    <input type="month" name="month" id="filter-month" class="search-input" value="{{ request('month', now()->format('Y-m')) }}">
                </div>

            </x-filter-bar>

            <div style="margin-left: auto;">
                <form method="GET" action="{{ route('reports.maintenance.export-pdf') }}" style="display: inline-flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
                    <label for="export-month">Export month</label>
                    <input type="month" id="export-month" name="month" value="{{ request('month') ?: now()->format('Y-m') }}" required class="search-input">
                    <button type="submit" class="btn-create">Export PDF</button>
                </form>
                @if(Auth::user()->isEngineer())
                    <a href="{{ route('maintenance.create') }}" class="btn-create" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none;">
                        + Add Schedule
                    </a>
                @endif
            </div>
        </div>

        <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="mobile-summary-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
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
                            <tr data-request-url="{{ route('maintenance.show', $schedule->id) }}" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.875rem 1rem;">
                                    {{ $schedule->scheduled_date ? \Carbon\Carbon::parse($schedule->scheduled_date)->format('F d, Y') : 'N/A' }}
                                </td>
                                <td class="mobile-summary-field" style="padding: 0.875rem 1rem;">{{ $schedule->activity ?? 'N/A' }}</td>
                                <td class="mobile-summary-field" style="padding: 0.875rem 1rem;">{{ $schedule->maintenance_in_charge ?? 'N/A' }}</td>
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
                                            Scheduled
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

                                    <a href="{{ route('maintenance.show', $schedule->id) }}" class="action-btn view-btn" title="View" style="color: #8b5cf6; margin-right: 0.5rem;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

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
