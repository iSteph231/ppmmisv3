{{-- resources/views/emails/password-reset.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - PPMMIS</title>
</head>
<body style="margin: 0; padding: 0; background: #f2f5fb; font-family: Arial, Helvetica, sans-serif; color: #243044;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f2f5fb; padding: 28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 640px; background: #ffffff; border-radius: 14px; overflow: hidden; border: 1px solid #dbe6f7; box-shadow: 0 18px 45px rgba(0, 31, 91, 0.12);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #003087 0%, #001f5b 100%); padding: 30px 28px; text-align: center; border-bottom: 5px solid #f5a800;">
                            <img src="https://i.imgur.com/IvdRxpD.png" alt="Pangasinan State University Logo" width="86" height="86" style="display: block; margin: 0 auto 14px; width: 86px; height: 86px; border-radius: 50%;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; letter-spacing: 1.5px; font-weight: 800;">PPMMIS</h1>
                            <p style="margin: 8px 0 0; color: #f5a800; font-size: 14px; font-weight: 700;">Pangasinan State University</p>
                            <p style="margin: 4px 0 0; color: rgba(255, 255, 255, 0.82); font-size: 13px;">Physical Plant Maintenance and Management Information System</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 34px 34px 16px;">
                            <p style="margin: 0 0 8px; color: #64748b; font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">Account Security</p>
                            <h2 style="margin: 0 0 18px; color: #071a3f; font-size: 24px; line-height: 1.25;">Reset Your Password</h2>
                            <p style="margin: 0 0 14px; font-size: 15px; line-height: 1.7;">Hello {{ $name }},</p>
                            <p style="margin: 0; font-size: 15px; line-height: 1.7;">We received a request to reset the password for your PPMMIS account. Use the secure button below to choose a new password.</p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 18px 34px 24px;">
                            <a href="{{ $resetLink }}" style="display: inline-block; background: linear-gradient(135deg, #f5a800 0%, #ffd467 100%); color: #071a3f; padding: 14px 30px; border-radius: 999px; font-size: 15px; font-weight: 800; text-decoration: none; box-shadow: 0 12px 24px rgba(184, 117, 0, 0.22);">
                                Reset Password
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 34px 28px;">
                            <div style="background: #e8efff; border-left: 4px solid #003087; border-radius: 10px; padding: 16px 18px;">
                                <p style="margin: 0 0 8px; color: #071a3f; font-size: 14px; line-height: 1.6;"><strong>This link expires in 60 minutes.</strong></p>
                                <p style="margin: 0; color: #475569; font-size: 13px; line-height: 1.6;">If you did not request a password reset, you can safely ignore this email. Your account password will remain unchanged.</p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 22px 34px 30px; background: #f8fbff; border-top: 1px solid #e5edf9;">
                            <p style="margin: 0; color: #64748b; font-size: 12px; line-height: 1.6;">If the button does not work, copy and paste this link into your browser:</p>
                            <p style="margin: 8px 0 18px; color: #003087; font-size: 12px; line-height: 1.6; word-break: break-all;">{{ $resetLink }}</p>
                            <p style="margin: 0; color: #94a3b8; font-size: 12px;">&copy; {{ date('Y') }} PPMMIS. Pangasinan State University.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
