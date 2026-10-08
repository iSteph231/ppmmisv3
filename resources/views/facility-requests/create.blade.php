@extends('layouts.app')

@section('title', 'Facility Use Request Form')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/facility-requests.css') }}">
@endpush

@section('content')
<div class="content-wrapper facility-page">
    <div class="greeting-section facility-page-header">
        <div>
            <h1 class="greeting-title">Facility Use Request Form</h1>
            <p class="greeting-subtitle">Complete the requestor information below to request a facility.</p>
        </div>
        <a href="{{ route('request-facility.index') }}" class="facility-btn-secondary">Back to Requests</a>
    </div>
    <div class="table-container">
        <form method="POST" action="{{ route('request-facility.store') }}" enctype="multipart/form-data" class="facility-form">
            @csrf
            <div class="mb-6 text-right">
                <p class="facility-label">Date: <time id="facility-current-date" datetime="{{ now('Asia/Manila')->toIso8601String() }}">{{ now('Asia/Manila')->format('F d, Y h:i:s A') }}</time></p>
            </div>
            @if($errors->any())
                <div class="facility-error mb-6" role="alert">
                    <p>Please correct the following fields:</p>
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            <div class="facility-form-grid">
                @foreach(['facility' => 'Facility Requested', 'requested_date' => 'Date of Use', 'requested_time' => 'Time of Use', 'lead_person' => 'Lead / Focal Person', 'contact_number' => 'Contact Number'] as $field => $label)
                    <div class="facility-field {{ $field === 'facility' ? 'facility-field-wide' : '' }}">
                        <label for="{{ $field }}" class="facility-label">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ ['requested_date' => 'date', 'requested_time' => 'time', 'contact_number' => 'tel'][$field] ?? 'text' }}" class="facility-input" value="{{ old($field, $field === 'contact_number' ? Auth::user()->phone_number : ($field === 'lead_person' ? Auth::user()->name : '')) }}" required @if($field === 'requested_date') min="{{ now()->toDateString() }}" @elseif($field !== 'requested_time') maxlength="{{ $field === 'contact_number' ? 50 : 255 }}" @endif aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" @error($field) aria-describedby="{{ $field }}-error" @enderror>
                        @error($field)<p id="{{ $field }}-error" class="facility-error">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div class="facility-field facility-field-wide">
                    <label for="program_image" class="facility-label">Program or Event Planner Image</label>
                    <p id="program-image-help" class="facility-result-count">Attach a JPG, PNG, or WebP image up to 5 MB. Requests submitted without an image are automatically declined.</p>
                    <input id="program_image" name="program_image" type="file" accept="image/jpeg,image/png,image/webp" class="facility-input" aria-describedby="program-image-help @error('program_image') program-image-error @enderror" aria-invalid="{{ $errors->has('program_image') ? 'true' : 'false' }}">
                    @error('program_image')<p id="program-image-error" class="facility-error">{{ $message }}</p>@enderror
                </div>
                <div class="facility-field facility-field-wide">
                    <label for="purpose" class="facility-label">Purpose of Use</label>
                    <textarea id="purpose" name="purpose" rows="4" class="facility-input" maxlength="2000" required aria-invalid="{{ $errors->has('purpose') ? 'true' : 'false' }}" @error('purpose') aria-describedby="purpose-error" @enderror>{{ old('purpose') }}</textarea>
                    @error('purpose')<p id="purpose-error" class="facility-error">{{ $message }}</p>@enderror
                </div>
                <div class="facility-field facility-field-wide">
                    <label for="participants" class="facility-label">List of Participants (Name and Affiliation)</label>
                    <p id="participants-help" class="facility-result-count">Enter one participant per line, including their name and affiliation.</p>
                    <textarea id="participants" name="participants" rows="7" class="facility-input" maxlength="10000" required placeholder="Juan Dela Cruz — College of Information Technology" aria-describedby="participants-help @error('participants') participants-error @enderror" aria-invalid="{{ $errors->has('participants') ? 'true' : 'false' }}">{{ old('participants') }}</textarea>
                    @error('participants')<p id="participants-error" class="facility-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid gap-8 border-t border-slate-200 pt-6 mt-6 sm:grid-cols-2">
                <div class="facility-field">
                    <label for="requested_by" class="facility-label">Requested by</label>
                    <input id="requested_by" name="requested_by" class="facility-input" value="{{ old('requested_by', Auth::user()->name) }}" maxlength="255" required aria-invalid="{{ $errors->has('requested_by') ? 'true' : 'false' }}" @error('requested_by') aria-describedby="requested_by-error" @enderror>
                    <p class="facility-result-count">Requestor's printed name</p>
                    @error('requested_by')<p id="requested_by-error" class="facility-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <p class="facility-label">Approval</p>
                    <p class="facility-status-note">Requests with a program or event planner image will be sent to the admin for review. Requests without an image are automatically declined.</p>
                </div>
            </div>
            <div class="facility-form-actions">
                <a href="{{ route('request-facility.index') }}" class="facility-btn-secondary">Cancel</a>
                <button type="submit" class="btn-create">Submit Request</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const currentDate = document.getElementById('facility-current-date');
        const formatter = new Intl.DateTimeFormat('en-US', {
            timeZone: 'Asia/Manila',
            year: 'numeric',
            month: 'long',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });

        const updateDate = () => {
            const now = new Date();
            currentDate.textContent = formatter.format(now);
            currentDate.dateTime = now.toISOString();
        };

        updateDate();
        window.setInterval(updateDate, 1000);
    })();
</script>
@endpush