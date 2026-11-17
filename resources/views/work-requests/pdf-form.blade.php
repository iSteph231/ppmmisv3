<div class="request-sheet">
    <div class="document-code">FM-AD-ENG-02<br>Rev. 0<br>03-Oct-2017</div>
    <table class="request-form">
        <tbody>
            <tr><td>
                    <table class="header"><tr>
                            <td class="logo">
                                @if(is_file(public_path('images/logo.png')))
                                    <img src="{{ public_path('images/logo.png') }}" alt="Pangasinan State University seal">
                                @else
                                    PSU
                                @endif
                            </td>
                            <td class="form-title">
                                <h1>WORK REQUEST FORM</h1>
                                <p class="university">PANGASINAN STATE UNIVERSITY</p>
                                <p class="campus-name">Asingan Campus</p>
                            </td>
                    </tr></table>
            </td></tr>
            <tr><td>
                    <table class="date-row"><tr>
                            <td><strong>DATE :</strong> {{ $workRequest->created_at?->format('F d') }}</td>
                            <td class="date-year"><strong>20__ - ___</strong></td>
                    </tr></table>
            </td></tr>
            <tr><td>
                    <table class="location-row"><tr>
                            <td class="campus-field"><strong>CAMPUS :</strong> Asingan</td>
                            <td><strong>DEPARTMENT:</strong> {{ $workRequest->department }}</td>
                    </tr></table>
            </td></tr>
            <tr><td>
                    <table class="location-row"><tr>
                            <td><strong>BUILDING NAME :</strong> {{ $workRequest->building_name }}</td>
                            <td><strong>NAME OF OFFICE / ROOM :</strong> {{ $workRequest->office_room }}</td>
                    </tr></table>
            </td></tr>
            <tr><td class="work-section">
                    <p class="section-label">WORK REQUEST:</p>
                    <table class="work-options">
                        @foreach([
                            'ocular_inspection' => ['Ocular inspection of', 'ocular_details'],
                            'installation' => ['Installation of', 'installation_details'],
                            'repair' => ['Repair of', 'repair_details'],
                            'replacement' => ['Replacement of', 'replacement_details'],
                            'others' => ['Others (specify)', 'others_details'],
                            ] as $type => [$label, $detailsField])
                            <tr>
                                <td class="option-box"><span class="checkbox">{{ $workRequest->work_type === $type ? '✓' : '' }}</span></td>
                                <td class="option-label">{{ $label }}</td>
                                <td class="option-detail">{{ $workRequest->work_type === $type ? $workRequest->{$detailsField} : '' }}</td>
                            </tr>
                        @endforeach
                    </table>
            </td></tr>
            <tr><td>
                    <table class="signature-table"><tr>
                            <td>
                                <div class="signature-label">Requestor :</div>
                                <div class="printed-name">{{ $workRequest->requester_display_name }}</div>
                                <div class="signature-caption">Signature over Printed Name</div>
                                <div class="signature-footer">Position / Designation:</div>
                            </td>
                            <td>
                                <div class="signature-label">Approved by :</div>
                                <div class="printed-name">&nbsp;</div>
                                <div class="signature-caption">Signature over Printed Name</div>
                                <div class="signature-footer">Date :</div>
                            </td>
                            <td>
                                <div class="signature-label">Work Request Accomplished by:</div>
                                <div class="printed-name">&nbsp;</div>
                                <div class="signature-caption">Signature over Printed Name</div>
                                <div class="signature-footer">Date :</div>
                            </td>
                    </tr></table>
            </td></tr>
        </tbody>
    </table>
</div>
