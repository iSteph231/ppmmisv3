@extends('layouts.app')

@section('title', 'Inspection Reports')

@section('content')
    <div class="content-wrapper">
        <div class="greeting-section">
            <h1 class="greeting-title">Inspection Reports</h1>
            <p class="greeting-subtitle">View all scheduled inspections</p>
        </div>

        <div class="stats-grid" style="margin-bottom: 1.5rem;">
            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-label">Total Inspections</p>
                        <p class="stat-value">{{ $totalInspections }}</p>
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
                        <p class="stat-label">Upcoming</p>
                        <p class="stat-value">{{ $upcomingInspections }}</p>
                    </div>
                    <div class="stat-icon stat-icon-yellow">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div>
                        <p class="stat-value">{{ $approvedInspections ?? 0 }}</p>
                        <p class="stat-label">Approved</p>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
            <x-filter-bar :action="route('inspections.index')" id="filterForm" class="filter-bar--report">
                <div class="filter-bar-search">
                    <label for="tableSearch">Search requests</label>
                    <input type="text" name="search" id="tableSearch" placeholder="Search inspections..." value="{{ request('search') }}">
                </div>

                <div>
                    <label for="filter-status">Status</label>
                    <select name="status" class="search-input" id="filter-status">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label for="filter-date_from">Start Date</label>
                    <input type="date" name="date_from" class="search-input" value="{{ request('date_from') }}" id="filter-date_from">
                </div>

                <div>
                    <label for="filter-date_to">End Date</label>
                    <input type="date" name="date_to" class="search-input" value="{{ request('date_to') }}" id="filter-date_to">
                </div>
                <x-slot:help>PDF exports include approved inspections only.</x-slot:help>
                <x-slot:actions>
                    <button type="submit" formaction="{{ route('inspections.export-all-pdf') }}" class="btn-create">Export PDF</button>
                </x-slot:actions>
            </x-filter-bar>
        </div>

        <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="mobile-summary-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 0.875rem 1rem; text-align: left;">ID</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Request #</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Title</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Process Owner</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Scheduled Date</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Status</th>
                            <th style="padding: 0.875rem 1rem; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inspections as $inspection)
                            <tr data-request-url="{{ route('inspections.show', $inspection->id) }}" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.875rem 1rem;">#{{ $inspection->id }}</td>
                                <td class="mobile-summary-field" style="padding: 0.875rem 1rem; font-family: monospace;">
                                    <x-new-request-dot :created-at="$inspection->workRequest?->created_at" :eligible="$inspection->workRequest?->status === 'pending' && $inspection->workRequest?->scheduled_date === null">{{ $inspection->workRequest->request_number ?? 'N/A' }}</x-new-request-dot>
                                </td>
                                <td style="padding: 0.875rem 1rem;">
                                    {{ $inspection->workRequest->title ?? 'N/A' }}
                                </td>
                                <td class="mobile-summary-field" style="padding: 0.875rem 1rem;">
                                    {{ $inspection->workRequest->user->name ?? 'N/A' }}
                                </td>
                                <td style="padding: 0.875rem 1rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #e0e7ff; color: #3730a3;">
                                        {{ $inspection->scheduled_date ? \Carbon\Carbon::parse($inspection->scheduled_date)->format('M d, Y h:i A') : 'N/A' }}
                                    </span>
                                </td>
                                <td style="padding: 0.875rem 1rem;">
                                    @php
                                $statusStyles = [
                                    'pending' => 'background: #fef3c7; color: #92400e;',
                                    'approved' => 'background: #d1fae5; color: #065f46;',
                                    'completed' => 'background: #dbeafe; color: #1e40af;',
                                    'cancelled' => 'background: #fee2e2; color: #991b1b;',
                                ];
                                $statusStyle = $statusStyles[$inspection->status] ?? 'background: #f3f4f6; color: #374151;';
                            @endphp
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; {{ $statusStyle }}">
                                        {{ ucfirst($inspection->status) }}
                                    </span>
                                </td>
                                <td style="padding: 0.875rem 1rem; text-align: center;">
                                    <a href="{{ route('inspections.show', $inspection->id) }}" class="action-btn view-btn" title="View Report" aria-label="View inspection report {{ $inspection->report_number }}">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
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
                                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p style="margin: 0;">No inspection reports found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($inspections) && method_exists($inspections, 'links'))
                <div style="padding: 0.75rem 1.5rem; border-top: 1px solid #e5e7eb; background: #f9fafb;">
                    {{ $inspections->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
