@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="wallet">My Wallet &amp; Loyalty Points</span>
    </div>

    <div class="account-layout">
        @include('partials.account-sidebar', ['activeTab' => 'wallet'])

        <div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-bottom:24px">
                <!-- Wallet Balance Card -->
                <div class="wallet-hero-card">
                    <span class="label" data-i18n="wallet_amount">Available Wallet Balance</span>
                    <div class="balance" id="wallet-balance-val">₹{{ number_format($currentUser->wallet_balance, 2) }}</div>
                    <p style="font-size:12px;opacity:0.85;margin-bottom:16px">Use at checkout for instant 1-click grocery payment</p>
                    <form action="{{ url('/wallet/add-fund') }}" method="POST" style="display:flex;gap:8px;position:relative;z-index:2">
                        @csrf
                        <input type="number" name="amount" value="100" min="10" max="10000" style="width:110px;padding:8px 12px;border-radius:8px;border:none;font-weight:700;color:#111827">
                        <button type="submit" class="btn-primary" style="background:#fff;color:var(--primary);padding:8px 16px;font-size:13px">+ Add Fund</button>
                    </form>
                    <img src="{{ asset('assets/image/wallet_banner.png') }}" alt="Wallet" class="bg-art" onerror="this.style.display='none'">
                </div>

                <!-- Loyalty Points Card -->
                <div class="wallet-hero-card loyalty">
                    <span class="label" data-i18n="loyalty_point">Getgozo Loyalty Points</span>
                    <div class="balance" id="loyalty-points-val">{{ $currentUser->loyalty_point }} Pts</div>
                    <p style="font-size:12px;opacity:0.9;margin-bottom:16px">Exchange Rate: 1 Point = ₹1.00 Wallet Cash (Earn 5% on every order!)</p>
                    <form action="{{ url('/wallet/convert-points') }}" method="POST" style="display:flex;gap:8px;position:relative;z-index:2">
                        @csrf
                        <input type="number" name="points" value="{{ max(10, $currentUser->loyalty_point) }}" min="1" max="{{ max(1, $currentUser->loyalty_point) }}" style="width:110px;padding:8px 12px;border-radius:8px;border:none;font-weight:700;color:#111827">
                        <button type="submit" class="btn-primary" style="background:#111827;color:#fff;padding:8px 16px;font-size:13px" {{ $currentUser->loyalty_point <= 0 ? 'disabled' : '' }}>
                            ⇄ Convert to Wallet
                        </button>
                    </form>
                    <img src="{{ asset('assets/image/loyalty_point.png') }}" alt="Points" class="bg-art">
                </div>
            </div>

            <!-- Real Loyalty Promo Banner -->
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
                <div>
                    <h3 style="font-size:16px;font-weight:700;margin-bottom:6px">How Getgozo Loyalty Rewards Work</h3>
                    <ul style="font-size:13px;color:var(--text-secondary);line-height:1.8">
                        <li>✓ Place any grocery order above ₹{{ $settings['minimum_order_value'] ?? 69 }} and automatically earn <strong>5% Loyalty Points</strong>.</li>
                        <li>✓ Convert your accumulated points anytime into <strong>Getgozo Wallet Balance</strong> (1 Point = ₹1).</li>
                        <li>✓ Pay for your next order using Getgozo Wallet at checkout with zero extra fees!</li>
                    </ul>
                </div>
                <img src="{{ asset('storage/banner/touchstains-hero-banner-1.jpg') }}" alt="TOUCHSTAINS Rewards" style="height:100px;border-radius:10px">
            </div>
        </div>
    </div>
</main>
@endsection

