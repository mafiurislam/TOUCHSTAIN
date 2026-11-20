@extends('layouts.app')

@section('content')
<main class="container" style="max-width:460px;padding:42px 16px">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;box-shadow:var(--shadow-md);text-align:center">
        <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:58px;margin:0 auto 14px;object-fit:contain" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
        <h1 style="font-size:22px;font-weight:800;margin-bottom:8px" data-i18n="forgot_password">Reset Your Password</h1>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:24px">Enter your registered mobile number or email and we will send an OTP to reset your password.</p>

        <form onsubmit="event.preventDefault(); GetgozoApp.showToast('Password reset OTP sent to your number!', 'success')">
            <div class="form-group" style="text-align:left">
                <label>Registered Mobile Number / Email</label>
                <input type="text" class="form-control" placeholder="+91 9876543210 or email" required>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px">Send Reset OTP</button>
        </form>

        <div style="margin-top:18px">
            <a href="{{ url('/login') }}" style="font-size:13px;color:var(--primary);font-weight:600">← Back to Login</a>
        </div>
    </div>
</main>
@endsection

