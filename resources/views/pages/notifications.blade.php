@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="notification">Notifications</span>
    </div>

    <div class="account-layout">
        @include('partials.account-sidebar', ['activeTab' => 'notifications'])

        <div>
            <h1 style="font-size:20px;font-weight:700;margin-bottom:18px" data-i18n="notification">Notifications &amp; Offers</h1>
            <div style="display:flex;flex-direction:column;gap:12px">
                <div style="background:var(--bg-card);border:1px solid var(--border);border-left:4px solid var(--primary);border-radius:var(--radius-md);padding:16px 20px;display:flex;gap:14px;align-items:flex-start">
                    <img src="{{ asset('assets/image/notification.png') }}" alt="" style="width:36px;height:36px">
                    <div>
                        <h4 style="font-size:15px;font-weight:700">🎁 Loyalty Point Offer is Live at TOUCHSTAINS!</h4>
                        <p style="font-size:13px;color:var(--text-secondary);margin-top:4px">Earn 5% Loyalty Points on every order at Jadupr branch and convert them directly into Wallet Cash.</p>
                        <span style="font-size:11px;color:var(--text-muted)">Today • TOUCHSTAINS Official</span>
                    </div>
                </div>
                <div style="background:var(--bg-card);border:1px solid var(--border);border-left:4px solid #F59E0B;border-radius:var(--radius-md);padding:16px 20px;display:flex;gap:14px;align-items:flex-start">
                    <img src="{{ asset('assets/image/fast_delivery.png') }}" alt="" style="width:36px;height:36px">
                    <div>
                        <h4 style="font-size:15px;font-weight:700">🚚 Free Delivery on All Orders Over ₹69!</h4>
                        <p style="font-size:13px;color:var(--text-secondary);margin-top:4px">Enjoy free delivery across India on all orders above ₹299.</p>
                        <span style="font-size:11px;color:var(--text-muted)">Yesterday • Delivery Update</span>
                    </div>
                </div>
                <div style="background:var(--bg-card);border:1px solid var(--border);border-left:4px solid #10B981;border-radius:var(--radius-md);padding:16px 20px;display:flex;gap:14px;align-items:flex-start">
                    <img src="{{ asset('assets/image/coupon.png') }}" alt="" style="width:36px;height:36px">
                    <div>
                        <h4 style="font-size:15px;font-weight:700">🏷️ Use Code GOZO20 for 20% OFF</h4>
                        <p style="font-size:13px;color:var(--text-secondary);margin-top:4px">Apply promo code GOZO20 at checkout on orders above ₹199.</p>
                        <span style="font-size:11px;color:var(--text-muted)">2 days ago • Promo Alert</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

