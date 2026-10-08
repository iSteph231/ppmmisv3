@php
    $evaluation = $facilityRequest->evaluation;
@endphp
<div class="evaluation-sheet">
    <table class="evaluation-header">
        <tr>
            <td style="width: 15%; text-align: center;">
                @if(!empty($logo))<img src="{{ $logo }}" alt="PSU Seal" style="width: 65px; height: 65px;">@endif
            </td>
            <td style="text-align: center;">
                <h1>FACILITY EVALUATION FORM</h1>
                <div>PANGASINAN STATE UNIVERSITY</div>
                <div>Asingan Campus</div>
            </td>
        </tr>
    </table>
    <table class="evaluation-profile">
        <tr>
            <td colspan="2">Semester: {{ $evaluation['semester'] ?? '—' }} · A.Y. {{ $evaluation['academic_year'] ?? '—' }}</td>
            <td>Date: {{ isset($evaluation['submitted_at']) ? \Carbon\Carbon::parse($evaluation['submitted_at'])->format('M d, Y') : '—' }}</td>
        </tr>
        <tr><th colspan="3">A. PROFILE OF THE RESPONDENT</th></tr>
        <tr><td colspan="3">NAME: {{ $evaluation['name'] ?? $facilityRequest->user->name }}</td></tr>
        <tr><td>AGE: {{ $evaluation['age'] ?? '—' }}</td><td colspan="2">GENDER: {{ $evaluation['gender'] ?? '—' }}</td></tr>
        <tr><td>CLIENT CATEGORY:</td><td colspan="2">
            @foreach(['Students', 'Faculty', 'Non-Teaching', 'Supplier', 'Alumni', 'Regulatory Body', 'Industry', 'Community', 'Others'] as $category)
                <span style="display: inline-block; margin-right: 10px; line-height: 1.8;">{{ ($evaluation['client_category'] ?? null) === $category ? '[X]' : '[ ]' }} {{ $category }}</span>
            @endforeach
            @if(!empty($evaluation['other_category']))<div>Others, specify: {{ $evaluation['other_category'] }}</div>@endif
        </td></tr>
        <tr><td colspan="3">FACILITY: {{ $facilityRequest->facility }}</td></tr>
        <tr><th colspan="3">B. DEGREE OF SATISFACTION ON THE SERVICE PROVIDED</th></tr>
    </table>
    @if($evaluation)
        <p><strong>DIRECTION:</strong> Below are qualifying statements to describe the facility that you regularly use. Please rate your degree of satisfaction using the scale below.</p>
        <p style="margin-left: 30px; line-height: 1.7;">
            5 – More Convenient<br>
            4 – Convenient<br>
            3 – Somewhat Convenient<br>
            2 – Less Convenient<br>
            1 – Not Convenient
        </p>
        <table class="evaluation-ratings">
            <thead>
                <tr><th rowspan="2" style="width: 65%;">INDICATORS</th><th colspan="5">DEGREE OF SATISFACTION</th></tr>
                <tr>@foreach([1, 2, 3, 4, 5] as $rating)<th>{{ $rating }}</th>@endforeach</tr>
            </thead>
            <tbody>
                @foreach(\App\Models\FacilityRequest::EVALUATION_INDICATORS as $key => $label)
                    <tr>
                        <td>{{ $loop->iteration }}. {{ $label }}</td>
                        @foreach([1, 2, 3, 4, 5] as $rating)
                            <td class="rating-mark">{{ (int) ($evaluation['ratings'][$key] ?? 0) === $rating ? 'X' : '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="evaluation-comments">
            <p>If you have further comments and/or suggestions, please do not hesitate to inform so that we can serve you better. Please write them down below.</p>
            <p class="purpose">{{ $evaluation['comments'] ?? 'No comments or suggestions.' }}</p>
        </div>
    @else
        <p>No evaluation was recorded for this completed request.</p>
    @endif
    <p style="font-size: 9px;">Request reference: {{ $facilityRequest->request_number }}</p>
</div>