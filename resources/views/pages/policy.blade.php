@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span>{{ $policyHeading ?? 'Policy' }}</span>
    </div>

    <div class="policy-card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;padding-bottom:20px;border-bottom:1px solid var(--border);margin-bottom:24px">
            <div>
                <h1>{{ $policyHeading ?? 'TOUCHSTAINS Policy' }}</h1>
                <p style="margin:0;color:var(--text-secondary)">{{ $policySubheading ?? 'TOUCHSTAINS — Professional Fabric Stain & Rust Remover (ISO 9001:2015)' }}</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ url('/about') }}" class="sub-cat-chip {{ ($pageId ?? '') === 'about' ? 'active' : '' }}">About Us</a>
                <a href="{{ url('/terms') }}" class="sub-cat-chip {{ ($pageId ?? '') === 'terms' ? 'active' : '' }}">Terms</a>
                <a href="{{ url('/privacy-policy') }}" class="sub-cat-chip {{ ($pageId ?? '') === 'privacy' ? 'active' : '' }}">Privacy</a>
                <a href="{{ url('/return-policy') }}" class="sub-cat-chip {{ ($pageId ?? '') === 'return-policy' ? 'active' : '' }}">Return</a>
                <a href="{{ url('/refund-policy') }}" class="sub-cat-chip {{ ($pageId ?? '') === 'refund-policy' ? 'active' : '' }}">Refund</a>
                <a href="{{ url('/cancellation-policy') }}" class="sub-cat-chip {{ ($pageId ?? '') === 'cancellation-policy' ? 'active' : '' }}">Cancellation</a>
            </div>
        </div>

        <div class="policy-html-body" style="line-height:1.85;font-size:14px;color:var(--text-secondary)">
            {!! $policyContent ?: '<p>Welcome to <strong>TOUCHSTAINS</strong>, certified ISO 9001:2015. Head Office: Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022. For any assistance, call +91 7679416294 / 7039214465 or email touchstains@gmail.com.</p>' !!}
        </div>
    </div>
</main>
@endsection

