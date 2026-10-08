@extends('layouts.app')

@section('title', 'Facility Request Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/facility-requests.css') }}">
@endpush

@section('content')
<div class="content-wrapper facility-page">
    <div class="greeting-section facility-page-header">
        <div>
            <h1 class="greeting-title">Facility Request Details</h1>
            <p class="greeting-subtitle">{{ $facilityRequest->request_number }}</p>
        </div>
        <div class="facility-export-actions">
            @if(Auth::user()->isAdmin() && $facilityRequest->status === 'finished')
                <a href="{{ route('request-facility.export-pdf', $facilityRequest) }}" class="btn-create">Export PDF</a>
            @endif
            <a href="{{ route('request-facility.index') }}" class="facility-btn-secondary">Back to Requests</a>
        </div>
    </div>
    @if(session('success'))
        <div class="facility-alert" role="status">{{ session('success') }}</div>
    @endif
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">{{ $facilityRequest->facility }}</h2>
            <span class="status-badge {{ $facilityRequest->status === 'finished' ? 'badge-completed' : ($facilityRequest->status === 'approved' ? 'badge-approved' : ($facilityRequest->status === 'declined' ? 'bg-red-100 text-red-700' : 'badge-pending')) }}">{{ ucfirst($facilityRequest->status) }}</span>
        </div>
        <div class="facility-form">
            <dl class="facility-form-grid facility-details">
                <div><dt>Requester</dt><dd>{{ $facilityRequest->user->name }}</dd></div>
                <div><dt>Category</dt><dd>{{ $facilityRequest->category }}</dd></div>
                <div><dt>Requested Date</dt><dd>{{ $facilityRequest->requested_date->format('M d, Y') }}</dd></div>
                <div><dt>Submitted</dt><dd>{{ $facilityRequest->created_at->format('M d, Y h:i A') }}</dd></div>
                <div class="facility-field-wide"><dt>Request Details</dt><dd class="facility-purpose">{{ $facilityRequest->purpose }}</dd></div>
            </dl>
                    @include('facility-requests.use-details')
            @if(in_array($facilityRequest->status, ['approved', 'finished'], true))
                <div class="facility-form-grid facility-status-note">
                    @foreach(['before' => 'Before Use', 'after' => 'After Use'] as $stage => $label)
                        <div>
                            <h3 class="facility-label">{{ $label }}</h3>
                            @if($facilityRequest->{$stage.'_photo_path'})
                                <img src="{{ route('request-facility.photo', [$facilityRequest, $stage]) }}" alt="Facility {{ $stage }} use" class="facility-photo">
                            @else
                                <p>Photo not uploaded yet.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if(Auth::user()->isUser() && $facilityRequest->status === 'approved')
                    <div class="facility-form-actions"><a href="{{ route('request-facility.photos', $facilityRequest) }}" class="btn-create">Complete Photos and Evaluation</a></div>
                @endif
            @endif
            @if($facilityRequest->status === 'declined')
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="status">
                    {{ $facilityRequest->decline_reason }}
                    @if(Auth::user()->isUser())
                        <a href="{{ route('request-facility.create') }}" class="font-semibold underline">Submit a new request with an image</a>
                    @endif
                </div>
            @endif
            @if($facilityRequest->program_image_path)
                <div class="mb-6 space-y-3">
                    <h3 class="facility-label">Program or Event Planner</h3>
                    <a href="{{ route('request-facility.program-image', $facilityRequest) }}" target="_blank" rel="noopener" class="facility-view-link">View full image</a>
                    <img src="{{ route('request-facility.program-image', $facilityRequest) }}" alt="Program or event planner for {{ $facilityRequest->facility }}" class="max-h-96 max-w-full rounded-lg border border-slate-200 object-contain">
                </div>
            @endif
            @include('facility-requests.evaluation-results')
            @if(Auth::user()->isAdmin() && $facilityRequest->status === 'pending')
                <form method="POST" action="{{ route('request-facility.approve', $facilityRequest) }}" class="facility-form-actions">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-create">Approve Request</button>
                </form>
            @elseif($facilityRequest->status !== 'declined')
                <p class="facility-status-note">{{ $facilityRequest->status === 'finished' ? 'This facility request is finished. Both usage photos have been uploaded.' : ($facilityRequest->status === 'approved' ? 'This facility request has been approved. Both usage photos and the facility evaluation are required to finish it.' : 'This request is awaiting admin approval.') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
