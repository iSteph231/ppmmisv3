<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PPMMIS - Forgot Password</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', sans-serif;
    }

    body {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .container {
        width: 450px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        overflow: hidden;
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .header {
        background: linear-gradient(135deg, #5576E6, #56B3C9);
        color: white;
        text-align: center;
        padding: 40px 30px;
    }

    .header h1 {
        font-size: 32px;
        margin-bottom: 10px;
    }

    .header p {
        font-size: 14px;
        opacity: 0.9;
    }

    .content {
        padding: 40px;
    }

    .content h2 {
        font-size: 24px;
        margin-bottom: 10px;
        color: #333;
    }

    .info-text {
        color: #666;
        font-size: 14px;
        margin-bottom: 25px;
        line-height: 1.6;
    }

    .input-group {
        margin-bottom: 25px;
    }

    .input-group input {
        width: 100%;
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.3s;
        outline: none;
    }

    .input-group input:focus {
        border-color: #5576E6;
        box-shadow: 0 0 0 3px rgba(85,118,230,0.1);
    }

    .input-group label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-size: 14px;
        font-weight: 500;
    }

    button {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #5576E6, #56B3C9);
        color: white;
        border: none;
        border-radius: 30px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        margin-top: 10px;
    }

    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(85,118,230,0.3);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .back-link {
        text-align: center;
        margin-top: 25px;
    }

    .back-link a {
        color: #5576E6;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .back-link a:hover {
        text-decoration: underline;
    }

    .psu-logo {
        width: 80px;
        margin-bottom: 15px;
    }
</style>

</head>

<body>

<div class="container">
    <div class="header">
        <img src="/images/logo.png" class="psu-logo" alt="PSU Logo">
        <h1>PPMMIS</h1>
        <p>Password Recovery</p>
    </div>

    <div class="content">
        <h2>Forgot Password?</h2>
        <p class="info-text">Enter your registered email address and we'll send you a link to reset your password.</p>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="yourname@psu.edu.ph" required>
            </div>

            <button type="submit">Send Reset Link</button>

            <div class="back-link">
                <a href="{{ route('login') }}">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>