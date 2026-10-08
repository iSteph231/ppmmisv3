<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Facility Evaluation {{ $facilityRequest->request_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        h1 { color: #003087; font-size: 22px; margin-bottom: 8px; }
        h2 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; vertical-align: top; }
        th { width: 28%; background: #f1f5f9; }
        .purpose { white-space: pre-wrap; overflow-wrap: break-word; }
        img { max-width: 100%; max-height: 280px; }
        @page { margin: 30px; }
        .evaluation-sheet { color: #000; font-size: 10px; }
        .evaluation-sheet h1 { font-size: 19px; color: #000; margin: 0 0 5px; }
        .evaluation-sheet table { margin: 0; border: 1px solid #000; }
        .evaluation-sheet th, .evaluation-sheet td { border: 1px solid #000; padding: 6px; width: auto; vertical-align: middle; }
        .evaluation-sheet th { background: #eee; }
        .evaluation-header td { height: 75px; }
        .evaluation-ratings th { text-align: center; }
        .evaluation-ratings .rating-mark { text-align: center; width: 7%; font-weight: bold; }
        .evaluation-ratings tr { page-break-inside: avoid; }
        .evaluation-comments { border: 1px solid #000; padding: 8px; min-height: 80px; }
        .usage-photos { page-break-before: always; }
        .usage-photo { page-break-inside: avoid; margin-bottom: 20px; text-align: center; }
        .usage-photo img { max-width: 100%; max-height: 280px; }
    </style>
</head>
<body>
    @include('facility-requests.evaluation-pdf')
    <div class="usage-photos">
        <h1>Usage Photos</h1>
        <p>{{ $facilityRequest->request_number }} · {{ $facilityRequest->facility }}</p>
        @foreach(['before' => 'Before Use', 'after' => 'After Use'] as $stage => $label)
            <div class="usage-photo">
                <h2>{{ $label }}</h2>
                @if(!empty($photos[$stage]))
                    <img src="{{ $photos[$stage] }}" alt="{{ $label }}">
                @else
                    <p>Photo unavailable.</p>
                @endif
            </div>
        @endforeach
    </div>
</body>
</html>
