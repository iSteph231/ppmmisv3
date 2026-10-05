@extends('layouts.app')

@section('title', 'Add Facility Request')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/facility-requests.css') }}">
@endpush

@section('content')
<div class="content-wrapper facility-page">
    <div class="greeting-section">
        <h1 class="greeting-title">Add Facility Request</h1>
        <p class="greeting-subtitle">Tell us what facility you need and when you need it.</p>
    </div>
    <div class="table-container">
        <div class="table-header">
            <div>
                <h2 class="table-title">Request Details</h2>
                <p class="facility-card-subtitle">Complete all fields to submit your facility request.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('request-facility.store') }}" class="facility-form">
            @csrf
            <div class="facility-form-grid">
                <div class="facility-field">
                    <label for="facility" class="facility-label">Facility / Area</label>
                    <input id="facility" name="facility" type="text" class="facility-input" value="{{ old('facility') }}" placeholder="e.g., Conference Room A" maxlength="255" required aria-invalid="{{ $errors->has('facility') ? 'true' : 'false' }}" @error('facility') aria-describedby="facility-error" @enderror>
                    @error('facility')
                        <p id="facility-error" class="facility-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="facility-field">
                    <label for="category" class="facility-label">Request Category</label>
                    <select id="category" name="category" class="facility-input" required aria-invalid="{{ $errors->has('category') ? 'true' : 'false' }}" @error('category') aria-describedby="category-error" @enderror>
                        <option value="">Select a category</option>
                        @foreach(['Room Setup', 'Maintenance', 'Equipment', 'Utility', 'Security'] as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p id="category-error" class="facility-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="facility-field">
                    <label for="requested_date" class="facility-label">Requested Date</label>
                    <input id="requested_date" name="requested_date" type="date" class="facility-input" min="{{ now()->toDateString() }}" value="{{ old('requested_date') }}" required aria-invalid="{{ $errors->has('requested_date') ? 'true' : 'false' }}" @error('requested_date') aria-describedby="requested-date-error" @enderror>
                    @error('requested_date')
                        <p id="requested-date-error" class="facility-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="facility-field facility-field-wide">
                    <label for="purpose" class="facility-label">Request Details</label>
                    <textarea id="purpose" name="purpose" class="facility-input" rows="6" maxlength="2000" required placeholder="Describe the setup, equipment, or other requirements." aria-invalid="{{ $errors->has('purpose') ? 'true' : 'false' }}" @error('purpose') aria-describedby="purpose-error" @enderror>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p id="purpose-error" class="facility-error">{{ $message }}</p>
                    @enderror
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
