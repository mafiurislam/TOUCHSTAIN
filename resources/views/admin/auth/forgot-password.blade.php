<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password — TOUCHSTAINS</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #F8FAFC;
        }
        .card {
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            width: 100%;
            max-width: 440px;
            padding: 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .form-control {
            width: 100%;
            background: rgba(15, 23, 42, 0.65);
            border: 1.5px solid #334155;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            color: #fff;
            margin: 8px 0 20px;
        }
        .form-control:focus { outline: none; border-color: #3B82F6; }
        .btn-submit {
            width: 100%;
            background: #2563EB;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="card">
        <div style="text-align:center;margin-bottom:20px">
            <div style="display:inline-flex;background:#fff;padding:8px 16px;border-radius:12px;margin-bottom:14px">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:48px;object-fit:contain">
            </div>
            <h1 style="font-size:20px;font-weight:800">Reset Admin Password</h1>
            <p style="font-size:12.5px;color:#94A3B8;margin-top:4px">Enter your admin email or username to restore access.</p>
        </div>

        @if(session('error'))
            <div style="background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.5);color:#FCA5A5;padding:12px;border-radius:8px;font-size:13px;margin-bottom:16px">
                ⚠️ {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div style="background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.5);color:#6EE7B7;padding:12px;border-radius:8px;font-size:13px;margin-bottom:16px">
                ✅ {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.send-reset') }}" method="POST">
            @csrf
            <label style="font-size:12.5px;font-weight:600">Admin Email or Username</label>
            <input type="text" name="email" class="form-control" placeholder="admin12345 or admin@12345" required autofocus>

            <button type="submit" class="btn-submit">Restore Password</button>
        </form>

        <div style="text-align:center;margin-top:20px;font-size:12.5px">
            <a href="{{ route('admin.login') }}" style="color:#60A5FA;text-decoration:none">← Back to Login</a>
        </div>
    </div>
</body>
</html>
