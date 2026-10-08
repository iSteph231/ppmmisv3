@extends('layouts.app')

@section('title', 'Inventory - Exhaust Fan')

@push('styles')
<style>

    .inventory-form * { box-sizing: border-box; }
    .inventory-form {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        margin: 20px;
        color: #000;
    }
    .inventory-form .form-wrapper {
        border: 2px solid #000;
        width: 100%;
        max-width: 1050px;
        margin: 0 auto;
    }
    .inventory-form .header-table {
        width: 100%;
        border-collapse: collapse;
    }
    .inventory-form .header-table td {
        vertical-align: middle;
        padding: 4px 8px;
    }
    .inventory-form .logo-cell {
        width: 110px;
        text-align: center;
        border-right: 2px solid #000;
        border-bottom: 2px solid #000;
    }
    .inventory-form .logo-cell img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
    }
    .inventory-form .title-cell {
        text-align: center;
        border-bottom: 2px solid #000;
    }
    .inventory-form .title-cell h1 {
        font-size: 22px;
        margin: 4px 0 2px 0;
        letter-spacing: 1px;
    }
    .inventory-form .title-cell .campus {
        font-size: 12px;
        font-weight: bold;
        margin: 0;
    }
    .inventory-form .title-cell .campus-sub {
        font-size: 11px;
        margin: 0;
    }
    .inventory-form .code-cell {
        width: 140px;
        text-align: right;
        font-size: 10px;
        border-bottom: 2px solid #000;
        vertical-align: top;
        padding-top: 6px;
    }
    .inventory-form .code-cell div { line-height: 1.4; }

    .inventory-form table.data-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .inventory-form table.data-table th, .inventory-form table.data-table td {
        border: 1px solid #000;
        text-align: center;
        padding: 3px 4px;
        font-size: 10px;
        height: 20px;
    }
    .inventory-form table.data-table th {
        font-weight: bold;
        background-color: #fff;
    }
    .inventory-form table.data-table td.blank {
        height: 22px;
    }
    .inventory-form input.cell-input {
        width: 100%;
        border: none;
        text-align: center;
        font-size: 10px;
        font-family: Arial, Helvetica, sans-serif;
        background: transparent;
    }
    .inventory-form input.cell-input:focus {
        outline: 1px solid #1a3a8f;
    }

   @media print {
        .inventory-form { margin: 0; }
        .inventory-form .form-wrapper { border: 2px solid #000; }
    }

    .inventory-form { overflow-x: auto; }
    .inventory-form .form-wrapper { min-width: 1000px; }
    @media print {
        @page { size: landscape; margin: 10mm; }
        .sidebar, .top-nav, .inventory-toolbar { display: none !important; }
        .main-content, .content-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .inventory-form { margin: 0; overflow: visible; }
        .inventory-form .form-wrapper { min-width: 0; max-width: none; }
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="inventory-toolbar flex flex-wrap items-center justify-between gap-4 mb-6">
        <a href="{{ route('inventory.index') }}" class="btn-create">Back to Inventory</a>
        <button type="button" onclick="window.print()" class="btn-create">Print Inventory</button>
    </div>
    <div class="inventory-form">


<div class="form-wrapper">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-cell" rowspan="1">
                <img src="{{ asset('images/inventory/exhaust-fan.png') }}" alt="PSU Seal">
            </td>
            <td class="title-cell">
                <h1>INVENTORY OF EXHAUST FAN</h1>
                <p class="campus">PANGASINAN STATE UNIVERSITY</p>
                <p class="campus-sub">Asingan Campus</p>
            </td>
            <td class="code-cell">
                <div>FM-AD-ENG-09b</div>
                <div>Rev. 0</div>
                <div>03-Oct-2017</div>
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
                <th rowspan="2">EXHAUST<br>FAN NO.</th>
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
                <th>Repaired</th>
                <th>Replaced</th>
                <th>Date</th>
                <th>Condition</th>
                <th>Cleaned</th>
                <th>Repaired</th>
                <th>Replaced</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < 30; $i++)
            <tr>
                @for ($c = 0; $c < 15; $c++)
                <td class="blank"><input type="text" class="cell-input" aria-label="Row {{ $i + 1 }}, column {{ $c + 1 }}" name="row{{ $i }}_col{{ $c }}"></td>
                @endfor
            </tr>
            @endfor
        </tbody>
    </table>

</div>


    </div>
</div>
@endsection