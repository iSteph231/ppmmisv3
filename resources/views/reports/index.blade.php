@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Reports & Analytics</h1>
        <p class="greeting-subtitle">Generate and view system reports</p>
    </div>

    {{-- Statistics Cards --}}
    <div class="stats-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-card-content">
                <div>
                    <p class="stat-label">TOTAL WORK REQUESTS</p>
                    <p class="stat-value">{{ $totalWorkRequests ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-blue">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-content">
                <div>
                    <p class="stat-label">COMPLETED</p>
                    <p class="stat-value">{{ $completedRequests ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-green">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-content">
                <div>
                    <p class="stat-label">PENDING</p>
                    <p class="stat-value">{{ $pendingRequests ?? 0 }}</p>
                </div>
                <div class="stat-icon stat-icon-yellow">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Options --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
        {{-- Work Requests Report Card --}}
        <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="padding: 1.5rem; text-align: center;">
                <div style="width: 60px; height: 60px; background: #dbeafe; border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <svg width="30" height="30" fill="none" stroke="#3b82f6" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Work Requests Report</h3>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 1rem;">View all work requests with filters</p>
                <a href="{{ route('reports.work-requests') }}" class="btn-create" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; display: inline-block;">
                    View Report
                </a>
            </div>
            <div style="background: #f8fafc; padding: 0.75rem 1rem; border-top: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                    <span>Total: {{ $totalWorkRequests ?? 0 }}</span>
                    <span>Completed: {{ $completedRequests ?? 0 }}</span>
                    <span>Pending: {{ $pendingRequests ?? 0 }}</span>
                </div>
            </div>
        </div>

        {{-- Maintenance Report Card --}}
        <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="padding: 1.5rem; text-align: center;">
                <div style="width: 60px; height: 60px; background: #dcfce7; border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <svg width="30" height="30" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                        <path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                </div>
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Maintenance Report</h3>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 1rem;">Preventive maintenance schedules</p>
                <a href="{{ route('reports.maintenance') }}" class="btn-create" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; display: inline-block;">
                    View Report
                </a>
            </div>
            <div style="background: #f8fafc; padding: 0.75rem 1rem; border-top: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                    <span>Total Schedules: {{ $totalMaintenance ?? 0 }}</span>
                    <span>Completed: {{ $completedMaintenance ?? 0 }}</span>
                    <span>Upcoming: {{ $upcomingMaintenance ?? 0 }}</span>
                </div>
            </div>
        </div>

        {{-- Performance Report Card --}}
        <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="padding: 1.5rem; text-align: center;">
                <div style="width: 60px; height: 60px; background: #fef3c7; border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <svg width="30" height="30" fill="none" stroke="#d97706" viewBox="0 0 24 24">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Performance Report</h3>
                <p style="font-size: 0.75rem; color: #6b7280; margin-bottom: 1rem;">System performance analytics</p>
                <a href="{{ route('reports.performance') }}" class="btn-create" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; display: inline-block;">
                    View Report
                </a>
            </div>
            <div style="background: #f8fafc; padding: 0.75rem 1rem; border-top: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                    <span>Completion Rate: {{ $completionRate ?? 0 }}%</span>
                    <span>Active Personnel: {{ $activePersonnel ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        {{-- Monthly Work Requests Chart --}}
        <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Monthly Work Requests ({{ now()->year }})</h3>
            <canvas id="monthlyWorkRequestsChart" style="max-height: 300px; width: 100%;"></canvas>
        </div>

        {{-- Requests by Type Chart --}}
        <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">Work Requests by Type</h3>
            <canvas id="requestsByTypeChart" style="max-height: 300px; width: 100%;"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Work Requests Chart
        const monthlyCtx = document.getElementById('monthlyWorkRequestsChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($months ?? []),
                datasets: [{
                    label: 'Work Requests',
                    data: @json($monthlyWorkRequests ?? []),
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Number of Requests' } },
                    x: { title: { display: true, text: 'Month' } }
                }
            }
        });

        // Requests by Type Chart (Doughnut)
        const typeCtx = document.getElementById('requestsByTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: @json($requestTypesLabels ?? []),
                datasets: [{
                    data: @json($requestTypesData ?? []),
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                }
            }
        });
    });
</script>
@endpush