@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="content-wrapper" x-data="dashboardApp()" x-init="init()">
    
    {{-- GREETING --}}
    <div class="greeting-section">
        <h1 class="greeting-title">Welcome back, {{ Auth::user()->name }}!</h1>
        <p class="greeting-subtitle">Here's what's happening with your maintenance requests today.</p>
    </div>

    {{-- STATISTICS CARDS --}}
    @if(Auth::user()->isAdmin())
        {{-- Admin Cards --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Total Work Requests</p>
                        <p class="stat-value">{{ $totalRequests ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Pending Requests</p>
                        <p class="stat-value">{{ $pendingRequests ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-yellow">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Completed Requests</p>
                        <p class="stat-value">{{ $completedRequests ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-blue">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Active Personnel</p>
                        <p class="stat-value">{{ $activePersonnel ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-indigo">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

    @elseif(Auth::user()->isPersonnel())
        {{-- Personnel Cards --}}
        <div class="stats-grid user-stats">
            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Total Requests</p>
                        <p class="stat-value">{{ $totalRequests ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-blue">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Pending Requests</p>
                        <p class="stat-value">{{ $pendingRequests ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-yellow">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Completed Requests</p>
                        <p class="stat-value">{{ $completedRequests ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- Regular User Cards --}}
        <div class="stats-grid user-stats">
            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">My Requests</p>
                        <p class="stat-value">{{ $myRequests ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-blue">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Pending (My)</p>
                        <p class="stat-value">{{ $myPendingRequests ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-yellow">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Completed (My)</p>
                        <p class="stat-value">{{ $myCompletedRequests ?? 0 }}</p>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- TWO COLUMN LAYOUT (Chart + Notifications) --}}
    <div class="two-column-layout">
        {{-- LEFT COLUMN: Chart --}}
        <div class="chart-container">
            <h2 class="chart-title">Monthly Work Requests</h2>
            <canvas id="requestsChart" style="width: 100%; height: 280px;"></canvas>
        </div>

        {{-- RIGHT COLUMN: Notifications --}}
        <div class="notifications-panel">
            <div class="notifications-header">
                <div class="notifications-title-wrapper">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <h2 class="notifications-title">Notifications</h2>
                    <span x-show="unreadCount > 0" x-text="unreadCount" class="notification-badge"></span>
                </div>
                <button @click="markAllRead" class="mark-all-read-btn">Mark all as read</button>
            </div>
            <div class="notifications-list">
                <template x-for="notif in notifications" :key="notif.id">
                    <div class="notification-item" :class="{'notification-unread': !notif.read}" @click="markAsRead(notif.id)">
                        <div class="notification-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="notification-content">
                            <div class="notification-message">
                                <span class="notification-text" x-text="notif.title"></span>
                                <span class="notification-time" x-text="notif.time"></span>
                            </div>
                            <div class="notification-detail" x-text="notif.message"></div>
                        </div>
                    </div>
                </template>
                <div x-show="notifications.length === 0" class="empty-notifications">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p>No new notifications</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Work Requests Table --}}
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Recent Work Requests</h2>
            <div>
                <input type="text" id="tableSearch" onkeyup="filterTable()" placeholder="Search requests..." class="search-input">
            </div>
        </div>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">ID</th>
                        <th style="width: 12%">Request #</th>
                        <th style="width: 30%">Title</th>
                        <th style="width: 18%">Requester</th>
                        <th style="width: 12%">Status</th>
                        <th style="width: 15%">Date</th>
                        <th style="width: 8%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workRequests ?? [] as $request)
                    <tr>
                        <td data-label="ID">#{{ $request->id }}</td>
                        <td data-label="Request #">{{ $request->request_number ?? 'N/A' }}</td>
                        <td data-label="Title">{{ $request->title }}</td>
                        <td data-label="Requester">{{ $request->user->name ?? 'N/A' }}</td>
                        <td data-label="Status">
                            @php
                                $statusClass = match($request->status) {
                                    'pending' => 'badge-pending',
                                    'approved' => 'badge-approved',
                                    'completed' => 'badge-completed',
                                    default => 'badge-pending'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ ucfirst($request->status) }}</span>
                        </td>
                        <td data-label="Date">{{ $request->created_at->format('M d, Y') }}</td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('work-requests.show', $request->id) }}" class="action-btn view-btn" title="View">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty-table">
                            <div class="empty-state">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p>No work requests found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($workRequests) && method_exists($workRequests, 'links'))
        <div class="table-footer">
            {{ $workRequests->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    function dashboardApp() {
        return {
            unreadCount: 0,
            notifications: @json($notifications ?? []),
            
            init() {
                this.initChart();
                this.updateUnreadCount();
            },
            
            updateUnreadCount() {
                this.unreadCount = this.notifications.filter(n => !n.read).length;
            },
            
            initChart() {
                const chartCanvas = document.getElementById('requestsChart');
                if (chartCanvas && typeof Chart !== 'undefined') {
                    const ctx = chartCanvas.getContext('2d');
                    const chartData = @json($monthlyData ?? array_fill(0, 12, 0));
                    
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            datasets: [{
                                label: 'Work Requests',
                                data: chartData,
                                backgroundColor: 'rgba(0, 48, 135, 0.86)',
                                borderColor: 'rgb(245, 168, 0)',
                                borderWidth: 1,
                                borderRadius: 6,
                                barPercentage: 0.65
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { position: 'top', labels: { font: { size: 11 } } },
                                tooltip: { backgroundColor: '#001f5b' }
                            },
                            scales: {
                                y: { 
                                    beginAtZero: true, 
                                    grid: { color: '#e5e7eb' }, 
                                    ticks: { 
                                        stepSize: 1,
                                        font: { size: 10 } 
                                    } 
                                },
                                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                            }
                        }
                    });
                }
            },
            
            markAsRead(id) {
                fetch(`/notifications/${id}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notif = this.notifications.find(n => n.id === id);
                        if (notif) {
                            notif.read = true;
                            this.updateUnreadCount();
                        }
                    }
                })
                .catch(err => console.error(err));
            },
            
            markAllRead() {
                fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.notifications.forEach(n => n.read = true);
                        this.updateUnreadCount();
                    }
                })
                .catch(err => console.error(err));
            }
        }
    }
    
    function filterTable() {
        const input = document.getElementById('tableSearch');
        if (!input) return;
        
        const filter = input.value.toLowerCase();
        const table = document.querySelector('.data-table table');
        if (!table) return;
        
        const rows = table.getElementsByTagName('tr');
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length; j++) {
                const cellText = cells[j].textContent || cells[j].innerText;
                if (cellText.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? '' : 'none';
        }
    }
</script>
@endpush
@endsection
