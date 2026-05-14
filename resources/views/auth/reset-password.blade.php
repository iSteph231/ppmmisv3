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
        :root {
            --sidebar-width: 260px;
            --primary-color: #0d6efd;
            --sidebar-bg: #1a1e2c;
            --content-bg: #f0f2f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--content-bg);
            display: flex;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            color: white;
            transition: all 0.3s;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .sidebar-header p {
            font-size: 0.8rem;
            opacity: 0.7;
            margin: 5px 0 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 12px 20px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .sidebar-menu li:hover,
        .sidebar-menu li.active {
            background: rgba(255,255,255,0.1);
            border-left: 3px solid var(--primary-color);
        }

        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-menu li a i {
            width: 20px;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
            padding: 20px;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            border-radius: 12px;
            padding: 15px 25px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: #1a1e2c;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        /* Reset Password Card */
        .reset-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            max-width: 550px;
            margin: 0 auto;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), #0b5ed7);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .card-header h4 {
            margin: 0 0 8px 0;
            font-size: 1.5rem;
        }

        .card-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group label i {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
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
            padding: 12px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-reset:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13,110,253,0.3);
        }

        .info-text {
            background: #e7f3ff;
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #004085;
            text-align: center;
        }

        .info-text i {
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>PPMMIS</h3>
            <p>Property & Procurement Management</p>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="active">
                <a href="#">
                    <i class="fas fa-key"></i>
                    <span>Reset Password</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tasks"></i>
                    <span>Work Requests</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h2 class="page-title">Reset Password</h2>
            <div class="user-info">
                <span>jane@psu.edu.ph</span>
                <div class="user-avatar">J</div>
            </div>
        </div>

        <!-- Reset Password Form -->
        <div class="reset-card">
            <div class="card-header">
                <h4>Create New Password</h4>
                <p>Please enter your new password below</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token ?? '' }}">
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ $email ?? old('email') }}" 
                               required readonly>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>