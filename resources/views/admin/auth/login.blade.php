<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Portal — TOUCHSTAINS Secure Login</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #0F172A 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #F8FAFC;
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle ambient glowing orbs */
        .ambient-glow-1 {
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, transparent 70%);
            top: -120px;
            left: -120px;
            pointer-events: none;
        }
        .ambient-glow-2 {
            position: absolute;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, transparent 70%);
            bottom: -150px;
            right: -150px;
            pointer-events: none;
        }

        .login-card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            padding: 40px 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            position: relative;
            z-index: 10;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand-logo-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            padding: 10px 18px;
            border-radius: 12px;
            margin-bottom: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .brand-logo-container img {
            height: 42px;
            object-fit: contain;
        }

        .login-title {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
        }
        .login-subtitle {
            font-size: 13px;
            color: #94A3B8;
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #CBD5E1;
            margin-bottom: 6px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            color: #64748B;
            font-size: 16px;
            pointer-events: none;
        }
        .form-control {
            width: 100%;
            background: rgba(15, 23, 42, 0.65);
            border: 1.5px solid #334155;
            border-radius: 10px;
            padding: 12px 14px 12px 42px;
            font-size: 14px;
            color: #fff;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #3B82F6;
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
        .toggle-pwd {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
        }
        .toggle-pwd:hover {
            color: #fff;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            margin-bottom: 24px;
        }
        .form-options label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94A3B8;
            cursor: pointer;
        }
        .form-options a {
            color: #60A5FA;
            text-decoration: none;
            font-weight: 600;
        }
        .form-options a:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
        }

        .alert-box {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .alert-error {
            background: rgba(220, 38, 38, 0.2);
            border: 1px solid rgba(220, 38, 38, 0.5);
            color: #FCA5A5;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.5);
            color: #6EE7B7;
        }

        .credentials-pill {
            margin-top: 24px;
            padding: 14px;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px dashed rgba(255, 255, 255, 0.15);
            font-size: 12px;
            color: #94A3B8;
        }
        .credentials-pill strong {
            color: #E2E8F0;
        }
        .credentials-pill code {
            background: rgba(255, 255, 255, 0.1);
            color: #60A5FA;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="login-card">
        
        <div class="login-brand">
            <div class="brand-logo-container">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:56px;object-fit:contain">
            </div>
            <h1 class="login-title">Super Admin Portal</h1>
            <p class="login-subtitle">TOUCHSTAINS Enterprise E-commerce Management</p>
        </div>

        @if(session('error'))
            <div class="alert-box alert-error">
                ⚠️ {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert-box alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.authenticate') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="username">Username or Admin Email</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" name="username" id="username" class="form-control" value="{{ old('username', 'admin12345') }}" placeholder="admin12345 or admin@12345" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••••••" value="admin1234" required>
                    <button type="button" class="toggle-pwd" onclick="togglePasswordVisibility()" title="Show/Hide Password">👁️</button>
                </div>
            </div>

            <div class="form-options">
                <label>
                    <input type="checkbox" name="remember" value="1" checked style="accent-color:#2563EB">
                    Remember session
                </label>
                <a href="{{ route('admin.forgot-password') }}">Forgot password?</a>
            </div>

            <button type="submit" class="btn-submit">
                <span>Sign In to Super Admin</span>
                <span>→</span>
            </button>
        </form>

        <div class="credentials-pill">
            <div style="font-weight:700;color:#F1F5F9;margin-bottom:4px">🔑 Initial Credentials:</div>
            <div>Username: <code>admin12345</code> (or <code>admin@12345</code>)</div>
            <div style="margin-top:2px">Password: <code>admin1234</code> (or <code>admin123</code>)</div>
            <div style="font-size:11px;margin-top:6px;opacity:0.8">Protected by bcrypt hashing & Rate-Limiter (5 attempts max).</div>
        </div>

    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            if (pwd.type === 'password') {
                pwd.type = 'text';
            } else {
                pwd.type = 'password';
            }
        }
    </script>
</body>
</html>
