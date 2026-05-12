@extends('layouts.app')

@section('title', 'Performance Report')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Performance Report</h1>
        <p class="greeting-subtitle">System performance analytics and metrics</p>
    </div>

    {{-- Key Metrics Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Completion Rate</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #10b981;">{{ $completionRate ?? 0 }}%</p>
        </div>
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Avg. Completion Time</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #3b82f6;">{{ $avgCompletionTime ?? 0 }} <span style="font-size: 1rem;">days</span></p>
        </div>
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Active Personnel</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #8b5cf6;">{{ $activePersonnel ?? 0 }}</p>
        </div>
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Total Work Requests</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #ef4444;">{{ $totalWorkRequests ?? 0 }}</p>
        </div>
    </div>

    {{-- Monthly Trends Chart --}}
    <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Monthly Work Requests Trend</h2>
        <canvas id="monthlyTrendsChart" style="max-height: 400px; width: 100%;"></canvas>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
        {{-- Work Types Distribution --}}
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Work Requests by Type</h2>
            <canvas id="workTypesChart" style="max-height: 300px; width: 100%;"></canvas>
        </div>

        {{-- Top Requesters --}}
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Top Requesters</h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <th style="padding: 0.75rem; text-align: left;">Name</th>
                            <th style="padding: 0.75rem; text-align: center;">Requests</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topRequesters as $requester)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 0.75rem;">{{ $requester->user->name ?? 'N/A' }}</td>
                            <td style="padding: 0.75rem; text-align: center;">{{ $requester->total }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" style="padding: 1rem; text-align: center; color: #94a3b8;">No data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Status Distribution --}}
    <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Work Request Status Distribution</h2>
        <div style="max-width: 400px; margin: 0 auto;">
            <canvas id="statusDistributionChart" style="max-height: 300px; width: 100%;"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trends Chart
    const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: @json($months ?? []),
            datasets: [{
                label: 'Work Requests',
                data: @json($monthlyWorkRequests ?? []),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // Work Types Chart
    const workTypesCtx = document.getElementById('workTypesChart').getContext('2d');
    new Chart(workTypesCtx, {
        type: 'doughnut',
        data: {
            labels: @json($requestTypesLabels ?? []),
            datasets: [{
                data: @json($requestTypesData ?? []),
                backgroundColor: [
                    '#3b82f6',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'Approved', 'Completed'],
            datasets: [{
                data: [
                    @json($pendingRequests ?? 0),
                    @json($approvedRequests ?? 0),
                    @json($completedRequests ?? 0)
                ],
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endpush
@endsection