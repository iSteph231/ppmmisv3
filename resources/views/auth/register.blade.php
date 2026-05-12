<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PPMMIS Register</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* RESET */
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Segoe UI, sans-serif;
}

/* BODY */
body{
height:100vh;
background:#f5f7fb;
display:flex;
align-items:center;
justify-content:center;
}

/* CONTAINER */
.container{
width:900px;
height:580px;
display:flex;
border-radius:18px;
overflow:hidden;
box-shadow:0 20px 50px rgba(0,0,0,0.15);
}

/* LEFT PANEL */
.left-panel{
flex:1;
background:linear-gradient(135deg,#5576E6,#56B3C9);
color:white;
display:flex;
align-items:center;
justify-content:center;
position:relative;
overflow:hidden;
}

.left-content{
text-align:center;
max-width:420px;
z-index:1;
}

.left-content h1{
font-size:64px;
letter-spacing:8px;
margin-bottom:20px;
}

.left-content p{
font-size:16px;
line-height:1.6;
opacity:0.9;
}

/* RIGHT PANEL */
.right-panel{
flex:1;
background:#ffffff;
display:flex;
align-items:center;
justify-content:center;
overflow-y:auto;
}

/* REGISTER BOX */
.register-box{
width:360px;
padding:20px 0;
}

.register-box h2{
font-size:28px;
margin-bottom:25px;
font-weight:600;
}

/* FLOATING INPUT GROUP */
.input-group{
position:relative;
margin-bottom:20px;
}

/* INPUT */
.input-group input{
width:100%;
padding:18px 16px 10px 16px;
border-radius:12px;
border:1px solid #ddd;
font-size:14px;
background:#f9fbff;
transition:0.3s;
outline:none;
}

/* LABEL */
.input-group label{
position:absolute;
left:14px;
top:50%;
transform:translateY(-50%);
font-size:14px;
color:#777;
background:white;
padding:0 5px;
transition:0.3s;
pointer-events:none;
}

/* FLOAT EFFECT */
.input-group input:focus + label,
.input-group input:not(:placeholder-shown) + label{
top:-8px;
font-size:12px;
color:#2c7be5;
}

/* INPUT FOCUS */
.input-group input:focus{
border-color:#2c7be5;
box-shadow:0 0 0 3px rgba(44,123,229,0.15);
}

/* PASSWORD WRAPPER */
.password-wrapper{
position:relative;
}

/* RIGHT ICON */
.toggle{
position:absolute;
right:14px;
top:50%;
transform:translateY(-50%);
cursor:pointer;
color:#777;
font-size:18px;
display:none;
}

/* FIX INPUT SPACE */
.password-wrapper input{
padding-right:45px;
}

/* BUTTON */
button{
width:100%;
padding:15px;
border:none;
border-radius:30px;
background:linear-gradient(135deg,#5576E6,#56B3C9);
color:white;
font-weight:600;
font-size:15px;
cursor:pointer;
transition:0.3s;
margin-top:10px;
}

button:hover{
transform:translateY(-2px);
box-shadow:0 10px 25px rgba(44,123,229,0.3);
}

button:disabled{
opacity:0.6;
cursor:not-allowed;
}

/* LOGIN LINK */
.login-link{
text-align:center;
margin-top:15px;
font-size:14px;
}

.login-link a{
color:#2c7be5;
text-decoration:none;
font-weight:600;
}

/* LOGO */
.psu-logo{
width:120px;
margin-bottom:20px;
display:block;
margin-left:auto;
margin-right:auto;
}

/* BUBBLES */
.bubbles{
position:absolute;
width:100%;
height:100%;
top:0;
left:0;
overflow:hidden;
z-index:0;
}

.bubbles span{
position:absolute;
bottom:-120px;
background:rgba(255,255,255,0.15);
border-radius:50%;
animation:rise 20s infinite ease-in;
}

.bubbles span:nth-child(1){
left:20%;
width:80px;
height:80px;
animation-duration:10s;
}

.bubbles span:nth-child(2){
left:40%;
width:50px;
height:50px;
animation-duration:13s;
}

.bubbles span:nth-child(3){
left:65%;
width:100px;
height:100px;
animation-duration:15s;
}

.bubbles span:nth-child(4){
left:85%;
width:60px;
height:60px;
animation-duration:18s;
}

/* ANIMATION */
@keyframes rise{
0%{transform:translateY(0);opacity:0;}
50%{opacity:1;}
100%{transform:translateY(-700px);opacity:0;}
}
/* STRENGTH BAR */
.strength-bar{
height:6px;
background:#eee;
border-radius:5px;
margin-top:6px;
overflow:hidden;
}

#strengthFill{
height:100%;
width:0%;
background:red;
transition:0.3s;
}

/* TEXT FEEDBACK */
small{
display:block;
margin-top:5px;
font-size:12px;
}

/* COLORS */
.weak{ color:#e74c3c; }
.medium{ color:#f39c12; }
.strong{ color:#27ae60; }
.match{ color:#27ae60; }
.not-match{ color:#e74c3c; }
.error-message{ color:#e74c3c; font-size:12px; margin-top:5px; display:block; }

/* Email error specific */
.email-error-container {
    margin-top: -15px;
    margin-bottom: 15px;
}

.email-error {
    color: #e74c3c;
    font-size: 12px;
    display: none;
    margin-top: 5px;
}
</style>

</head>

<body>

<div class="container">

<!-- LEFT SIDE -->
<div class="left-panel">
<div class="left-content">

<img src="/images/logo.png" class="psu-logo" alt="PSU Logo">

<h1>PPMMIS</h1>
<p>Physical Plant Maintenance and Management Information System</p>

<div class="bubbles">
<span></span><span></span><span></span><span></span>
</div>

</div>
</div>

<!-- RIGHT SIDE -->
<div class="right-panel">

<div class="register-box">

<h2>Create Account</h2>

<form method="POST" action="{{ route('register') }}">
@csrf

<!-- FULL NAME -->
<div class="input-group">
    <input type="text" name="name" id="name" required placeholder=" " value="{{ old('name') }}">
    <label>Full Name</label>
</div>
@error('name')
    <small class="error-message">{{ $message }}</small>
@enderror

<!-- EMAIL -->
<div class="input-group">
    <input type="email" name="email" id="email" required placeholder=" " value="{{ old('email') }}">
    <label>Email (@psu.edu.ph)</label>
</div>
<div class="email-error-container">
    <small class="email-error" id="emailError">❌ Only @psu.edu.ph email addresses are allowed</small>
</div>
@error('email')
    <small class="error-message">❌ {{ $message }}</small>
@enderror

<!-- PASSWORD -->
<div class="input-group password-wrapper">
    <input type="password" name="password" id="password" required placeholder=" ">
    <label>Password</label>
    <i class="fa-solid fa-eye toggle" id="togglePassword"></i>
    <small id="strengthText"></small>
    <div class="strength-bar">
        <div id="strengthFill"></div>
    </div>
</div>

<!-- CONFIRM PASSWORD -->
<div class="input-group password-wrapper">
    <input type="password" name="password_confirmation" id="confirmPassword" required placeholder=" ">
    <label>Confirm Password</label>
    <small id="matchText"></small>
</div>

<!-- BUTTON -->
<button type="submit" id="registerBtn">REGISTER</button>

</form>

<!-- LOGIN LINK -->
<div class="login-link">
Already have an account?
<a href="{{ route('login') }}">Login</a>
</div>

</div>
</div>

</div>

<!-- SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", function(){

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");
const email = document.getElementById("email");
const toggle = document.getElementById("togglePassword");

const strengthText = document.getElementById("strengthText");
const strengthFill = document.getElementById("strengthFill");
const matchText = document.getElementById("matchText");
const emailError = document.getElementById("emailError");
const btn = document.getElementById("registerBtn");

/* EMAIL VALIDATION - Only @psu.edu.ph */
function validateEmail() {
    const emailValue = email.value;
    if (emailValue && !emailValue.endsWith('@psu.edu.ph')) {
        emailError.style.display = 'block';
        email.setCustomValidity('Only @psu.edu.ph email addresses are allowed');
        return false;
    } else {
        emailError.style.display = 'none';
        email.setCustomValidity('');
        return true;
    }
}

email.addEventListener('input', validateEmail);
email.addEventListener('blur', validateEmail);

/* PASSWORD TOGGLE */
password.addEventListener("input", function(){
    toggle.style.display = password.value.length > 0 ? "block" : "none";
});

toggle.addEventListener("click", function(){
    if(password.type === "password"){
        password.type = "text";
        toggle.classList.replace("fa-eye","fa-eye-slash");
    } else {
        password.type = "password";
        toggle.classList.replace("fa-eye-slash","fa-eye");
    }
});

/* PASSWORD STRENGTH */
password.addEventListener("input", function(){

let val = password.value;
let strength = 0;

if(val.length >= 8) strength++;
if(/[A-Z]/.test(val)) strength++;
if(/[0-9]/.test(val)) strength++;
if(/[@$!%*?&]/.test(val)) strength++;

if(val.length === 0){
    strengthFill.style.width = "0%";
    strengthText.innerText = "";
    return;
}

if(strength <= 1){
    strengthFill.style.width = "33%";
    strengthFill.style.background = "#e74c3c";
    strengthText.innerText = "Weak password - Use 8+ chars, uppercase, numbers";
    strengthText.className = "weak";
}
else if(strength == 2){
    strengthFill.style.width = "66%";
    strengthFill.style.background = "#f39c12";
    strengthText.innerText = "Medium strength - Add uppercase or numbers";
    strengthText.className = "medium";
}
else{
    strengthFill.style.width = "100%";
    strengthFill.style.background = "#27ae60";
    strengthText.innerText = "Strong password";
    strengthText.className = "strong";
}

});

/* PASSWORD MATCH & BUTTON STATE */
function validateForm() {
    let isValid = true;
    
    // Email validation
    if (!validateEmail()) {
        isValid = false;
    }
    
    // Check if email is empty
    if (email.value === "") {
        isValid = false;
    }
    
    // Password match validation
    if (confirmPassword.value !== "" && confirmPassword.value !== password.value) {
        isValid = false;
    }
    
    // Password length validation
    if (password.value.length > 0 && password.value.length < 8) {
        isValid = false;
    }
    
    // Check if passwords are empty
    if (password.value === "" || confirmPassword.value === "") {
        isValid = false;
    }
    
    btn.disabled = !isValid;
}

confirmPassword.addEventListener("input", function(){

if(confirmPassword.value === ""){
    matchText.innerText = "";
    validateForm();
    return;
}

if(confirmPassword.value === password.value){
    matchText.innerText = "✓ Passwords match";
    matchText.className = "match";
    validateForm();
} else {
    matchText.innerText = "✗ Passwords do not match";
    matchText.className = "not-match";
    validateForm();
}

});

password.addEventListener("input", validateForm);
email.addEventListener("input", validateForm);

// Initial validation
validateForm();

});
</script>

</body>
</html>