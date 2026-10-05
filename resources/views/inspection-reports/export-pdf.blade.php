<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inspection Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            color: #000;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        .page {
            min-height: 190mm;
            width: 277mm;
        }

        .document-code {
            font-size: 8px;
            font-style: italic;
            line-height: 1.25;
            margin-bottom: 5mm;
            text-align: right;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .main {
            border: 2px solid #000;
            table-layout: fixed;
        }

        .main td,
        .main th {
            border: 1px solid #000;
        }

        .header-logo {
            height: 24mm;
            text-align: center;
            vertical-align: middle;
            width: 28mm;
        }

        .header-title {
            height: 24mm;
            text-align: center;
            vertical-align: middle;
        }

        .header-title h1 {
            font-size: 20px;
            margin: 0 0 3px;
        }

        .header-title p {
            font-size: 10px;
            margin: 0;
            text-transform: uppercase;
        }

        .report-table th {
            font-size: 9px;
            height: 8mm;
            line-height: 1.05;
            text-align: center;
            vertical-align: middle;
        }

        .report-table td {
            height: 8mm;
            line-height: 1.15;
            padding: 3px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .col-date {
            width: 9%;
        }

        .col-campus {
            width: 13%;
        }

        .col-building {
            width: 15%;
        }

        .col-room {
            width: 16%;
        }

        .col-observations,
        .col-findings,
        .col-recommendation {
            width: 15.6%;
        }

        .signature td {
            height: 31mm;
            padding: 6px;
            vertical-align: top;
            width: 50%;
        }

        .signature-title {
            font-size: 10px;
            font-weight: bold;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin: 17mm auto 0;
            padding-top: 3px;
            text-align: center;
            width: 64mm;
        }
    </style>
</head>
<body>
    @php
        $reports = isset($inspectionReports) ? $inspectionReports : collect([$inspectionReport]);
        $blankRows = max(0, 9 - $reports->count());
    @endphp

    <div class="page">
        <div class="document-code">
            FM-AD-ENG-05<br>
            Rev. 0<br>
            03-Oct-2017
        </div>

        <table class="main">
        <tr>
            <td class="header-logo">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="file://{{ public_path('images/logo.png') }}" style="width: 20mm;">
                @endif
            </td>
            <td class="header-title" colspan="6">
                <h1>INSPECTION REPORT</h1>
                <p>PANGASINAN STATE UNIVERSITY</p>
                <p>Asingan Campus</p>
            </td>
        </tr>
        <tr class="report-table">
            <th class="col-date">DATE OF<br>INSPECTION</th>
            <th class="col-campus">CAMPUS</th>
            <th class="col-building">BUILDING</th>
            <th class="col-room">FLOOR / ROOM</th>
            <th class="col-observations">OBSERVATIONS</th>
            <th class="col-findings">FINDINGS</th>
            <th class="col-recommendation">RECOMMENDATION</th>
        </tr>
        @foreach($reports as $report)
            <tr class="report-table">
                <td>{{ optional($report->actual_inspection_date ?? $report->scheduled_date)->format('m/d/Y') }}</td>
                <td>Asingan</td>
                <td>{{ $report->workRequest->building_name ?? '' }}</td>
                <td>{{ $report->workRequest->office_room ?? '' }}</td>
                <td>{{ $report->observation ?? '' }}</td>
                <td>{{ $report->findings ?? '' }}</td>
                <td>{{ $report->recommendations ?? '' }}</td>
            </tr>
        @endforeach
        @for($row = 0; $row < $blankRows; $row++)
            <tr class="report-table">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
        <tr class="signature">
            <td colspan="4">
                <div class="signature-title">INSPECTED AND PREPARED BY:</div>
                <div class="signature-line">Signature over Printed Name / Date</div>
            </td>
            <td colspan="3">
                <div class="signature-title">NOTED BY:</div>
                <div class="signature-line">Signature over Printed Name / Date</div>
            </td>
        </tr>
        </table>
    </div>
</body>
</html>
