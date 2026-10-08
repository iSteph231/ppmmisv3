@extends('layouts.app')
@section('title', 'Completed Facility Requests')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/facility-requests.css') }}">
@endpush
@section('content')
<div class="content-wrapper facility-page">
    <div class="greeting-section facility-page-header">
        <div>
            <h1 class="greeting-title">Completed Facility Requests</h1>
            <p class="greeting-subtitle">Completed requests appear here automatically with their usage photos and evaluation.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="facility-btn-secondary">Back to Reports</a>
    </div>
    <p class="facility-result-count mb-4">{{ $facilityRequests->total() }} completed {{ Str::plural('request', $facilityRequests->total()) }}</p>
    <div class="space-y-6">
        @forelse($facilityRequests as $facilityRequest)
            <article class="table-container">
                <div class="table-header flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="table-title">{{ $facilityRequest->request_number }} — {{ $facilityRequest->facility }}</h2>
                        <p class="facility-card-subtitle">{{ $facilityRequest->user->name }} · {{ $facilityRequest->requested_date->format('M d, Y') }}</p>
                    </div>
                    <a href="{{ route('request-facility.export-pdf', $facilityRequest) }}" class="btn-create">Export PDF</a>
                </div>
                <div class="facility-form">
                    <dl class="facility-form-grid facility-details">
                        <div><dt>Category</dt><dd>{{ $facilityRequest->category }}</dd></div>
                        <div><dt>Status</dt><dd>Finished</dd></div>
                        <div class="facility-field-wide"><dt>Request Details</dt><dd class="facility-purpose">{{ $facilityRequest->purpose }}</dd></div>
                    </dl>
                    @include('facility-requests.use-details')
                    <div class="facility-form-grid mt-6">
                        @foreach(['before' => 'Before Use', 'after' => 'After Use'] as $stage => $label)
                            <div>
                                <h3 class="facility-label">{{ $label }}</h3>
                                @if($facilityRequest->{$stage.'_photo_path'})
                                    <img src="{{ route('request-facility.photo', [$facilityRequest, $stage]) }}" alt="{{ $facilityRequest->request_number }} {{ $label }}" class="facility-photo" loading="lazy">
                                @else
                                    <p class="facility-status-note">Photo unavailable.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @include('facility-requests.evaluation-results')
                    @if(!$facilityRequest->evaluation)
                        <p class="facility-status-note">No evaluation was recorded for this completed request.</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="table-container"><div class="empty-state"><p>No completed facility requests yet.</p></div></div>
        @endforelse
    </div>
    @if($facilityRequests->hasPages())
        <div class="mt-6">{{ $facilityRequests->links() }}</div>
    @endif
</div>
@endsection