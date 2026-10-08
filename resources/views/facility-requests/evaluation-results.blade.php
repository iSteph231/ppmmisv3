            @if($facilityRequest->evaluation)
                <section class="mt-6">
                    <h3 class="facility-label">Facility Evaluation</h3>
                    <dl class="facility-form-grid facility-details">
                        @foreach(['name' => 'Name', 'age' => 'Age', 'gender' => 'Gender', 'client_category' => 'Client Category', 'other_category' => 'Others', 'semester' => 'Semester', 'academic_year' => 'Academic Year', 'submitted_at' => 'Submitted'] as $key => $label)
                            <div><dt>{{ $label }}</dt><dd>{{ $facilityRequest->evaluation[$key] ?? '—' }}</dd></div>
                        @endforeach
                    </dl>
                    <ol class="list-decimal pl-6 space-y-2 mt-4">
                        @foreach(\App\Models\FacilityRequest::EVALUATION_INDICATORS as $key => $label)
                            <li>{{ $label }} <strong>{{ $facilityRequest->evaluation['ratings'][$key] }}/5</strong></li>
                        @endforeach
                    </ol>
                    <p class="facility-purpose mt-4">Comments / Suggestions: {{ $facilityRequest->evaluation['comments'] ?? 'None' }}</p>
                </section>
            @endif