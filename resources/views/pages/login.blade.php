@extends('layouts.app')

@section('content')
<style>
    .ts-login-wrap {
        max-width: 470px;
        margin: 34px auto 60px;
        padding: 0 16px;
        box-sizing: border-box;
    }
    .ts-login-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
    }
    .ts-login-top {
        background: #ffffff;
        padding: 26px 24px 18px;
        text-align: center;
        color: #0F172A;
        border-bottom: 1px solid #F1F5F9;
    }
    .ts-login-body {
        padding: 28px 28px 30px;
    }
    .ts-login-field {
        margin-bottom: 16px;
    }
    .ts-login-field label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 6px;
    }
    .ts-login-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .ts-login-input-wrap span {
        position: absolute;
        left: 14px;
        font-size: 16px;
        pointer-events: none;
    }
    .ts-login-field input {
        width: 100%;
        height: 48px;
        padding: 0 14px 0 42px;
        border: 1.5px solid #CBD5E1;
        border-radius: 12px;
        font-size: 14.5px;
        color: #0F172A;
        background: #ffffff;
        box-sizing: border-box;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }
    .ts-login-field input:focus {
        outline: none;
        border-color: #01684B;
        box-shadow: 0 0 0 3.5px rgba(1, 104, 75, 0.12);
    }
    .ts-login-btn {
        width: 100%;
        height: 50px;
        border: none;
        border-radius: 13px;
        background: linear-gradient(135deg, #01684B 0%, #059669 100%);
        color: #ffffff;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(1, 104, 75, 0.28);
        transition: transform 0.14s ease, box-shadow 0.14s ease;
    }
    .ts-login-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(1, 104, 75, 0.35);
    }
    @media (max-width: 520px) {
        .ts-login-wrap {
            margin: 16px auto 40px;
            padding: 0 12px;
        }
        .ts-login-body {
            padding: 22px 18px 26px;
        }
        .ts-login-field input {
            font-size: 16px;
        }
    }
</style>

<main class="ts-login-wrap">
    <div class="ts-login-card">
        <div class="ts-login-top">
            <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:54px;margin:0 auto 12px;object-fit:contain;display:block" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
            <h1 style="font-size:22px;font-weight:800;margin:0 0 6px;color:#0F172A" data-i18n="login">Customer Login</h1>
            <p style="font-size:13px;color:#64748B;margin:0">Sign in quickly with your registered Mobile Number — no password needed!</p>
        </div>

        <div class="ts-login-body">
            <form action="{{ url('/login') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ request('redirect', 'checkout') }}">

                <div class="ts-login-field">
                    <label>Registered Mobile Number <span style="color:#DC2626">*</span></label>
                    <div class="ts-login-input-wrap">
                        <span>📱</span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="e.g. +91 9876543210" required>
                    </div>
                </div>

                <div class="ts-login-field">
                    <label>Email Address <span style="color:#64748B;font-weight:500">(Optional)</span></label>
                    <div class="ts-login-input-wrap">
                        <span>✉️</span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com (Optional)">
                    </div>
                </div>

                <button type="submit" class="ts-login-btn" data-i18n="login">
                    🔑 Login to My Account →
                </button>
            </form>

            <div style="text-align:center;margin-top:22px;padding-top:18px;border-top:1px solid #F1F5F9;font-size:13.5px;color:#475569">
                New to TOUCHSTAINS?
                <a href="{{ url('/register' . (request('redirect') ? '?redirect=' . request('redirect') : '?redirect=checkout')) }}" style="color:#01684B;font-weight:800;text-decoration:underline" data-i18n="sign_up">Create New Account →</a>
            </div>
        </div>
    </div>
</main>
@endsection
