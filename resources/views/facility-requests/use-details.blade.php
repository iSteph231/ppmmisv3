@if($facilityRequest->requested_time || $facilityRequest->lead_person)
    <dl class="facility-form-grid facility-details mt-6">
        <div><dt>Time of Use</dt><dd>{{ $facilityRequest->requested_time ? substr($facilityRequest->requested_time, 0, 5) : 'Not recorded' }}</dd></div>
        <div><dt>Lead / Focal Person</dt><dd>{{ $facilityRequest->lead_person ?? 'Not recorded' }}</dd></div>
        <div><dt>Contact Number</dt><dd>{{ $facilityRequest->contact_number ?? 'Not recorded' }}</dd></div>
        <div><dt>Requested by</dt><dd>{{ $facilityRequest->requested_by ?? $facilityRequest->user->name }}</dd></div>
        <div class="facility-field-wide"><dt>List of Participants (Name and Affiliation)</dt><dd class="facility-purpose">{{ $facilityRequest->participants ?? 'Not recorded' }}</dd></div>
    </dl>
@endif