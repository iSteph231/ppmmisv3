<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory of {{ $name }}</title>
    <style>
        @page { margin: 20px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #000; font-size: 8px; }
        .sheet { page-break-before: always; }
        .sheet:first-child { page-break-before: auto; }
        .form-wrapper { border: 2px solid #000; width: 100%; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 4px 8px; border-bottom: 2px solid #000; }
        .logo-cell { width: 90px; text-align: center; border-right: 2px solid #000; }
        .logo-cell img { width: 65px; height: 65px; }
        .title-cell { text-align: center; }
        h1 { font-size: 19px; margin: 4px 0 2px; letter-spacing: 1px; }
        .campus { font-size: 11px; font-weight: bold; margin: 0; }
        .campus-sub { font-size: 10px; margin: 0; }
        .code-cell { width: 125px; text-align: right; font-size: 9px; }
        .code-cell div { line-height: 1.4; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data-table th, .data-table td { border: 1px solid #000; text-align: center; padding: 2px; font-size: 7px; overflow-wrap: break-word; }
        .data-table th { font-weight: bold; height: 18px; }
        .data-table td { height: 13px; }
        .data-table tr { page-break-inside: avoid; }
        .data-table thead { display: table-header-group; }
        .page-number { text-align: right; font-size: 7px; margin: 4px 0; }
    </style>
</head>
<body>
    @foreach (($entries->isEmpty() ? collect([collect()]) : $entries->chunk($layout['rows'])) as $pageEntries)
        <div class="sheet">
            <div class="form-wrapper">
                <table class="header-table">
                    <tr>
                        <td class="logo-cell">
                            <img src="{{ public_path('images/inventory/'.$form.'.png') }}" alt="PSU Seal">
                        </td>
                        <td class="title-cell">
                            <h1>INVENTORY OF {{ strtoupper($name) }}</h1>
                            <p class="campus">PANGASINAN STATE UNIVERSITY</p>
                            <p class="campus-sub">Asingan Campus</p>
                        </td>
                        <td class="code-cell">
                            <div>{{ $layout['code'] }}</div>
                            <div>Rev. 0</div>
                            <div>03-Oct-2017</div>
                        </td>
                    </tr>
                </table>
                <table class="data-table">
                    @include('inventory.pdf.'.$form)
                    <tbody>
                        @foreach ($pageEntries as $entry)
                            <tr>
                                @foreach ($sections as $fields)
                                    @foreach ($fields as $key => $field)
                                        <td>{{ $field['type'] === 'checkbox' ? (array_key_exists($key, $entry->data) ? (!empty($entry->data[$key]) ? 'Yes' : 'No') : '') : ($entry->data[$key] ?? '') }}</td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                        @for ($row = $pageEntries->count(); $row < $layout['rows']; $row++)
                            <tr>
                                @foreach ($sections as $fields)
                                    @foreach ($fields as $field)
                                        <td>&nbsp;</td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <p class="page-number">Page {{ $loop->iteration }} of {{ $loop->count }}</p>
        </div>
    @endforeach
</body>
</html>