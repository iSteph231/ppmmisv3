<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PPMMIS Login</title>

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
background:#e6edf6;
display:flex;
align-items:center;
justify-content:center;
}

/* CONTAINER */
.container{
width:900px;
height:500px;
display:flex;
border-radius:15px;
overflow:hidden;
box-shadow:0 15px 40px rgba(0,0,0,0.2);
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
font-size:18px;
line-height:1.6;
}

/* RIGHT PANEL */
.right-panel{
flex:1;
background:#e6edf6;
display:flex;
align-items:center;
justify-content:center;
}

/* LOGIN BOX */
.login-box{
width:350px;
}

.login-box h2{
font-size:32px;
margin-bottom:25px;
}

/* ========================= */
/* FLOATING INPUT SYSTEM */
/* ========================= */

.input-group{
position:relative;
margin-bottom:22px;
}

/* INPUT */
.input-group input{
width:100%;
padding:18px 45px 12px 14px; /* space for eye icon */
border-radius:12px;
border:1px solid #ddd;
font-size:14px;
background:#f9fbff;
outline:none;
transition:0.3s;
}

/* LABEL */
.input-group label{
position:absolute;
left:14px;
top:50%;
transform:translateY(-50%);
font-size:14px;
color:#777;
background:#f9fbff;
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

/* VALID */
.input-group input:valid{
border-color:#27ae60;
}

/* INVALID */
.input-group input:invalid:focus{
border-color:#e74c3c;
}

/* ========================= */
/* PASSWORD + EYE ICON */
/* ========================= */

.password-wrapper{
position:relative;
}

/* EYE ICON */
.toggle{
position:absolute;
right:15px;
top:50%;
transform:translateY(-50%);
cursor:pointer;
color:#777;
font-size:18px;
display:none;
transition:0.2s;
}

.toggle:hover{
color:#2c7be5;
}

/* ========================= */
/* HIDE DEFAULT PASSWORD ICON */
/* ========================= */

input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear{
display:none;
}

input[type="password"]::-webkit-credentials-auto-fill-button{
display:none !important;
}

/* ========================= */
/* OPTIONS */
/* ========================= */

.options{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
font-size:14px;
}

.remember{
display:flex;
align-items:center;
gap:8px;
}

.forgot{
cursor:pointer;
}

/* ========================= */
/* BUTTON */
/* ========================= */

button{
width:100%;
padding:15px;
border:none;
border-radius:30px;
background:linear-gradient(135deg,#2c7be5,#4facfe);
color:white;
font-weight:600;
font-size:15px;
cursor:pointer;
transition:0.3s;
}

button:hover{
transform:translateY(-2px);
box-shadow:0 10px 25px rgba(44,123,229,0.3);
}

/* ========================= */
/* REGISTER */
/* ========================= */

.register-section{
text-align:center;
margin-top:20px;
}

.register-section p{
font-size:14px;
color:#555;
margin-bottom:8px;
}

.register-btn{
display:inline-block;
padding:10px 25px;
border-radius:20px;
background:#f0f0f0;
text-decoration:none;
color:#3B82F6;
font-weight:600;
border:1px solid #3B82F6;
transition:0.3s;
}

.register-btn:hover{
background:#ddd;
}

/* ========================= */
/* BUBBLES */
/* ========================= */

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

@keyframes rise{
0%{transform:translateY(0);opacity:0;}
50%{opacity:1;}
100%{transform:translateY(-700px);opacity:0;}
}

/* LOGO */
.psu-logo{
width:130px;
display:block;
margin:0 auto 20px auto;
}

/* ========================= */
/* RESPONSIVE */
/* ========================= */

@media(max-width:768px){

.container{
flex-direction:column;
width:90%;
height:auto;
}

.left-panel{
height:200px;
}

.login-box{
width:90%;
padding:20px;
}

}
</style>

</head>

<body>

<div class="container">

<!-- LEFT PANEL -->
<div class="left-panel">
    <div class="left-content">

        <img src="/images/logo.png" class="psu-logo">

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

<form method="POST" action="{{ route('login') }}">
@csrf

<!-- EMAIL -->
<div class="input-group">
    <input type="email" name="email" required placeholder=" ">
    <label>Email</label>
</div>

<!-- PASSWORD -->
<div class="input-group password-wrapper">
    <input type="password" name="password" id="password" required placeholder=" ">
    <label>Password</label>

    <i class="fa-solid fa-eye toggle" id="togglePassword"></i>
</div>

<!-- OPTIONS -->
<div class="options">
    <label class="remember">
        <input type="checkbox">
        <span>Remember me</span>
    </label>

    <a class="forgot" href="#">Forgot Password?</a>
</div>

<!-- BUTTON -->
<button type="submit">LOGIN</button>

<!-- REGISTER -->
<div class="register-section">
    <p>Don't have an account?</p>
    <a href="{{ route('register') }}" class="register-btn">Register</a>
</div>

</form>

</div>
</div>
</div>

<!-- SCRIPT -->
<script>
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

});
</script>

</body>
</html>