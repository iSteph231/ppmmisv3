<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory of Water Tank</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f0f0f0;
            padding: 20px;
        }

        .inventory-card {
            background-color: #fff;
            border: 2px solid #000;
            max-width: 1300px;
            margin: 0 auto;
        }

        /* Top Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
        }

        .logo-box {
            width: 110px;
            border-right: 2px solid #000;
            text-align: center;
            padding: 6px;
            vertical-align: middle;
        }

        .logo-box img {
            width: 85px;
            height: 85px;
            object-fit: contain;
        }

        .title-box {
            text-align: center;
            padding: 10px;
            vertical-align: middle;
        }

        .title-box h1 {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .title-box h2 {
            font-size: 11px;
            font-weight: normal;
            line-height: 1.3;
        }

        /* Table Design */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 10px;
        }

        .grid-table th, 
        .grid-table td {
            border: 1px solid #000;
            padding: 3px 2px;
            height: 22px;
        }

        .grid-table th {
            font-weight: bold;
            text-transform: uppercase;
            background-color: #ffffff;
        }

        /* Responsive Print */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .inventory-card {
                border: 2px solid #000;
            }
        }
    </style>
</head>
<body>

<div class="inventory-card">
    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="logo-box">
                <img src="{{ asset('images/psu-logo.jpg') }}" alt="PSU Logo">
            </td>
            <td class="title-box">
                <h1>INVENTORY OF WATER TANK</h1>
                <h2>PANGASINAN STATE UNIVERSITY<br>Asingan Campus</h2>
            </td>
        </tr>
    </table>

    <!-- Main Table -->
    <table class="grid-table">
        <thead>
            <!-- Header Row 1 -->
            <tr>
                <th colspan="2">LOCATION</th>
                <th rowspan="2" style="width: 8%;">WATER TANK NO.</th>
                <th colspan="2">INSPECTION</th>
                <th colspan="2">MAINTENANCE</th>
                <th rowspan="2" style="width: 10%;">INSPECTED BY</th>
                <th colspan="2">INSPECTION</th>
                <th colspan="2">MAINTENANCE</th>
                <th rowspan="2" style="width: 10%;">INSPECTED BY</th>
            </tr>
            <!-- Header Row 2 -->
            <tr>
                <th style="width: 9%;">Bldg. Name</th>
                <th style="width: 9%;">Room/Office</th>

                <th style="width: 7%;">Date</th>
                <th style="width: 8%;">Condition</th>
                <th style="width: 7%;">Repaired</th>
                <th style="width: 7%;">Replaced</th>

                <th style="width: 7%;">Date</th>
                <th style="width: 8%;">Condition</th>
                <th style="width: 7%;">Repaired</th>
                <th style="width: 7%;">Replaced</th>
            </tr>
        </thead>
        <tbody>
            {{-- Blank rows --}}
            @for ($i = 0; $i < 18; $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>

</body>
</html>