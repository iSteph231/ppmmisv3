<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PPMMIS - @yield('title', 'Dashboard')</title>
    
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Your Custom CSS (from public folder) --}}
    <link rel="stylesheet" href="{{ asset('css/ppmmis-dashboard.css') }}">
    
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('styles')
</head>
<body x-data="{ sidebarOpen: false }">
    <div class="dashboard-container">
        {{-- ========== SIDEBAR ========== --}}
        <aside class="sidebar" :class="{'open': sidebarOpen}">
            <div class="sidebar-header">
                <span class="sidebar-brand">PPMMIS</span>
            </div>
            
            <nav class="sidebar-nav">
                {{-- Dashboard Link --}}
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="nav-text">Dashboard</span>
                </a>

                {{-- Work Requests Link --}}
                <a href="{{ route('work-requests.index') }}" class="nav-item {{ request()->routeIs('work-requests.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="nav-text">Work Requests</span>
                </a>

                {{-- Inspection Report Link - ADMIN ONLY --}}
                @auth
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('inspections.index') }}" class="nav-item {{ request()->routeIs('inspections.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            <path d="M16 2v4h4"/>
                        </svg>
                        <span class="nav-text">Inspection Report</span>
                    </a>
                    @endif
                @endauth

                {{-- Maintenance Records Link - Only visible to ADMIN and PERSONNEL --}}
                @auth
                    @if(Auth::user()->isAdmin() || Auth::user()->role === 'personnel')
                    <a href="{{ route('maintenance.index') }}" class="nav-item {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span class="nav-text">Maintenance Records</span>
                    </a>
                    @endif
                @endauth

                {{-- Reports Link - Only visible to ADMIN and PERSONNEL --}}
                @auth
                    @if(Auth::user()->isAdmin() || Auth::user()->role === 'personnel')
                    <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="nav-text">Reports</span>
                    </a>
                    @endif
                @endauth

                {{-- Users Link - Admin Only --}}
                @auth
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="nav-text">Users</span>
                    </a>
                    @endif
                @endauth

                {{-- Settings Link - Visible to all authenticated users --}}
                <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="nav-text">Settings</span>
                </a>

                {{-- Logout Form --}}
                @auth
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="nav-item nav-logout" style="width: 100%; background: none; text-align: left;">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="nav-text">Logout</span>
                    </button>
                </form>
                @endauth
            </nav>
        </aside>

        {{-- ========== MAIN CONTENT ========== --}}
        <main class="main-content">
            <header class="top-nav">
                <div class="top-nav-container">
                    {{-- Mobile Menu Toggle --}}
                    <button @click="sidebarOpen = !sidebarOpen" class="mobile-menu-btn">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    {{-- User Profile Dropdown --}}
                    @auth
                    <div class="user-menu" x-data="{ open: false }">
                        <button @click="open = !open" class="user-menu-btn">
                            <div class="user-avatar">
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="user-name">{{ Auth::user()->name ?? 'User' }}</span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="dropdown-menu" x-cloak>
                            <a href="{{ route('settings.index') }}" class="dropdown-item">My Profile</a>
                            <a href="{{ route('settings.index') }}" class="dropdown-item">Account Settings</a>
                            <hr style="margin: 0.5rem 0;">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width:100%; text-align:left;">Sign Out</button>
                            </form>
                        </div>
                    </div>
                    @else
                    {{-- Show login link when not authenticated --}}
                    <div class="user-menu">
                        <a href="{{ route('login') }}" class="user-menu-btn" style="text-decoration: none;">
                            <div class="user-avatar">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <span class="user-name">Login</span>
                        </a>
                    </div>
                    @endauth
                </div>
            </header>

            {{-- Page Content --}}
            <div class="dashboard-content">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Your Custom JS (from public folder) --}}
    <script src="{{ asset('js/dashboard.js') }}"></script>
    @stack('scripts')
</body>
</html>