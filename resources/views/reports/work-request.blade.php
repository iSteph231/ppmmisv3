@extends('layouts.app')

@section('title', 'Work Requests Report')

@section('content')
<div class="content-wrapper">
    <div class="greeting-section">
        <h1 class="greeting-title">Work Requests Report</h1>
        <p class="greeting-subtitle">View and filter all work requests</p>
    </div>

    {{-- Filters --}}
    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; overflow: hidden;">
        <div style="padding: 1.5rem;">
            <form method="GET" action="{{ route('reports.work-requests') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Status</label>
                    <select name="status" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Work Type</label>
                    <select name="work_type" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                        <option value="all" {{ request('work_type') == 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="ocular_inspection" {{ request('work_type') == 'ocular_inspection' ? 'selected' : '' }}>Ocular Inspection</option>
                        <option value="installation" {{ request('work_type') == 'installation' ? 'selected' : '' }}>Installation</option>
                        <option value="repair" {{ request('work_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                        <option value="replacement" {{ request('work_type') == 'replacement' ? 'selected' : '' }}>Replacement</option>
                        <option value="others" {{ request('work_type') == 'others' ? 'selected' : '' }}>Others</option>
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
                    <a href="{{ route('reports.work-requests') }}" style="background: #6b7280; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none;">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Total Work Requests</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #3b82f6;">{{ $totalRequests ?? 0 }}</p>
        </div>
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Pending</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #f59e0b;">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Approved</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #10b981;">{{ $approvedCount ?? 0 }}</p>
        </div>
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Completed</h3>
            <p style="font-size: 2rem; font-weight: bold; color: #059669;">{{ $completedCount ?? 0 }}</p>
        </div>
    </div>

    {{-- Work Requests Table --}}
    <div style="background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 1rem; text-align: left;">ID</th>
                        <th style="padding: 1rem; text-align: left;">Title</th>
                        <th style="padding: 1rem; text-align: left;">Work Type</th>
                        <th style="padding: 1rem; text-align: left;">Requester</th>
                        <th style="padding: 1rem; text-align: left;">Status</th>
                        <th style="padding: 1rem; text-align: left;">Date Created</th>
                        <th style="padding: 1rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workRequests as $request)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1rem;">#{{ $request->id }}</td>
                        <td style="padding: 1rem;">{{ $request->title ?? 'N/A' }}</td>
                        <td style="padding: 1rem;">{{ ucfirst(str_replace('_', ' ', $request->work_type ?? 'N/A')) }}</td>
                        <td style="padding: 1rem;">{{ $request->user->name ?? 'N/A' }}</td>
                        <td style="padding: 1rem;">
                            @if($request->status == 'pending')
                                <span style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">Pending</span>
                            @elseif($request->status == 'approved')
                                <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">Approved</span>
                            @elseif($request->status == 'completed')
                                <span style="background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">Completed</span>
                            @else
                                <span style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">{{ ucfirst($request->status) }}</span>
                            @endif
                        </td>
                        <td style="padding: 1rem;">{{ $request->created_at ? \Carbon\Carbon::parse($request->created_at)->format('M d, Y') : 'N/A' }}</td>
                        <td style="padding: 1rem; text-align: center;">
                            <a href="{{ route('work-requests.show', $request->id) }}" style="color: #3b82f6; text-decoration: none;">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">No work requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($workRequests) && method_exists($workRequests, 'links'))
        <div style="padding: 1rem; border-top: 1px solid #e5e7eb;">
            {{ $workRequests->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection