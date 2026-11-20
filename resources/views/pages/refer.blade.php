@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="refer_and_earn">Refer &amp; Earn</span>
    </div>

    <div class="account-layout">
        @include('partials.account-sidebar', ['activeTab' => 'refer'])

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:36px 28px;text-align:center">
            <img src="{{ asset('assets/image/refer_banner.svg') }}" alt="Refer & Earn" style="max-width:260px;margin:0 auto 20px">
            <h1 style="font-size:24px;font-weight:800;margin-bottom:8px" data-i18n="refer_and_earn">Invite Friends &amp; Earn ₹50 Wallet Cash!</h1>
            <p style="font-size:14px;color:var(--text-secondary);max-width:520px;margin:0 auto 24px">
                Share your unique TOUCHSTAINS referral code with friends, family, and colleagues. When they place their first order, you both receive bonus rewards!
            </p>

            <div style="display:inline-flex;align-items:center;gap:12px;background:var(--primary-lighter);border:2px dashed var(--primary);padding:12px 24px;border-radius:var(--radius-md);margin-bottom:24px">
                <span style="font-size:20px;font-weight:800;letter-spacing:2px;color:var(--primary)">{{ $currentUser->referral_code ?? 'TOUCHSTAINS' }}</span>
                <button type="button" class="btn-primary" style="padding:8px 16px;font-size:12px" onclick="navigator.clipboard?.writeText('{{ $currentUser->referral_code ?? 'TOUCHSTAINS' }}'); GetgozoApp.showToast('Referral code copied!', 'success')">
                    Copy Code
                </button>
            </div>

            <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap">
                <a href="https://wa.me/?text=Shop%20premium%20fabric%20stain%20remover%20on%20TOUCHSTAINS!%20Use%20my%20referral%20code%20{{ $currentUser->referral_code ?? 'TOUCHSTAINS' }}%20at%20touchstains.com" target="_blank" rel="noopener" class="btn-primary" style="background:#25D366">
                    Share via WhatsApp
                </a>
            </div>
        </div>
    </div>
</main>
@endsection

