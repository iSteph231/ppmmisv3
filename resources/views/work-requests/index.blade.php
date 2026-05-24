@extends('layouts.app')

@section('title', 'Work Requests')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Work Requests</h1>
        <p class="greeting-subtitle">View all maintenance work requests</p>
    </div>

    {{-- Create Button and Filters --}}
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        
        {{-- Filter Section --}}
        <form method="GET" action="{{ route('work-requests.index') }}" id="filterForm" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            
            {{-- Status Filter --}}
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">All Status</label>
                <select name="status" class="search-input" style="width: auto;" onchange="this.form.submit()">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            
            {{-- Start Date Filter --}}
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Start Date</label>
                <input type="date" name="date_from" class="search-input" style="width: auto;" value="{{ request('date_from') }}" onchange="this.form.submit()">
            </div>
            
            {{-- End Date Filter --}}
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">End Date</label>
                <input type="date" name="date_to" class="search-input" style="width: auto;" value="{{ request('date_to') }}" onchange="this.form.submit()">
            </div>
            
            {{-- Clear Filters Button --}}
            @if(request()->anyFilled(['status', 'date_from', 'date_to', 'search']))
                <div>
                    <a href="{{ route('work-requests.index') }}" class="btn-create" style="background: #6b7280; text-decoration: none;">Clear Filters</a>
                </div>
            @endif
        </form>
        
        {{-- Create Button --}}
        @unless(Auth::user()->isAdmin())
        <div>
            <a href="{{ route('work-requests.create') }}" class="btn-create">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Work Request
            </a>
        </div>
        @endunless
    </div>

    {{-- Work Requests Table --}}
    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <h2 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0;">All Work Requests</h2>
            <div>
                <form method="GET" action="{{ route('work-requests.index') }}" style="display: inline;">
                    @foreach(request()->except('search') as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input type="text" name="search" id="tableSearch" onkeyup="searchWithDelay()" placeholder="Search requests..." class="search-input" value="{{ request('search') }}">
                </form>
            </div>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 0.875rem 1rem; text-align: left;">ID</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">Request #</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">Title</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">Requester</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">Status</th>
                        <th style="padding: 0.875rem 1rem; text-align: left;">Date</th>
                        <th style="padding: 0.875rem 1rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workRequests ?? [] as $request)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.875rem 1rem;">#{{ $request->id }}</td>
                        <td style="padding: 0.875rem 1rem; font-family: monospace;">{{ $request->request_number ?? 'N/A' }}</td>
                        <td style="padding: 0.875rem 1rem;">{{ $request->title }}</td>
                        <td style="padding: 0.875rem 1rem;">{{ $request->user->name ?? 'N/A' }}</td>
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
        <div style="padding: 0.75rem 1.5rem; border-top: 1px solid #e5e7eb; background: #f9fafb;">
            {{ $workRequests->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    let searchTimeout;
    
    function searchWithDelay() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm')?.submit();
        }, 500);
    }
</script>
@endpush
@endsection
