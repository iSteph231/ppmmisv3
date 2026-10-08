@if($facilityRequest->status === 'approved' && $facilityRequest->before_photo_path && $facilityRequest->after_photo_path)
<div class="table-container mt-6">
    <div class="table-header"><div>
        <h2 class="table-title">Facility Evaluation Form</h2>
        <p class="facility-card-subtitle">Answer all nine questions before requesting another facility.</p>
    </div></div>
    <form method="POST" action="{{ route('request-facility.evaluation.store', $facilityRequest) }}" class="facility-form">
        @csrf
        @if($errors->any())
            <ul class="facility-error" role="alert">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif
        <h3 class="facility-label">A. Profile of the Respondent</h3>
        <p class="facility-status-note">Facility: {{ $facilityRequest->facility }} · Date: {{ now()->format('M d, Y') }}</p>
        <div class="facility-form-grid">
            @foreach(['name' => 'Name', 'age' => 'Age', 'semester' => 'Semester', 'academic_year' => 'Academic Year'] as $field => $label)
                <div class="facility-field">
                    <label for="evaluation-{{ $field }}" class="facility-label">{{ $label }}</label>
                    <input id="evaluation-{{ $field }}" name="{{ $field }}" type="{{ $field === 'age' ? 'number' : 'text' }}" value="{{ old($field, $field === 'name' ? Auth::user()->name : '') }}" class="facility-input" required @if($field === 'age') min="1" max="120" @else maxlength="{{ in_array($field, ['semester', 'academic_year']) ? 50 : 255 }}" @endif>
                </div>
            @endforeach
            @foreach(['gender' => ['Male', 'Female', 'Other', 'Prefer not to say'], 'client_category' => ['Students', 'Faculty', 'Non-Teaching', 'Supplier', 'Alumni', 'Regulatory Body', 'Industry', 'Community', 'Others']] as $field => $options)
                <div class="facility-field">
                    <label for="evaluation-{{ $field }}" class="facility-label">{{ $field === 'gender' ? 'Gender' : 'Client Category' }}</label>
                    <select id="evaluation-{{ $field }}" name="{{ $field }}" class="facility-input" required>
                        <option value="">Select an option</option>
                        @foreach($options as $option)
                            <option value="{{ $option }}" @selected(old($field) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="facility-field">
                <label for="evaluation-other" class="facility-label">Others, specify (required when Others is selected)</label>
                <input id="evaluation-other" name="other_category" value="{{ old('other_category') }}" class="facility-input" maxlength="255">
            </div>
        </div>
        <h3 class="facility-label mt-6">B. Degree of Satisfaction on the Service Provided</h3>
        <p class="facility-status-note">5 – More Convenient; 4 – Convenient; 3 – Somewhat Convenient; 2 – Less Convenient; 1 – Not Convenient.</p>
        <div class="space-y-4 mt-4">
            @foreach(\App\Models\FacilityRequest::EVALUATION_INDICATORS as $key => $label)
                <fieldset class="rounded-lg border border-slate-200 p-4">
                    <legend class="facility-label px-2">{{ $loop->iteration }}. {{ $label }}</legend>
                    <div class="flex flex-wrap gap-6">
                        @foreach([1, 2, 3, 4, 5] as $rating)
                            <label class="flex items-center gap-2">
                                <input type="radio" name="ratings[{{ $key }}]" value="{{ $rating }}" @checked((string) old('ratings.'.$key) === (string) $rating) required>
                                <span>{{ $rating }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>
        <div class="facility-field mt-6">
            <label for="evaluation-comments" class="facility-label">Further comments and/or suggestions (optional)</label>
            <textarea id="evaluation-comments" name="comments" rows="4" maxlength="2000" class="facility-input">{{ old('comments') }}</textarea>
        </div>
        <div class="facility-form-actions"><button type="submit" class="btn-create">Submit Evaluation</button></div>
    </form>
</div>
@endif