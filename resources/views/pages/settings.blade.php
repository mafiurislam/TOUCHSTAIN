@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="settings">Settings &amp; Preferences</span>
    </div>

    <div class="account-layout">
        @include('partials.account-sidebar', ['activeTab' => 'settings'])

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px">
            <h1 style="font-size:20px;font-weight:700;margin-bottom:20px" data-i18n="settings">Language, Theme &amp; Branch Settings</h1>

            <div style="display:grid;gap:20px">
                <div style="padding:18px;border:1px solid var(--border);border-radius:var(--radius-md);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                    <div>
                        <h4 style="font-size:15px;font-weight:700">App Display Language</h4>
                        <p style="font-size:12px;color:var(--text-secondary)">Standard display language is set to English</p>
                    </div>
                    <span style="padding:6px 14px;background:var(--primary-light);color:var(--primary);border-radius:99px;font-size:12px;font-weight:700">🇬🇧 English (Default)</span>
                </div>

                <div style="padding:18px;border:1px solid var(--border);border-radius:var(--radius-md);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                    <div>
                        <h4 style="font-size:15px;font-weight:700" data-i18n="dark_theme">Appearance / Theme Mode</h4>
                        <p style="font-size:12px;color:var(--text-secondary)">Toggle between Light Mode and Dark Mode</p>
                    </div>
                    <button type="button" class="btn-primary" onclick="GetgozoApp.toggleTheme()">
                        🌙 Toggle Dark / Light Mode
                    </button>
                </div>

                <div style="padding:18px;border:1px solid var(--border);border-radius:var(--radius-md);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                    <div>
                        <h4 style="font-size:15px;font-weight:700">Active Delivery Branch</h4>
                        <p style="font-size:12px;color:var(--text-secondary)">TOUCHSTAINS Mumbai Head Office — Sion East (400022)</p>
                    </div>
                    <span style="padding:6px 14px;background:var(--primary-light);color:var(--primary);border-radius:99px;font-size:12px;font-weight:700">✓ Active Branch</span>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

