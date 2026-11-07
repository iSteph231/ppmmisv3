<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request an Account - PPMMIS</title>
    <link rel="stylesheet" href="{{ asset('css/login.css').'?v='.substr(hash_file('sha256', public_path('css/login.css')), 0, 16) }}">
    <link rel="stylesheet" href="{{ asset('css/account-request.css').'?v='.substr(hash_file('sha256', public_path('css/account-request.css')), 0, 16) }}">
</head>
<body>
<main class="container">
<!-- LEFT PANEL -->
<div class="left-panel">
    <div class="left-content">

        <img src="/images/logo.png" class="psu-logo" alt="PSU Logo">

        <h1>PPMMIS</h1>
        <p>Physical Plant Maintenance and Management Information System</p>

        <div class="bubbles">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>

    </div>
</div>


    <section class="right-panel">
        <div class="login-box">
            <h2>Request an Account</h2>
            <p class="form-description">An administrator will review your request. Once approved, we will email you a link to set your password.</p>
            @if(session('success'))
                <div class="alert alert-success" role="status"><span>{{ session('success') }}</span></div>
            @endif
            <form method="POST" action="{{ route('register') }}">
                @csrf
                @foreach(['name' => 'Full name', 'email' => 'Email (@psu.edu.ph)', 'department' => 'Designation'] as $field => $label)
                    <div class="input-group">
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ old($field) }}" placeholder=" " maxlength="255" required @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                        <label for="{{ $field }}">{{ $label }}</label>
                    </div>
                    @error($field)<p id="{{ $field }}-error" class="field-error">{{ $message }}</p>@enderror
                @endforeach
                <button type="submit">SUBMIT ACCOUNT REQUEST</button>
                <div class="register-section">
                    <p>Already have an account?</p>
                    <a href="{{ route('login') }}" class="register-btn">Log in</a>
                </div>
            </form>
        </div>
    </section>
</main>
</body>
</html>
