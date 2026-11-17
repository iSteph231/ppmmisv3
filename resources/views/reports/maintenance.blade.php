@extends('layouts.app')

@section('title', 'Maintenance Report')

@section('content')
    <div class="content-wrapper">
        <div class="greeting-section">
            <h1 class="greeting-title">Maintenance Report</h1>
            <p class="greeting-subtitle">View and filter maintenance schedules</p>
        </div>

        <div class="mb-6">
            <x-filter-bar :action="route('reports.maintenance')" class="filter-bar--report">
                <div>
                    <label for="filter-status">Status</label>
                    <select name="status" class="form-control" id="filter-status">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div>
                    <label for="filter-date_from">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" id="filter-date_from">
                </div>

                <div>
                    <label for="filter-date_to">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" id="filter-date_to">
                </div>

                <div>
                    <label for="report-month">Whole month (optional)</label>
                    <input type="month" id="report-month" name="month" value="{{ request('month') }}" class="form-control" aria-describedby="report-month-help">
                </div>
                <x-slot:help><span id="report-month-help">Selecting a month overrides Date From and Date To. PDF exports include completed records only.</span></x-slot:help>
                <x-slot:actions>
                    <button type="submit" formaction="{{ route('reports.maintenance.export-pdf') }}" class="btn-create">Export PDF</button>
                </x-slot:actions>

            </x-filter-bar>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Total Schedules</h3>
                <p style="font-size: 2rem; font-weight: bold; color: #3b82f6;">{{ $totalSchedules ?? 0 }}</p>
            </div>
            <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Scheduled</h3>
                <p style="font-size: 2rem; font-weight: bold; color: #f59e0b;">{{ $pendingSchedules ?? 0 }}</p>
            </div>
            <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">In Progress</h3>
                <p style="font-size: 2rem; font-weight: bold; color: #8b5cf6;">{{ $inProgressSchedules ?? 0 }}</p>
            </div>
            <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Completed</h3>
                <p style="font-size: 2rem; font-weight: bold; color: #059669;">{{ $completedSchedules ?? 0 }}</p>
            </div>
        </div>

        <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="mobile-summary-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 1rem; text-align: left;">Scheduled Date</th>
                            <th style="padding: 1rem; text-align: left;">Activity</th>
                            <th style="padding: 1rem; text-align: left;">In-Charge</th>
                            <th style="padding: 1rem; text-align: left;">Engineer</th>
                            <th style="padding: 1rem; text-align: left;">Status</th>
                            <th style="padding: 1rem; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenanceSchedules as $schedule)
                            <tr data-request-url="{{ route('maintenance.show', $schedule->id) }}" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 1rem;">{{ $schedule->scheduled_date ? \Carbon\Carbon::parse($schedule->scheduled_date)->format('M d, Y') : 'N/A' }}</td>
                                <td class="mobile-summary-field" style="padding: 1rem;">{{ $schedule->activity ?? 'N/A' }}</td>
                                <td class="mobile-summary-field" style="padding: 1rem;">{{ $schedule->maintenance_in_charge ?? 'N/A' }}</td>
                                <td style="padding: 1rem;">{{ $schedule->engineer_in_charge ?? 'N/A' }}</td>
                                <td style="padding: 1rem;">
                                    @if($schedule->status == 'pending')
                                        <span style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">Scheduled</span>
                                    @elseif($schedule->status == 'in_progress')
                                        <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">In Progress</span>
                                    @elseif($schedule->status == 'completed')
                                        <span style="background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">Completed</span>
                                    @else
                                        <span style="background: #f3f4f6; color: #374151; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">{{ ucfirst($schedule->status ?? 'N/A') }}</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <a href="{{ route('maintenance.show', $schedule->id) }}" style="color: #3b82f6; text-decoration: none;">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">No maintenance schedules found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($maintenanceSchedules) && method_exists($maintenanceSchedules, 'links'))
                <div style="padding: 1rem; border-top: 1px solid #e5e7eb;">
                    {{ $maintenanceSchedules->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
