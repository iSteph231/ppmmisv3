<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request an Account - PPMMIS</title>
    <link rel="stylesheet" href="{{ asset('css/account-request.css') }}">
</head>
<body>
<main class="request-card">
    <section class="request-brand" aria-label="PPMMIS">
        <img src="{{ asset('images/logo.png') }}" alt="PSU Logo" width="90" height="90">
        <h1>PPMMIS</h1>
        <p>Physical Plant Maintenance and Management Information System</p>
    </section>
    <section class="request-form">
        <h2>Request an Account</h2>
        <p>An administrator will review your request. Once approved, we will email you a link to set your password.</p>
        @if(session('success'))
            <div class="notice" role="status">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('register') }}">
            @csrf
            @foreach(['name' => 'Full name', 'email' => 'PSU email address', 'department' => 'Department (optional)'] as $field => $label)
                <div class="field">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ old($field) }}" maxlength="255" @required($field !== 'department') @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                    @error($field)<p id="{{ $field }}-error" class="error">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <button type="submit">Submit account request</button>
        </form>
        <p class="login-link">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </section>
</main>
</body>
</html>
