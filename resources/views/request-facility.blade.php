@extends('layouts.app')

@section('title', 'Facility Requests')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/facility-requests.css').'?v='.substr(hash_file('sha256', public_path('css/facility-requests.css')), 0, 16) }}">
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
            <x-filter-bar :action="route('request-facility.index')">
                <div class="filter-bar-search">
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
                @if($errors->any())
                    <p class="facility-error">{{ $errors->first() }}</p>
                @endif
            </x-filter-bar>
            <div class="data-table">
                <table class="mobile-summary-table">
                    <thead>
                        <tr>
                            <th scope="col">Request #</th>
                            <th scope="col" @class(['mobile-only-cell' => !Auth::user()->isAdmin()])>Process Owner</th>
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
                            <tr data-request-url="{{ route('request-facility.show', $facilityRequest) }}">
                                <td class="facility-request-number mobile-summary-field"><x-new-request-dot :created-at="$facilityRequest->created_at" :eligible="$facilityRequest->status === 'pending'">{{ $facilityRequest->request_number }}</x-new-request-dot></td>
                                <td @class(['mobile-summary-field', 'mobile-only-cell' => !Auth::user()->isAdmin()])>
                                    {{ $facilityRequest->user->name }}
                                    @if(Auth::user()->isAdmin())
                                        <p class="mt-1 text-xs text-slate-600">Designation: {{ $facilityRequest->user?->department ?: 'Not specified' }}</p>
                                    @endif
                                </td>
                                <td>{{ $facilityRequest->facility }}</td>
                                <td>{{ $facilityRequest->category }}</td>
                                <td>{{ $facilityRequest->requested_date->format('M d, Y') }}</td>
                                <td><span class="status-badge {{ $facilityRequest->status === 'finished' ? 'badge-completed' : ($facilityRequest->status === 'approved' ? 'badge-approved' : ($facilityRequest->status === 'declined' ? 'bg-red-100 text-red-700' : 'badge-pending')) }}">{{ ucfirst($facilityRequest->status) }}</span></td>
                                <td>{{ $facilityRequest->created_at->format('M d, Y') }}</td>
                                @if($showActions)
                                    <td>
                                        <div class="facility-export-actions">
                                            @if(Auth::user()->isAdmin() || in_array($facilityRequest->status, ['approved', 'declined'], true))
                                                <a href="{{ route('request-facility.show', $facilityRequest) }}" class="action-btn view-btn" title="View Request" aria-label="View request {{ $facilityRequest->request_number }}">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                @if(Auth::user()->isUser() && $facilityRequest->status === 'approved')
                                                    <a href="{{ route('request-facility.photos', $facilityRequest) }}" class="action-btn view-btn" title="Complete Photos and Evaluation" aria-label="Complete photos and evaluation for {{ $facilityRequest->request_number }}">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path d="M14.5 4h-5L7 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2h-3l-2.5-3z"/>
                                                            <circle cx="12" cy="13" r="3"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                                @if(Auth::user()->isAdmin() && $facilityRequest->status === 'finished')
                                                    <a href="{{ route('request-facility.export-pdf', $facilityRequest) }}" class="action-btn view-btn" title="Export PDF" aria-label="Export {{ $facilityRequest->request_number }} as PDF">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $showActions ? 8 : 7 }}" class="empty-table">
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
