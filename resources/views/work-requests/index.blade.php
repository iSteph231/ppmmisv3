@extends('layouts.app')

@section('title', 'Work Requests')

@section('content')
    <div class="content-wrapper">
        <div class="greeting-section flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h1 class="greeting-title" style="max-width: 100%;">Work Requests</h1>
                <p class="greeting-subtitle">View all maintenance work requests</p>
            </div>
            @unless(Auth::user()->isAdmin())
                <a href="{{ route('work-requests.create') }}" class="btn-create relative w-full shrink-0 sm:w-auto">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Work Request
                </a>
            @endunless
        </div>

        <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">

            <x-filter-bar :action="route('work-requests.index')" id="filterForm">
                <div class="filter-bar-search">
                    <label for="tableSearch">Search requests</label>
                    <input type="text" name="search" id="tableSearch" placeholder="Request number, title or process owner" value="{{ request('search') }}">
                </div>

                <div>
                    <label for="filter-status">Status</label>
                    <select name="status" class="search-input" id="filter-status">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
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

            </x-filter-bar>

        </div>

        <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0;">All Work Requests</h2>
            </div>

            <div style="overflow-x: auto;" data-request-updates="work-list">
                <table class="mobile-summary-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 0.875rem 1rem; text-align: left;">ID</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Request #</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Title</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Process Owner</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Status</th>
                            <th style="padding: 0.875rem 1rem; text-align: left;">Date</th>
                            <th style="padding: 0.875rem 1rem; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workRequests ?? [] as $request)
                            <tr data-request-url="{{ route('work-requests.show', $request->id) }}" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.875rem 1rem;">#{{ $request->id }}</td>
                                <td class="mobile-summary-field" style="padding: 0.875rem 1rem; font-family: monospace;">
                                    <x-new-request-dot :created-at="$request->created_at" :eligible="$request->status === 'pending' && $request->scheduled_date === null">{{ $request->request_number ?? 'N/A' }}</x-new-request-dot>
                                </td>
                                <td style="padding: 0.875rem 1rem;">{{ $request->title }}</td>
                                <td class="mobile-summary-field" style="padding: 0.875rem 1rem;">
                                    {{ $request->requester_display_name }}
                                    @if(Auth::user()->isAdmin())
                                        <p class="mt-1 text-xs text-slate-600">Designation: {{ $request->department ?: ($request->user?->department ?: ($request->requester_snapshot['department'] ?? 'Not specified')) }}</p>
                                    @endif
                                </td>
                                <td style="padding: 0.875rem 1rem;">
                                    @php
                                $statusStyles = [
                                    'pending' => 'background: #fef3c7; color: #92400e;',
                                    'approved' => 'background: #d1fae5; color: #065f46;',
                                    'completed' => 'background: #dbeafe; color: #1e40af;',
                                ];
                                $statusStyle = $statusStyles[$request->status] ?? 'background: #f3f4f6; color: #374151;';
                            @endphp
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; {{ $statusStyle }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td style="padding: 0.875rem 1rem;">{{ $request->created_at->format('M d, Y') }}</td>
                                <td style="padding: 0.875rem 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="{{ route('work-requests.show', $request->id) }}" class="action-btn view-btn" title="View" style="color: #3b82f6;">
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
                                <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.5;">
                                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p style="margin: 0;">No work requests found</p>
                                        @unless(Auth::user()->isAdmin())
                                            <a href="{{ route('work-requests.create') }}" class="btn-create" style="margin-top: 0.5rem; text-decoration: none;">Create your first request</a>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($workRequests) && method_exists($workRequests, 'links'))
                <div data-request-updates="work-pagination" style="padding: 0.75rem 1.5rem; border-top: 1px solid #e5e7eb; background: #f9fafb;">
                    {{ $workRequests->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
