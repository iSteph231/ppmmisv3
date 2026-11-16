<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Requests Batch Export</title>
    @include('work-requests.pdf-styles')
</head>
<body>
    @forelse($workRequests as $workRequest)
        @include('work-requests.pdf-form', ['workRequest' => $workRequest])
    @empty
        <p>No work requests found for export.</p>
    @endforelse
</body>
</html>