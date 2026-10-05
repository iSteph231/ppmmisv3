@extends('layouts.app')
@section('title', 'Facility Usage Photos')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/facility-requests.css') }}">
@endpush
@section('content')
<div class="content-wrapper facility-page">
    <div class="greeting-section facility-page-header">
        <div>
            <h1 class="greeting-title">Facility Usage Photos</h1>
            <p class="greeting-subtitle">{{ $facilityRequest->request_number }} · {{ $facilityRequest->facility }}</p>
        </div>
        <a href="{{ route('request-facility.show', $facilityRequest) }}" class="facility-btn-secondary">Back to Request</a>
    </div>
    @foreach(['success', 'error'] as $messageType)
        @if(session($messageType))<div class="facility-alert" role="status">{{ session($messageType) }}</div>@endif
    @endforeach
    <div class="table-container">
        <div class="table-header">
            <div>
                <h2 class="table-title">Before and After Use</h2>
                <p class="facility-card-subtitle">{{ $facilityRequest->status === 'finished' ? 'Both photos are uploaded. Your request is finished.' : 'Upload one image per stage. Your request stays approved until both photos are saved.' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('request-facility.photos.store', $facilityRequest) }}" enctype="multipart/form-data" class="facility-form">
            @csrf
            <div class="facility-form-grid">
                @foreach(['before' => 'Before Use', 'after' => 'After Use'] as $stage => $label)
                    <div class="facility-field">
                        <label for="{{ $stage }}_photo" class="facility-label">{{ $label }}</label>
                        @if($facilityRequest->{$stage.'_photo_path'})
                            <img src="{{ route('request-facility.photo', [$facilityRequest, $stage]) }}" alt="Facility {{ $stage }} use" class="facility-photo">
                            <p class="facility-status-note">Photo uploaded. One image per stage.</p>
                        @elseif($facilityRequest->status === 'approved')
                            <input id="{{ $stage }}_photo" name="{{ $stage }}_photo" type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp" class="facility-input" aria-describedby="{{ $stage }}-help">
                            <p id="{{ $stage }}-help" class="facility-result-count">One JPG, PNG, WebP, GIF, or BMP image, up to 5 MB.</p>
                        @endif
                        @error($stage.'_photo')<p class="facility-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
            @if($facilityRequest->status === 'approved')
                <div class="facility-form-actions"><button type="submit" class="btn-create">Save Photos</button></div>
            @else
                <div class="facility-form-actions"><a href="{{ route('request-facility.create') }}" class="btn-create">Request Another Facility</a></div>
            @endif
        </form>
    </div>
</div>
@endsection
