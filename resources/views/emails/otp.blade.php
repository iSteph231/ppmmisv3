<!DOCTYPE html>
<html>
<head>
    <title>Email Verification - PPMMIS</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #f9f9f9; border-radius: 10px; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white;">
            <h1 style="margin: 0;">PPMMIS</h1>
            <p style="margin: 10px 0 0;">Property & Procurement Management System</p>
        </div>
        
        <div style="padding: 30px;">
            <h2>Email Verification</h2>
            <p>Hello {{ $name ?? 'User' }},</p>
            <p>Thank you for registering with PPMMIS. Please use the following One-Time Password (OTP) to verify your email address:</p>
            
            <div style="background: #f0f2f6; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;">
                <h1 style="font-size: 32px; letter-spacing: 5px; color: #667eea; margin: 0;">{{ $otp }}</h1>
            </div>
            
            <p>This OTP is valid for <strong>10 minutes</strong>.</p>
            <p>If you didn't request this verification, please ignore this email.</p>
            
            <hr style="margin: 20px 0;">
            <p style="color: #666; font-size: 12px;">&copy; {{ date('Y') }} PPMMIS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>