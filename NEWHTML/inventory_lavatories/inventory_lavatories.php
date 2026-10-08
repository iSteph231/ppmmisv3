<?php
// Inventory of Floor Drains - Pangasinan State University, Asingan Campus
// Form Code: FM-AD-ENG-10d, Rev. 0, 03-Oct-2017

$formCode   = "FM-AD-ENG-10d";
$revision   = "Rev. 0";
$formDate   = "03-Oct-2017";
$campus     = "PANGASINAN STATE UNIVERSITY";
$campusSub  = "Asingan Campus";
$title      = "INVENTORY OF LAVATORIES";

// Number of blank data rows to render
$numRows = 22;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $title; ?></title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        margin: 20px;
        color: #000;
    }
    .form-wrapper {
        border: 2px solid #000;
        width: 100%;
        max-width: 1050px;
        margin: 0 auto;
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
    }
    .header-table td {
        vertical-align: middle;
        padding: 4px 8px;
    }
    .logo-cell {
        width: 110px;
        text-align: center;
        border-right: 2px solid #000;
        border-bottom: 2px solid #000;
    }
    .logo-cell img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
    }
    .logo-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 2px solid #1a3a8f;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
        text-align: center;
        color: #1a3a8f;
        font-weight: bold;
    }
    .title-cell {
        text-align: center;
        border-bottom: 2px solid #000;
    }
    .title-cell h1 {
        font-size: 22px;
        margin: 4px 0 2px 0;
        letter-spacing: 1px;
    }
    .title-cell .campus {
        font-size: 12px;
        font-weight: bold;
        margin: 0;
    }
    .title-cell .campus-sub {
        font-size: 11px;
        margin: 0;
    }
    .code-cell {
        width: 140px;
        text-align: right;
        font-size: 10px;
        border-bottom: 2px solid #000;
        vertical-align: top;
        padding-top: 6px;
    }
    .code-cell div { line-height: 1.4; }

    table.data-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    table.data-table th, table.data-table td {
        border: 1px solid #000;
        text-align: center;
        padding: 3px 4px;
        font-size: 10px;
        height: 20px;
    }
    table.data-table th {
        font-weight: bold;
        background-color: #fff;
    }
    table.data-table td.blank {
        height: 22px;
    }
    input.cell-input {
        width: 100%;
        border: none;
        text-align: center;
        font-size: 10px;
        font-family: Arial, Helvetica, sans-serif;
        background: transparent;
    }
    input.cell-input:focus {
        outline: 1px solid #1a3a8f;
    }

    @media print {
        body { margin: 0; }
        .form-wrapper { border: 2px solid #000; }
    }
</style>
</head>
<body>

<div class="form-wrapper">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-cell" rowspan="1">
                <img src="psu_logo.png" alt="PSU Seal">
            </td>
            <td class="title-cell">
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <p class="campus"><?php echo htmlspecialchars($campus); ?></p>
                <p class="campus-sub"><?php echo htmlspecialchars($campusSub); ?></p>
            </td>
            <td class="code-cell">
                <div><?php echo htmlspecialchars($formCode); ?></div>
                <div><?php echo htmlspecialchars($revision); ?></div>
                <div><?php echo htmlspecialchars($formDate); ?></div>
            </td>
        </tr>
    </table>

    <!-- DATA TABLE -->
    <table class="data-table">
        <colgroup>
            <col style="width:8%">
            <col style="width:9%">
            <col style="width:7%">
            <col style="width:6.5%">
            <col style="width:7%">
            <col style="width:6.5%">
            <col style="width:7%">
            <col style="width:6.5%">
            <col style="width:6.5%">
            <col style="width:6.5%">
            <col style="width:7%">
            <col style="width:6.5%">
            <col style="width:7%">
            <col style="width:6.5%">
            <col style="width:6.5%">
        </colgroup>
        <thead>
            <tr>
                <th colspan="2">LOCATION</th>
                <th rowspan="2">LAVATORY/<br>SINK NO.</th>
                <th colspan="2">INSPECTION</th>
                <th colspan="3">MAINTENANCE</th>
                <th rowspan="2">INSPECTED<br>BY</th>
                <th colspan="2">INSPECTION</th>
                <th colspan="3">MAINTENANCE</th>
                <th rowspan="2">INSPECTED<br>BY</th>
            </tr>
            <tr>
                <th>Bldg. Name</th>
                <th>Room/Office</th>
                <th>Date</th>
                <th>Condition</th>
                <th>Cleaned</th>
                <th>Declogged</th>
                <th>Replaced</th>
                <th>Date</th>
                <th>Condition</th>
                <th>Cleaned</th>
                <th>Declogged</th>
                <th>Replaced</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < $numRows; $i++): ?>
            <tr>
                <?php for ($c = 0; $c < 15; $c++): ?>
                <td class="blank"><input type="text" class="cell-input" name="row<?php echo $i; ?>_col<?php echo $c; ?>"></td>
                <?php endfor; ?>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

</div>

</body>
</html>