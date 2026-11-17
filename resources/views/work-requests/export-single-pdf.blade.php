<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Work Request - {{ $workRequest->request_number }}</title>
        @include('work-requests.pdf-styles')
    </head>
    <body>
        @include('work-requests.pdf-form', ['workRequest' => $workRequest])
    </body>
</html>
