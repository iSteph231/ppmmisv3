@extends('layouts.app')

@section('title', 'Facility Requests')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/facility-requests.css') }}">
@endpush

@section('content')
@php
    $showActions = Auth::user()->isAdmin() || $facilityRequests->getCollection()->contains(fn ($facilityRequest) => in_array($facilityRequest->status, ['approved', 'declined'], true));
@endphp
<div class="content-wrapper facility-page" data-request-updates="facility-list">
    <div class="greeting-section facility-page-header">
        <div>
            <h1 class="greeting-title">{{ Auth::user()->isAdmin() ? 'Facility Requests' : 'My Facility Requests' }}</h1>
            <p class="greeting-subtitle">{{ Auth::user()->isAdmin() ? 'Review facility requirements and approve pending requests.' : 'Submit your facility requirements and track their approval.' }}</p>
        </div>
        @if(Auth::user()->isUser() && empty($outstandingRequest))
            <a href="{{ route('request-facility.create') }}" class="btn-create"><span aria-hidden="true">+</span> Add Request</a>
        @endif
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="facility-alert" role="status">{{ session('success') }}</div>
    @endif

    @if(!empty($outstandingRequest))
        <div class="facility-alert" role="status">
            Finish {{ $outstandingRequest->request_number }} by uploading both usage photos and completing the evaluation before requesting another facility.
            <a href="{{ route('request-facility.photos', $outstandingRequest) }}" class="facility-view-link">Complete Photos and Evaluation</a>
        </div>
    @endif

    @isset($summary)
        <div class="facility-summary" aria-label="Request summary">
            @foreach(['total' => 'Total Requests', 'pending' => 'Pending Approval', 'approved' => 'Awaiting Photos / Evaluation', 'finished' => 'Finished Requests', 'declined' => 'Declined Requests'] as $key => $label)
                <div class="facility-summary-card"><span>{{ $label }}</span><strong>{{ $summary[$key] }}</strong></div>
            @endforeach
        </div>
    @endisset

    <div class="table-container">
        <div class="table-header">
            <div>
                <h2 class="table-title">{{ Auth::user()->isAdmin() ? 'All Facility Requests' : 'Your Submitted Requests' }}</h2>
                <p class="facility-card-subtitle">{{ Auth::user()->isAdmin() ? 'Open a request to review its details before approval.' : 'Requests submitted from your account appear here.' }}</p>
            </div>
        </div>
        <form method="GET" action="{{ route('request-facility.index') }}" class="facility-filters">
            <div class="facility-field">
                <label for="search" class="facility-label">Search requests</label>
                <input id="search" name="search" class="facility-input" placeholder="Request number or facility" value="{{ request('search') }}" maxlength="255">
            </div>
            <div class="facility-field">
                <label for="status" class="facility-label">Status</label>
                <select id="status" name="status" class="facility-input">
                    <option value="">All statuses</option>
                    <option value="declined" @selected(request('status') === 'declined')>Declined</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="finished" @selected(request('status') === 'finished')>Finished</option>
                </select>
            </div>
            <button type="submit" class="btn-create">Apply Filters</button>
            <a href="{{ route('request-facility.index') }}" class="facility-btn-secondary">Reset</a>
            @if($errors->any())
                <p class="facility-error">{{ $errors->first() }}</p>
            @endif
        </form>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Request #</th>
                        @if(Auth::user()->isAdmin())<th scope="col">Requester</th>@endif
                        <th scope="col">Facility / Area</th>
                        <th scope="col">Category</th>
                        <th scope="col">Requested Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Submitted</th>
                        @if($showActions)<th scope="col">Action</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($facilityRequests as $facilityRequest)
                        <tr>
                            <td class="facility-request-number">{{ $facilityRequest->request_number }}</td>
                            @if(Auth::user()->isAdmin())<td>{{ $facilityRequest->user->name }}</td>@endif
                            <td>{{ $facilityRequest->facility }}</td>
                            <td>{{ $facilityRequest->category }}</td>
                            <td>{{ $facilityRequest->requested_date->format('M d, Y') }}</td>
                            <td><span class="status-badge {{ $facilityRequest->status === 'finished' ? 'badge-completed' : ($facilityRequest->status === 'approved' ? 'badge-approved' : ($facilityRequest->status === 'declined' ? 'bg-red-100 text-red-700' : 'badge-pending')) }}">{{ ucfirst($facilityRequest->status) }}</span></td>
                            <td>{{ $facilityRequest->created_at->format('M d, Y') }}</td>
                            @if($showActions)
                            <td>
                                <div class="facility-export-actions">
                                    @if(Auth::user()->isAdmin() || in_array($facilityRequest->status, ['approved', 'declined'], true))
                                    <a href="{{ route('request-facility.show', $facilityRequest) }}" class="facility-view-link">{{ Auth::user()->isAdmin() && $facilityRequest->status === 'pending' ? 'Review Request' : 'View Details' }}</a>
                                    @if(Auth::user()->isUser() && $facilityRequest->status === 'approved')
                                        <a href="{{ route('request-facility.photos', $facilityRequest) }}" class="facility-view-link">Complete Photos and Evaluation</a>
                                    @endif
                                    @if(Auth::user()->isAdmin() && $facilityRequest->status === 'finished')
                                        <a href="{{ route('request-facility.export-pdf', $facilityRequest) }}" class="facility-btn-secondary" aria-label="Export {{ $facilityRequest->request_number }} as PDF">Export PDF</a>
                                    @endif
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->isAdmin() ? 8 : ($showActions ? 7 : 6) }}" class="empty-table">
                                <div class="empty-state">
                                    <p>No facility requests found.</p>
                                    @if(Auth::user()->isUser())
                                        <a href="{{ route('request-facility.create') }}" class="btn-create">Add Request</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <p class="facility-result-count">{{ $facilityRequests->total() }} {{ Str::plural('request', $facilityRequests->total()) }} found</p>
            @if($facilityRequests->hasPages()){{ $facilityRequests->links() }}@endif
        </div>
    </div>
</div>
@endsection
