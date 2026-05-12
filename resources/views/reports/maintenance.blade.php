@extends('layouts.app')

@section('title', 'Maintenance Report')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Maintenance Report</h1>
        <p class="greeting-subtitle">View and filter maintenance schedules</p>
    </div>

    {{-- Filters --}}
    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; overflow: hidden;">
        <div style="padding: 1.5rem;">
            <form method="GET" action="{{ route('reports.maintenance') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Status</label>
                    <select name="status" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Date From</label>
                    <input type="date" name="date_from" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ request('date_from') }}">
                </div>

                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Date To</label>
                    <input type="date" name="date_to" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ request('date_to') }}">
                </div>

                <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                    <button type="submit" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; border: none; cursor: pointer;">Apply Filters</button>
                    <a href="{{ route('reports.maintenance') }}" style="background: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none;">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Total Schedules</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #3b82f6;">{{ $totalSchedules ?? 0 }}</p>
        </div>
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Pending</h3>
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

    {{-- Maintenance Table --}}
    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
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
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1rem;">{{ $schedule->scheduled_date ? \Carbon\Carbon::parse($schedule->scheduled_date)->format('M d, Y') : 'N/A' }}</td>
                        <td style="padding: 1rem;">{{ $schedule->activity ?? 'N/A' }}</td>
                        <td style="padding: 1rem;">{{ $schedule->maintenance_in_charge ?? 'N/A' }}</td>
                        <td style="padding: 1rem;">{{ $schedule->engineer_in_charge ?? 'N/A' }}</td>
                        <td style="padding: 1rem;">
                            @if($schedule->status == 'pending')
                                <span style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">Pending</span>
                            @elseif($schedule->status == 'in_progress')
                                <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">In Progress</span>
                            @elseif($schedule->status == 'completed')
                                <span style="background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">Completed</span>
                            @else
                                <span style="background: #f3f4f6; color: #374151; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">{{ ucfirst($schedule->status ?? 'N/A') }}</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <a href="{{ route('maintenance.edit', $schedule->id) }}" style="color: #3b82f6; text-decoration: none;">View</a>
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