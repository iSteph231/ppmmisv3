<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PPMMIS Login</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" href="{{ asset('css/login.css').'?v='.substr(hash_file('sha256', public_path('css/login.css')), 0, 16) }}">

</head>

<body>

<div class="container">

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

<!-- RIGHT PANEL -->
<div class="right-panel">

<div class="login-box">

<h2>Login</h2>

<!-- SUCCESS MESSAGES -->
@if(session('success'))
    <div class="alert alert-success" id="alert-message">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
        <span class="close-alert" onclick="this.parentElement.remove()">&times;</span>
    </div>
@endif

<!-- ERROR MESSAGES -->
@if(session('error'))
    <div class="alert alert-error" id="alert-message">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
        <span class="close-alert" onclick="this.parentElement.remove()">&times;</span>
    </div>
@endif

<!-- VALIDATION ERRORS -->
@if($errors->any())
    <div class="alert alert-error" id="alert-message">
        <i class="fas fa-exclamation-circle"></i>
        <span>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </span>
        <span class="close-alert" onclick="this.parentElement.remove()">&times;</span>
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
@csrf

<!-- EMAIL -->
<div class="input-group">
    <input type="email" name="email" id="email" required placeholder=" " value="{{ old('email') }}">
    <label for="email">Email (@psu.edu.ph)</label>
</div>

<!-- PASSWORD -->
<div class="input-group password-wrapper">
    <input type="password" name="password" id="password" required placeholder=" ">
    <label for="password">Password</label>
    <i class="fa-solid fa-eye toggle" id="togglePassword"></i>
</div>

<!-- OPTIONS -->
<div class="options">
    <label class="remember">
        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
        <span>Remember me</span>
    </label>

    <a class="forgot" href="{{ route('password.request') }}">Forgot Password?</a>
</div>

<!-- BUTTON -->
<button type="submit">LOGIN</button>

<!-- REGISTER -->
<div class="register-section">
    <p>Need an account?</p>
    <a href="{{ route('register') }}" class="register-btn">Request an Account</a>
</div>

</form>

</div>
</div>
</div>

<!-- SCRIPT -->
<script>
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});

document.addEventListener("DOMContentLoaded", function(){

const password = document.getElementById("password");
const toggle = document.getElementById("togglePassword");

/* SHOW ICON WHEN TYPING */
password.addEventListener("input", function(){
    toggle.style.display = password.value.length ? "block" : "none";
});

/* TOGGLE PASSWORD */
toggle.addEventListener("click", function(){

    if(password.type === "password"){
        password.type = "text";
        toggle.classList.replace("fa-eye","fa-eye-slash");
    } else {
        password.type = "password";
        toggle.classList.replace("fa-eye-slash","fa-eye");
    }

});

/* AUTO-HIDE ALERTS AFTER 5 SECONDS */
const alerts = document.querySelectorAll('.alert');
alerts.forEach(alert => {
    setTimeout(() => {
        if (alert) {
            alert.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => {
                if (alert) alert.remove();
            }, 300);
        }
    }, 5000);
});

});
</script>

</body>
</html>
