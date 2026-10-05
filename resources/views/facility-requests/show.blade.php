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
        <a href="{{ route('request-facility.index') }}" class="facility-btn-secondary">Back to Requests</a>
    </div>
    @if(session('success'))
        <div class="facility-alert" role="status">{{ session('success') }}</div>
    @endif
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">{{ $facilityRequest->facility }}</h2>
            <span class="status-badge {{ $facilityRequest->status === 'finished' ? 'badge-completed' : ($facilityRequest->status === 'approved' ? 'badge-approved' : 'badge-pending') }}">{{ ucfirst($facilityRequest->status) }}</span>
        </div>
        <div class="facility-form">
            <dl class="facility-form-grid facility-details">
                <div><dt>Requester</dt><dd>{{ $facilityRequest->user->name }}</dd></div>
                <div><dt>Category</dt><dd>{{ $facilityRequest->category }}</dd></div>
                <div><dt>Requested Date</dt><dd>{{ $facilityRequest->requested_date->format('M d, Y') }}</dd></div>
                <div><dt>Submitted</dt><dd>{{ $facilityRequest->created_at->format('M d, Y h:i A') }}</dd></div>
                <div class="facility-field-wide"><dt>Request Details</dt><dd class="facility-purpose">{{ $facilityRequest->purpose }}</dd></div>
            </dl>
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
                    <div class="facility-form-actions"><a href="{{ route('request-facility.photos', $facilityRequest) }}" class="btn-create">Upload Usage Photos</a></div>
                @endif
            @endif
            @if(Auth::user()->isAdmin() && $facilityRequest->status === 'pending')
                <form method="POST" action="{{ route('request-facility.approve', $facilityRequest) }}" class="facility-form-actions">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-create">Approve Request</button>
                </form>
            @else
                <p class="facility-status-note">{{ $facilityRequest->status === 'finished' ? 'This facility request is finished. Both usage photos have been uploaded.' : ($facilityRequest->status === 'approved' ? 'This facility request has been approved. Both usage photos are required to finish it.' : 'This request is awaiting admin approval.') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
