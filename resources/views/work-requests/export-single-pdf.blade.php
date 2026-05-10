<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Work Request - {{ $workRequest->request_number }}</title>

<style>
@page {
    size: A4 landscape;
    margin: 15px;
}

body {
    font-family: Arial, sans-serif;
    font-size: 12px;
}

.wrapper {
    width: 100%;
}

.main {
    width: 100%;
    border-collapse: collapse;
    border: 2px solid #000;
}

.main td {
    border: 1px solid #000;
    padding: 8px;
    vertical-align: top;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
}

.logo-box {
    width: 80px;
    text-align: center;
}

.title {
    text-align: center;
}

.title h2 {
    margin: 0;
    font-size: 18px;
}

.title p {
    margin: 2px 0;
    font-size: 12px;
}

.line_1, .line_2, .line_3, .line_4, .line_5 {
    text-align: center;
}

.line_1 {
    display: inline-block;
    border-bottom: 2px solid #000;
    min-width: 200px;
    width: auto;
    flex: 1;
    margin-left: 5px;
}

.line_2{
    display: inline-block;
    border-bottom: 2px solid #000;
    min-width: 200px;
    width: auto;
    flex: 1;
    margin-left: 40px;
}

.line_3{
    display: inline-block;
    border-bottom: 2px solid #000;
    min-width: 200px;
    width: auto;
    flex: 1;
    margin-left: 63px;
}

.line_4{
    display: inline-block;
    border-bottom: 2px solid #000;
    min-width: 200px;
    width: auto;
    flex: 1;
    margin-left: 28px;
}

.line_5{
    display: inline-block;
    border-bottom: 2px solid #000;
    min-width: 200px;
    width: auto;
    flex: 1;
    margin-left: 28px;
}

.line-small {
    width: 120px;
    display: inline-block;
    border-bottom: 1px solid #000;
}

.line-medium {
    width: 250px;
    display: inline-block;
    border-bottom: 1px solid #000;
}


/* CHECKBOX - FIXED WITH CHECKMARK */
.checkbox {
    width: 14px;
    height: 14px;
    border: 2px solid #000;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
    background-color: white;
    vertical-align: middle;
    margin-left: 300px;
}

.checkbox.checked::before {
    content: "\2713";
    color: black;
    font-size: 12px;
    font-weight: bold;
    display: block;
    line-height: 1;
}

.work-table {
    width: 100%;
}

.work-table td {
    border: none;
    padding: 6px 0;
    vertical-align: middle;
}

.signature-table {
    width: 100%;
    border-collapse: collapse;
}

.signature-table td {
    border: 1px solid #000;
    height: 140px;
    padding: 8px;
    vertical-align: top;
}

.sign-line {
    border-bottom: 1px solid #000;
    width: 90%;
    margin: 35px auto 5px auto;
}

.center {
    text-align: center;
    font-size: 11px;
    margin-top:50px;
}
</style>
</head>
<body>

<div class="wrapper">

<table class="main">

<!-- HEADER -->
<tr>
<td colspan="2">
    <table class="header-table">
        <tr>
            <td class="logo-box">
                @if(file_exists(public_path('images/logo.png')))
                <img src="file://{{ public_path('images/logo.png') }}" style="width:70px;">
                @else
                <div style="width:70px; height:70px; border:1px solid #ccc; text-align:center; line-height:70px;">PSU</div>
                @endif
            </td>
            <td class="title">
                <h2>WORK REQUEST FORM</h2>
                <p>PANGASINAN STATE UNIVERSITY</p>
                <p>Asingan Campus</p>
            </td>
        </tr>
    </table>
</td>
</tr>

<!-- DATE -->
<tr>
<td colspan="2">
    <strong>DATE :</strong>
    {{ $workRequest->created_at ? $workRequest->created_at->format('Y-m-d') : date('Y-m-d') }}
</td>
</tr>

<!-- CAMPUS & DEPARTMENT -->
<tr>
    <td width="50%"><strong>CAMPUS :</strong> <span class="line-with-value">{{ $workRequest->campus ?? '' }}</span></td>
    <td width="50%"><strong>DEPARTMENT :</strong> <span class="line-with-value">{{ $workRequest->department ?? '' }}</span></td>
</tr>

<!-- BUILDING NAME & OFFICE/ROOM -->
<tr>
    <td><strong>BUILDING NAME :</strong> <span class="line-with-value">{{ $workRequest->building_name ?? '' }}</span></td>
    <td><strong>NAME OF OFFICE / ROOM :</strong> <span class="line-with-value">{{ $workRequest->office_room ?? '' }}</span></td>
</tr>

<!-- WORK REQUEST -->
<tr>
<td colspan="2">

<strong>WORK REQUEST:</strong>

<table class="work-table" style="margin-top: 8px;">
    <tr>
        <td width="250">
            <div class="checkbox {{ $workRequest->work_type == 'ocular' ? 'checked' : '' }}"></div>
        </td>
        <td>Ocular inspection of <span class="line_1">{{ $workRequest->work_type == 'ocular' ? ($workRequest->ocular_details ?? '') : '' }}</span></td>
    </tr>
    <tr>
        <td width="250">
            <div class="checkbox {{ $workRequest->work_type == 'installation' ? 'checked' : '' }}"></div>
        </td>
        <td>Installation of <span class="line_2">{{ $workRequest->work_type == 'installation' ? ($workRequest->installation_details ?? '') : '' }}</span></td>
    </tr>
    <tr>
        <td width="250">
            <div class="checkbox {{ $workRequest->work_type == 'repair' ? 'checked' : '' }}"></div>
        </td>
        <td>Repair of <span class="line_3">{{ $workRequest->work_type == 'repair' ? ($workRequest->repair_details ?? '') : '' }}</span></td>
    </tr>
    <tr>
        <td width="250">
            <div class="checkbox {{ $workRequest->work_type == 'replacement' ? 'checked' : '' }}"></div>
        </td>
        <td>Replacement of <span class="line_4">{{ $workRequest->work_type == 'replacement' ? ($workRequest->replacement_details ?? '') : '' }}</span></td>
    </tr>
    <tr>
        <td width="250">
            <div class="checkbox {{ $workRequest->work_type == 'others' ? 'checked' : '' }}"></div>
        </td>
        <td>Others (specify) <span class="line_5">{{ $workRequest->work_type == 'others' ? ($workRequest->others_details ?? '') : '' }}</span></td>
    </tr>
</table>

</td>
</tr>

<!-- SIGNATURES -->
<tr>
<td colspan="2" style="padding:0;">

<table class="signature-table">
    <tr>
        <td width="33%">
            <strong>Requestor :</strong>
            <div class="center">Signature over Printed Name</div>
            <p style="margin-top: 15px;">
                <strong>Position / Designation:</strong><br>
            </p>
        </td>
        <td width="33%">
            <strong>Approved by :</strong>
            <div class="center">Signature over Printed Name</div>
            <p style="margin-top: 15px;">
                <strong>Date :</strong><br>
            </p>
        </td>
        <td width="33%">
            <strong>Work Request Accomplished by :</strong>
            <div class="center">Signature over Printed Name</div>
            <p style="margin-top: 15px;">
                <strong>Date :</strong><br>
            </p>
        </td>
    </tr>
</table>

</td>
</tr>

<!-- FOOTER -->


</table>

</div>

</body>
</html>