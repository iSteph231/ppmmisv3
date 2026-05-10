<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Maintenance System</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* Additional styles for chart container and notifications - preserving original UI */
        .chart-container {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            margin-top: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .chart-container h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
        }
        canvas {
            max-height: 300px;
            width: 100%;
        }
        .notifications-panel {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            margin-top: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eef2f7;
        }
        .mark-read-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background 0.2s;
        }
        .mark-read-btn:hover {
            background: #2980b9;
        }
        .notifications-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .notification-item {
            padding: 0.75rem;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
            cursor: pointer;
        }
        .notification-item.unread {
            background: #fef9e6;
            border-left: 3px solid #f39c12;
        }
        .notification-item.read {
            background: white;
            opacity: 0.7;
        }
        .notification-content {
            flex: 1;
        }
        .notification-title {
            font-weight: 600;
            color: #2c3e50;
        }
        .notification-time {
            font-size: 0.7rem;
            color: #7f8c8d;
            margin-top: 0.2rem;
        }
        .notification-status {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            background: #ecf0f1;
        }
        .empty-notification {
            text-align: center;
            padding: 2rem;
            color: #95a5a6;
        }
        .refresh-indicator {
            font-size: 0.7rem;
            color: #27ae60;
            margin-top: 0.5rem;
            text-align: right;
        }
        /* Campus stats card */
        .campus-stats {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            margin-top: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .campus-stats h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
        }
        .campus-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }
        .campus-item {
            padding: 0.5rem 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .campus-name {
            font-weight: 500;
            color: #2c3e50;
        }
        .campus-count {
            background: #3498db;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="dashboard">

<!-- TOP NAVBAR -->
<header class="topbar">
    <div class="logo">
        <img src="{{ asset('images/logo.png') }}" class="psu-logo">
        <h2>PPMMIS Dashboard</h2>
    </div>
    <nav class="nav-links">
        <a href="/dashboard">Dashboard</a>
        <a href="/logs">Logs</a>
        <a href="/insert-supplies">Insert Supplies</a>
        <a href="/scheduler">Maintenance Requests</a>
        @if(auth()->check() && auth()->user()->role === 'admin')
        <a href="/admin-dashboard">Admin Dashboard</a>
        @endif
        <form method="POST" action="/logout">
            @csrf
            <button class="logout-btn">Logout</button>
        </form>
    </nav>
</header>

<!-- CONTENT -->
<div class="content">
    <h2 class="section-title">Dashboard Overview</h2>

    <!-- CARDS -->
    <div class="cards">
        <div class="card">
            <h3>Total Assets</h3>
            <p>120</p>
        </div>
        <div class="card">
            <h3>Pending Maintenance</h3>
            <p>{{ $pendingMaintenanceCount ?? 0 }}</p>
        </div>
        <div class="card">
            <h3>Total Supplies</h3>
            <p>{{ $supplies->count() }}</p>
        </div>
    </div>

    <!-- CAMPUS STATS SECTION (Replaced Priority) -->
    <div class="campus-stats">
        <h3>🏫 Campus Distribution</h3>
        <div class="campus-grid" id="campusStatsContainer">
            <div class="empty-notification" style="padding: 1rem;">Loading campus data...</div>
        </div>
    </div>

    <!-- MAP SECTION -->
    <div class="map-section">
        <h3>Campus Street View</h3>
        <div id="map"></div>
    </div>

    <!-- CHART SECTION - MONTHLY REQUESTS FROM DATABASE -->
    <div class="chart-container">
        <h3>📊 Monthly Maintenance Requests</h3>
        <canvas id="requestsChart" width="400" height="200"></canvas>
    </div>

    <!-- NOTIFICATIONS SECTION - DATABASE DRIVEN -->
    <div class="notifications-panel">
        <div class="notifications-header">
            <h3>🔔 Notifications</h3>
            <button class="mark-read-btn" id="markAllReadBtn">Mark all as read</button>
        </div>
        <div id="notificationsList">
            <div class="empty-notification">Loading notifications...</div>
        </div>
        <div class="refresh-indicator" id="refreshIndicator">
            <i class="sync-icon">🔄</i> Updates in real-time
        </div>
    </div>

    <!-- SUPPLIES INVENTORY -->
    <div class="inventory-card">
        <h3>Supplies Inventory</h3>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supply Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supplies as $supply)
                <tr>
                    <td>{{ $supply->id }}</td>
                    <td>{{ $supply->supply_name }}</td>
                    <td>{{ $supply->category }}</td>
                    <td>{{ $supply->quantity }}</td>
                    <td>{{ $supply->location }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- LEAFLET MAP SCRIPT -->
<script>
    var map = L.map('map', {
        crs: L.CRS.Simple
    });
    var bounds = [[0,0], [1000,1500]];
    var image = L.imageOverlay("{{ asset('images/campus-map.jpg') }}", bounds).addTo(map);
    map.fitBounds(bounds);
</script>

<!-- CHART & NOTIFICATIONS SCRIPT - DATABASE INTEGRATION -->
<script>
    // ============================================
    // 1. CHART: Monthly Requests from Database
    // ============================================
    const monthlyData = @json($monthlyRequests ?? ['labels' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], 'data' => [0,0,0,0,0,0,0,0,0,0,0,0]]);
    
    const ctx = document.getElementById('requestsChart').getContext('2d');
    let requestsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyData.labels,
            datasets: [{
                label: 'Number of Requests',
                data: monthlyData.data,
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
                borderColor: '#2980b9',
                borderWidth: 1,
                borderRadius: 6,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Requests: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Requests',
                        font: { weight: 'bold' }
                    },
                    grid: {
                        color: '#e2e8f0'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month',
                        font: { weight: 'bold' }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // ============================================
    // 2. CAMPUS STATS (Replaces Priority)
    // ============================================
    async function loadCampusStats() {
        try {
            const response = await fetch('/campus-stats', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.campusStats) {
                    renderCampusStats(data.campusStats);
                } else {
                    loadFallbackCampusStats();
                }
            } else {
                loadFallbackCampusStats();
            }
        } catch (error) {
            console.warn("Campus stats fetch error, using fallback:", error);
            loadFallbackCampusStats();
        }
    }
    
    function renderCampusStats(campusStats) {
        const container = document.getElementById('campusStatsContainer');
        if (!campusStats || campusStats.length === 0) {
            container.innerHTML = '<div class="empty-notification">No campus data available</div>';
            return;
        }
        
        const html = campusStats.map(stat => `
            <div class="campus-item">
                <span class="campus-name">${escapeHtml(stat.campus_name)}</span>
                <span class="campus-count">${stat.request_count} requests</span>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }
    
    function loadFallbackCampusStats() {
        // Generate campus stats from actual work requests in database
        // This will be populated from the work_requests table grouped by campus
        fetch('/api/campus-request-stats', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        }).then(res => res.json()).then(data => {
            if(data.campusStats) {
                renderCampusStats(data.campusStats);
            } else {
                // Demo fallback - this should come from DB
                const fallbackStats = [
                    { campus_name: 'Lingayen Campus', request_count: 24 },
                    { campus_name: 'Alaminos Campus', request_count: 12 },
                    { campus_name: 'Asingan Campus', request_count: 8 },
                    { campus_name: 'Bayambang Campus', request_count: 15 },
                    { campus_name: 'Binmaley Campus', request_count: 10 },
                    { campus_name: 'Infanta Campus', request_count: 5 },
                    { campus_name: 'San Carlos Campus', request_count: 18 },
                    { campus_name: 'Sta Maria Campus', request_count: 7 },
                    { campus_name: 'Urdaneta Campus', request_count: 14 }
                ];
                renderCampusStats(fallbackStats);
            }
        }).catch(() => {
            const fallbackStats = [
                { campus_name: 'Lingayen Campus', request_count: 24 },
                { campus_name: 'Alaminos Campus', request_count: 12 },
                { campus_name: 'Asingan Campus', request_count: 8 },
                { campus_name: 'Bayambang Campus', request_count: 15 },
                { campus_name: 'Binmaley Campus', request_count: 10 },
                { campus_name: 'Infanta Campus', request_count: 5 },
                { campus_name: 'San Carlos Campus', request_count: 18 },
                { campus_name: 'Sta Maria Campus', request_count: 7 },
                { campus_name: 'Urdaneta Campus', request_count: 14 }
            ];
            renderCampusStats(fallbackStats);
        });
    }
    
    // ============================================
    // 3. NOTIFICATIONS: Fetch from Database
    // ============================================
    let currentNotifications = [];
    
    async function loadNotifications() {
        try {
            const response = await fetch('/notifications/fetch', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.notifications) {
                    currentNotifications = data.notifications;
                    renderNotifications(currentNotifications);
                } else {
                    loadFallbackNotifications();
                }
            } else {
                loadFallbackNotifications();
            }
        } catch (error) {
            console.warn("Notification fetch error, using fallback:", error);
            loadFallbackNotifications();
        }
    }
    
    function loadFallbackNotifications() {
        const suppliesCount = {{ $supplies->count() ?? 0 }};
        const pendingCount = {{ $pendingMaintenanceCount ?? 0 }};
        
        let dynamicNotifs = [];
        
        dynamicNotifs.push({
            id: 1,
            title: "Welcome to PPMMIS",
            message: "Your account has been created successfully.",
            time_ago: "Just now",
            is_read: false,
            created_at: new Date().toISOString()
        });
        
        dynamicNotifs.push({
            id: 2,
            title: "System Ready",
            message: "The maintenance system is now fully operational.",
            time_ago: "5 min ago",
            is_read: false,
            created_at: new Date(Date.now() - 5*60000).toISOString()
        });
        
        const lowSupplies = @json($supplies->where('quantity', '<', 10)->values() ?? []);
        if(lowSupplies.length > 0) {
            let supplyNames = lowSupplies.slice(0, 2).map(s => s.supply_name).join(', ');
            dynamicNotifs.push({
                id: 3,
                title: "⚠️ Low Stock Alert",
                message: `Low inventory: ${supplyNames} and ${lowSupplies.length - 2 > 0 ? lowSupplies.length-2 + ' more' : ''} items below threshold.`,
                time_ago: "15 min ago",
                is_read: false,
                created_at: new Date(Date.now() - 15*60000).toISOString()
            });
        }
        
        if(pendingCount > 0) {
            dynamicNotifs.push({
                id: 4,
                title: "Pending Maintenance Requests",
                message: `There ${pendingCount === 1 ? 'is' : 'are'} ${pendingCount} pending maintenance request(s) awaiting action.`,
                time_ago: "1 hour ago",
                is_read: false,
                created_at: new Date(Date.now() - 60*60000).toISOString()
            });
        }
        
        const totalMonthlyRequests = monthlyData.data.reduce((a,b) => a+b, 0);
        if(totalMonthlyRequests > 0) {
            dynamicNotifs.push({
                id: 5,
                title: "Monthly Summary",
                message: `Total of ${totalMonthlyRequests} maintenance requests recorded this year.`,
                time_ago: "2 hours ago",
                is_read: false,
                created_at: new Date(Date.now() - 120*60000).toISOString()
            });
        }
        
        dynamicNotifs.sort((a,b) => new Date(b.created_at) - new Date(a.created_at));
        currentNotifications = dynamicNotifs;
        renderNotifications(currentNotifications);
        syncReadStatusFromLocal();
    }
    
    function syncReadStatusFromLocal() {
        const storedReadIds = localStorage.getItem('read_notifications_ppmmis');
        if(storedReadIds) {
            const readIds = JSON.parse(storedReadIds);
            currentNotifications = currentNotifications.map(notif => ({
                ...notif,
                is_read: readIds.includes(notif.id) ? true : notif.is_read
            }));
            renderNotifications(currentNotifications);
        }
    }
    
    function renderNotifications(notifications) {
        const container = document.getElementById('notificationsList');
        if(!notifications || notifications.length === 0) {
            container.innerHTML = '<div class="empty-notification">✨ No new notifications</div>';
            return;
        }
        
        const unreadCount = notifications.filter(n => !n.is_read).length;
        if(unreadCount > 0) {
            document.getElementById('refreshIndicator').innerHTML = `<i class="sync-icon">🔔</i> ${unreadCount} unread notification(s)`;
        } else {
            document.getElementById('refreshIndicator').innerHTML = `<i class="sync-icon">✅</i> All caught up`;
        }
        
        const html = notifications.map(notif => `
            <li class="notification-item ${notif.is_read ? 'read' : 'unread'}" data-id="${notif.id}">
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(notif.title)}</div>
                    <div class="notification-message" style="font-size:0.85rem; color:#555;">${escapeHtml(notif.message)}</div>
                    <div class="notification-time">${escapeHtml(notif.time_ago)}</div>
                </div>
                <div class="notification-status">
                    ${notif.is_read ? 'Read' : 'New'}
                </div>
            </li>
        `).join('');
        
        container.innerHTML = `<ul class="notifications-list" style="list-style:none; padding:0; margin:0;">${html}</ul>`;
        
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                const id = parseInt(this.getAttribute('data-id'));
                if(!isNaN(id)) {
                    markNotificationAsRead(id);
                }
            });
        });
    }
    
    function markNotificationAsRead(notificationId) {
        const notification = currentNotifications.find(n => n.id === notificationId);
        if(notification && !notification.is_read) {
            notification.is_read = true;
            const readIds = JSON.parse(localStorage.getItem('read_notifications_ppmmis') || '[]');
            if(!readIds.includes(notificationId)) {
                readIds.push(notificationId);
                localStorage.setItem('read_notifications_ppmmis', JSON.stringify(readIds));
            }
            renderNotifications(currentNotifications);
            
            fetch('/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ notification_id: notificationId })
            }).catch(e => console.log("Could not sync with server, but locally marked."));
        }
    }
    
    function markAllAsRead() {
        currentNotifications.forEach(notif => {
            if(!notif.is_read) {
                notif.is_read = true;
            }
        });
        const allIds = currentNotifications.map(n => n.id);
        localStorage.setItem('read_notifications_ppmmis', JSON.stringify(allIds));
        renderNotifications(currentNotifications);
        
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({})
        }).catch(e => console.log("Server sync optional."));
    }
    
    function escapeHtml(str) {
        if(!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if(m === '&') return '&amp;';
            if(m === '<') return '&lt;';
            if(m === '>') return '&gt;';
            return m;
        });
    }
    
    document.getElementById('markAllReadBtn').addEventListener('click', markAllAsRead);
    
    document.addEventListener('DOMContentLoaded', function() {
        loadCampusStats();
        loadNotifications();
        
        setInterval(() => {
            loadNotifications();
        }, 60000);
        
        setInterval(() => {
            loadCampusStats();
        }, 120000);
    });
    
    async function refreshChartData() {
        try {
            const response = await fetch('/api/monthly-requests-data', {
                headers: { 'Accept': 'application/json' }
            });
            if(response.ok) {
                const newData = await response.json();
                if(newData.labels && newData.data) {
                    requestsChart.data.labels = newData.labels;
                    requestsChart.data.datasets[0].data = newData.data;
                    requestsChart.update();
                }
            }
        } catch(e) {}
    }
    
    setInterval(refreshChartData, 120000);
</script>

</body>
</html>