{{-- resources/views/auth/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PPMMIS - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --psu-blue: #003087;
            --psu-blue-dark: #001f5b;
            --psu-blue-deep: #071a3f;
            --psu-blue-soft: #e8efff;
            --psu-gold: #f5a800;
            --psu-gold-soft: #fff4cc;
            --text: #243044;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(245, 168, 0, 0.18), transparent 30rem),
                linear-gradient(135deg, #f8fbff 0%, #f2f5fb 45%, #edf3ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text);
        }

        .reset-card {
            max-width: 480px;
            width: 100%;
            background: white;
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, 0.09);
            box-shadow: 0 24px 60px rgba(0, 31, 91, 0.18);
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
            background: linear-gradient(135deg, var(--psu-blue) 0%, var(--psu-blue-dark) 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: 5px solid var(--psu-gold);
        }

        .brand-logo {
            width: 86px;
            height: 86px;
            border-radius: 50%;
            margin-bottom: 14px;
        }

        .card-header h2 {
            margin: 0 0 10px 0;
            font-size: 1.8rem;
            font-weight: 800;
        }

        .card-header p {
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
        }

        .card-body {
            padding: 40px 30px;
        }

        .email-display {
            background: var(--psu-blue-soft);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 500;
            color: var(--psu-blue-deep);
            word-break: break-all;
        }

        .email-display i {
            color: var(--psu-blue);
            margin-right: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--psu-blue-deep);
        }

        .form-group label i {
            margin-right: 8px;
            color: var(--psu-blue);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--psu-blue);
            box-shadow: 0 0 0 3px rgba(0, 48, 135, 0.12);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .btn-reset {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--psu-gold) 0%, #ffd467 100%);
            color: var(--psu-blue-deep);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(184, 117, 0, 0.24);
        }

        .info-text {
            background: var(--psu-blue-soft);
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.85rem;
            color: var(--psu-blue);
            text-align: center;
        }

        .info-text i {
            margin-right: 8px;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="card-header">
            <img src="{{ asset('images/logo.png') }}" alt="Pangasinan State University Logo" class="brand-logo">
            <h2>Create New Password</h2>
            <p>Please enter your new password below</p>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="email-display">
                <i class="fas fa-envelope"></i>
                <span>{{ $email }}</span>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        New Password
                    </label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" required autocomplete="new-password">
                    <small class="text-muted">Minimum 8 characters</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">
                        <i class="fas fa-check-circle"></i>
                        Confirm Password
                    </label>
                    <input type="password" class="form-control" 
                           id="password_confirmation" name="password_confirmation" 
                           required autocomplete="new-password">
                </div>

                <button type="submit" class="btn-reset">
                    <i class="fas fa-sync-alt"></i> Reset Password
                </button>

                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    After resetting your password, you'll be redirected to the login page.
                </div>
            </form>
        </div>
    </div>
</body>
</html>
