@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="faq">Frequently Asked Questions (FAQ)</span>
    </div>

    <div class="policy-card">
        <h1>Frequently Asked Questions — TOUCHSTAINS</h1>
        <p>Everything you need to know about TOUCHSTAINS Fabric Stain &amp; Rust Remover products, ordering, Razorpay online payment, and express delivery.</p>

        <div style="display:flex;flex-direction:column;gap:14px;margin-top:24px">
            <div style="padding:18px;border:1px solid var(--border);border-radius:var(--radius-md)">
                <h3 style="font-size:16px;margin:0 0 6px;color:var(--primary)">1. What is the minimum order value and delivery fee?</h3>
                <p style="margin:0">The minimum order amount is just <strong>₹{{ $settings['minimum_order_value'] ?? 69 }}</strong>. Moreover, TOUCHSTAINS offers <strong>FREE Express Home Delivery on all orders over ₹{{ $settings['free_delivery_over_amount'] ?? 299 }}</strong> across India!</p>
            </div>
            <div style="padding:18px;border:1px solid var(--border);border-radius:var(--radius-md)">
                <h3 style="font-size:16px;margin:0 0 6px;color:var(--primary)">2. Where is the TOUCHSTAINS Head Office located?</h3>
                <p style="margin:0">Our main head office is located at <strong>Shop No. 3 &amp; 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022</strong>. Fast express shipping is available across all Indian states and pin codes.</p>
            </div>
            <div style="padding:18px;border:1px solid var(--border);border-radius:var(--radius-md)">
                <h3 style="font-size:16px;margin:0 0 6px;color:var(--primary)">3. How do TOUCHSTAINS Loyalty Points work?</h3>
                <p style="margin:0">You automatically earn <strong>5% Loyalty Points</strong> on every completed order. In your <a href="{{ url('/wallet') }}" style="color:var(--primary);font-weight:700">Wallet &amp; Loyalty</a> section, you can convert your points into Wallet Cash at a <strong>1 Point = ₹1</strong> rate!</p>
            </div>
            <div style="padding:18px;border:1px solid var(--border);border-radius:var(--radius-md)">
                <h3 style="font-size:16px;margin:0 0 6px;color:var(--primary)">4. What payment methods are accepted?</h3>
                <p style="margin:0">We accept <strong>Razorpay Online Payment (UPI, Cards, NetBanking, Wallets)</strong> and <strong>Cash on Delivery (COD)</strong>.</p>
            </div>
            <div style="padding:18px;border:1px solid var(--border);border-radius:var(--radius-md)">
                <h3 style="font-size:16px;margin:0 0 6px;color:var(--primary)">5. How can I contact Customer Care for order or product updates?</h3>
                <p style="margin:0">You can call or WhatsApp us at <strong><a href="tel:+917679416294" style="color:var(--primary)">+91 7679416294</a> / <a href="tel:+917039214465" style="color:var(--primary)">7039214465</a></strong>, email <strong><a href="mailto:touchstains@gmail.com" style="color:var(--primary)">touchstains@gmail.com</a></strong>, or use our <a href="{{ url('/chat') }}" style="color:var(--primary);font-weight:700">Live Support Chat</a>.</p>
            </div>
        </div>
    </div>
</main>
@endsection

