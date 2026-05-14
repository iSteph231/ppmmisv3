{{-- resources/views/auth/verify-otp.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PPMMIS - Verify Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verification-card {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .card-header h2 {
            margin: 0 0 10px 0;
            font-size: 1.8rem;
        }

        .card-header p {
            margin: 0;
            opacity: 0.9;
        }

        .card-body {
            padding: 40px 30px;
        }

        .email-display {
            background: #f0f2f6;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 500;
            color: #333;
            word-break: break-all;
        }

        .email-display i {
            color: #667eea;
            margin-right: 8px;
        }

        .otp-input-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .otp-input {
            width: 55px;
            height: 65px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .otp-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .timer {
            text-align: center;
            margin: 20px 0;
            font-size: 0.9rem;
        }

        .timer-countdown {
            font-size: 1.2rem;
            font-weight: bold;
            color: #667eea;
        }

        .btn-verify {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }

        .btn-verify:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        }

        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .resend-container {
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .resend-button {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .resend-button:hover:not(:disabled) {
            color: #764ba2;
            text-decoration: underline;
        }

        .resend-button:disabled {
            color: #999;
            cursor: not-allowed;
        }

        .resend-timer {
            display: inline-block;
            margin-left: 8px;
            font-weight: bold;
            color: #764ba2;
            background: #f0f2f6;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .loader {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .info-text {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #004085;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <div class="card-header">
            <i class="fas fa-envelope fa-3x mb-3"></i>
            <h2>Verify Your Email</h2>
            <p>Please enter the verification code sent to your email</p>
        </div>
        <div class="card-body">
            <div class="email-display">
                <i class="fas fa-envelope"></i>
                <span id="emailDisplay">{{ $email }}</span>
            </div>

            <div id="alertMessage" class="alert"></div>

            <form id="otpForm">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                
                <div class="otp-input-container">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp1" autofocus>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp2">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp3">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp4">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp5">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="otp6">
                </div>

                <div class="timer">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Code expires in </span>
                    <span class="timer-countdown" id="otpTimer">10:00</span>
                </div>

                <button type="submit" class="btn-verify" id="verifyBtn">
                    <i class="fas fa-check-circle"></i> Verify Email
                </button>

                <div class="resend-container">
                    <button type="button" class="resend-button" id="resendBtn" disabled>
                        <i class="fas fa-redo-alt"></i> Resend Code
                    </button>
                    <span class="resend-timer" id="resendTimer"></span>
                </div>

                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    You can request a new code after <strong>30 seconds</strong>
                </div>
            </form>
        </div>
    </div>

    <script>
        // OTP Auto-focus and move between inputs
        const inputs = document.querySelectorAll('.otp-input');
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            // Allow paste functionality
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const pasteDigits = paste.replace(/\D/g, '').slice(0, 6);
                
                for (let i = 0; i < pasteDigits.length; i++) {
                    if (inputs[i]) {
                        inputs[i].value = pasteDigits[i];
                    }
                }
                
                if (pasteDigits.length === 6) {
                    document.getElementById('verifyBtn').focus();
                }
            });
        });

        // OTP Expiration Timer (10 minutes = 600 seconds)
        let otpTimeLeft = 600;
        const otpTimerElement = document.getElementById('otpTimer');
        
        function updateOtpTimer() {
            const minutes = Math.floor(otpTimeLeft / 60);
            const seconds = otpTimeLeft % 60;
            otpTimerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (otpTimeLeft <= 0) {
                clearInterval(otpTimerInterval);
                otpTimerElement.textContent = '00:00';
                showMessage('OTP has expired. Please request a new code.', 'danger');
                // Enable resend button when OTP expires
                if (resendCooldown <= 0) {
                    resendBtn.disabled = false;
                    resendTimerElement.textContent = '';
                }
            } else {
                otpTimeLeft--;
            }
        }
        
        let otpTimerInterval = setInterval(updateOtpTimer, 1000);
        
        // Resend Cooldown Timer (30 seconds)
        let resendCooldown = 30; // START WITH 30 SECONDS COOLDOWN IMMEDIATELY
        const resendBtn = document.getElementById('resendBtn');
        const resendTimerElement = document.getElementById('resendTimer');
        
        function updateResendTimer() {
            if (resendCooldown > 0) {
                resendBtn.disabled = true;
                resendTimerElement.textContent = `${resendCooldown}s`;
                resendCooldown--;
                setTimeout(updateResendTimer, 1000);
            } else {
                resendBtn.disabled = false;
                resendTimerElement.textContent = '';
                // Add a subtle visual cue that resend is now available
                resendBtn.style.color = '#667eea';
                resendBtn.style.fontWeight = 'bold';
            }
        }
        
        // START THE 30-SECOND COOLDOWN IMMEDIATELY WHEN PAGE LOADS
        updateResendTimer();
        
        // Show message function
        function showMessage(message, type) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.textContent = message;
            alertDiv.className = `alert alert-${type}`;
            alertDiv.style.display = 'block';
            
            // Auto scroll to message
            alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000);
        }
        
        // Verify OTP
        document.getElementById('otpForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Get OTP value
            let otp = '';
            inputs.forEach(input => {
                otp += input.value;
            });
            
            if (otp.length !== 6) {
                showMessage('Please enter the complete 6-digit code', 'danger');
                return;
            }
            
            const verifyBtn = document.getElementById('verifyBtn');
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = 'Verifying <span class="loader"></span>';
            
            try {
                const response = await fetch('{{ route("otp.verify") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        otp: otp
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showMessage(data.message, 'danger');
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verify Email';
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'danger');
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verify Email';
            }
        });
        
        // Resend OTP with 30-second cooldown
        resendBtn.addEventListener('click', async () => {
            // Check if cooldown is active
            if (resendCooldown > 0) {
                showMessage(`Please wait ${resendCooldown} seconds before requesting again.`, 'danger');
                return;
            }
            
            resendBtn.disabled = true;
            const originalIcon = resendBtn.innerHTML;
            resendBtn.innerHTML = 'Sending <span class="loader"></span>';
            
            try {
                const response = await fetch('{{ route("otp.resend") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        email: '{{ $email }}'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    
                    // Reset OTP timer to 10 minutes
                    otpTimeLeft = 600;
                    clearInterval(otpTimerInterval);
                    otpTimerInterval = setInterval(updateOtpTimer, 1000);
                    updateOtpTimer();
                    
                    // Clear OTP inputs
                    inputs.forEach(input => {
                        input.value = '';
                    });
                    inputs[0].focus();
                    
                    // Reset and start 30-second cooldown
                    resendCooldown = 30;
                    updateResendTimer();
                } else {
                    showMessage(data.message, 'danger');
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = originalIcon;
                }
            } catch (error) {
                showMessage('Failed to resend OTP. Please try again.', 'danger');
                resendBtn.disabled = false;
                resendBtn.innerHTML = originalIcon;
            }
        });
        
        // Optional: Auto-submit when all 6 digits are entered
        const autoSubmit = () => {
            let filled = true;
            inputs.forEach(input => {
                if (!input.value) filled = false;
            });
            if (filled) {
                document.getElementById('verifyBtn').click();
            }
        };
        
        // Add auto-submit listener to last input
        inputs[5].addEventListener('input', autoSubmit);
    </script>
</body>
</html>