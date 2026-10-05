<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Preventive Maintenance Schedule</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
        }

        body {
            color: #000;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        .document-code {
            font-size: 8px;
            font-style: italic;
            line-height: 1.1;
            margin-bottom: 2mm;
            text-align: right;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .main {
            border: 2px solid #000;
            page-break-inside: avoid;
            table-layout: fixed;
        }

        .main td,
        .main th {
            border: 1px solid #000;
        }

        .header-logo {
            height: 21mm;
            text-align: center;
            vertical-align: middle;
            width: 28mm;
        }

        .header-title {
            height: 21mm;
            text-align: center;
            vertical-align: middle;
        }

        .header-title h1 {
            font-size: 18px;
            margin: 0 0 2px;
        }

        .header-title p {
            font-size: 10px;
            margin: 0;
            text-transform: uppercase;
        }

        .month-row td {
            font-size: 10px;
            font-weight: bold;
            height: 7mm;
            text-align: center;
            vertical-align: middle;
        }

        .line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 32mm;
            padding: 0 2mm;
            text-align: center;
        }

        .schedule-table th {
            font-size: 9px;
            height: 7mm;
            line-height: 1.05;
            text-align: center;
            vertical-align: middle;
        }

        .schedule-table td {
            height: 6mm;
            line-height: 1.05;
            padding: 2px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .col-date {
            width: 13%;
        }

        .col-campus {
            width: 14%;
        }

        .col-activity {
            width: 19%;
        }

        .col-maintenance,
        .col-engineer {
            width: 19%;
        }

        .col-remarks {
            width: 16%;
        }

        .signature td {
            height: 22mm;
            padding: 4px;
            vertical-align: top;
            width: 33.33%;
        }

        .signature-title {
            font-size: 10px;
            font-weight: bold;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin: 10mm auto 0;
            padding-top: 3px;
            text-align: center;
            width: 56mm;
        }

        .signature-role {
            font-size: 9px;
            line-height: 1.1;
        }
    </style>
</head>
<body>
    @php
        $rowsPerPage = 14;
        $visibleMaintenanceSchedules = $maintenanceSchedules->take($rowsPerPage);
        $blankRows = max(0, $rowsPerPage - $visibleMaintenanceSchedules->count());
    @endphp

    <div class="document-code">
        FM-AD-ENG-07<br>
        Rev. 0<br>
        03-Oct-2017
    </div>

    <table class="main">
        <tr>
            <td class="header-logo">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="file://{{ public_path('images/logo.png') }}" style="width: 17mm;">
                @endif
            </td>
            <td class="header-title" colspan="5">
                <h1>PREVENTIVE MAINTENANCE SCHEDULE</h1>
                <p>PANGASINAN STATE UNIVERSITY</p>
                <p>Asingan Campus</p>
            </td>
        </tr>
        <tr class="month-row">
            <td colspan="6">
                For the month of <span class="line">{{ $monthName }}</span>, 20<span class="line" style="min-width: 8mm;">{{ $yearSuffix }}</span>
            </td>
        </tr>
        <tr class="schedule-table">
            <th class="col-date">DATE / SCHEDULE</th>
            <th class="col-campus">CAMPUS</th>
            <th class="col-activity">PREVENTIVE MAINTENANCE<br>ACTIVITY</th>
            <th class="col-maintenance">MAINTENANCE IN-CHARGE</th>
            <th class="col-engineer">ENGINEER-IN CHARGE</th>
            <th class="col-remarks">REMARKS</th>
        </tr>
        @foreach($visibleMaintenanceSchedules as $schedule)
            <tr class="schedule-table">
                <td>{{ $schedule->scheduled_date ? $schedule->scheduled_date->format('m/d/Y') : '' }}</td>
                <td>Asingan</td>
                <td>{{ $schedule->activity ?? '' }}</td>
                <td>{{ $schedule->maintenance_in_charge ?? '' }}</td>
                <td>{{ $schedule->engineer_in_charge ?? '' }}</td>
                <td>{{ $schedule->remarks ?? '' }}</td>
            </tr>
        @endforeach
        @for($row = 0; $row < $blankRows; $row++)
            <tr class="schedule-table">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
        <tr class="signature">
            <td colspan="2">
                <div class="signature-title">PREPARED BY :</div>
                <div class="signature-line">
                    <div class="signature-role">Deputy Director,<br>Physical Plant and Facilities</div>
                </div>
            </td>
            <td colspan="2">
                <div class="signature-title">SUBMITTED TO :</div>
                <div class="signature-line">
                    <div class="signature-role">University Engineer /<br>Director, Physical Plant and Facilities</div>
                </div>
            </td>
            <td colspan="2">
                <div class="signature-title">NOTED BY :</div>
                <div class="signature-line">
                    <div class="signature-role">Vice President for<br>Administration</div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
