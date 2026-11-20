@extends('layouts.app')

@section('content')
<main class="container" style="max-width:900px;padding:36px 16px">
    <div style="text-align:center;margin-bottom:32px">
        <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:72px;margin:0 auto 14px;object-fit:contain" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
        <div><span style="padding:4px 14px;background:var(--primary-light);color:var(--primary);border-radius:99px;font-size:12px;font-weight:700">WELCOME TO TOUCHSTAINS MUMBAI</span></div>
        <h1 style="font-size:28px;font-weight:800;margin-top:10px">Professional Fabric Stain &amp; Rust Remover at Your Doorstep</h1>
        <p style="color:var(--text-secondary)">Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:32px">
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;text-align:center">
            <img src="{{ asset('assets/image/on_boarding_1.png') }}" alt="Select Groceries" style="height:160px;margin:0 auto 16px;object-fit:contain">
            <h3 style="font-size:17px;font-weight:700;margin-bottom:8px">1. Choose From 240+ Items</h3>
            <p style="font-size:13px;color:var(--text-secondary)">Browse Sobji O Phol, Chal, Dal, Dim, Machh, Mangsho, Spices, Snacks &amp; Home Care.</p>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;text-align:center">
            <img src="{{ asset('assets/image/on_boarding_2.png') }}" alt="Safe Payment" style="height:160px;margin:0 auto 16px;object-fit:contain">
            <h3 style="font-size:17px;font-weight:700;margin-bottom:8px">2. Easy Checkout &amp; Rewards</h3>
            <p style="font-size:13px;color:var(--text-secondary)">Pay securely via Razorpay Online Payment or Cash on Delivery with 100% purchase safety.</p>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;text-align:center">
            <img src="{{ asset('assets/image/on_boarding_3.png') }}" alt="Fast Delivery" style="height:160px;margin:0 auto 16px;object-fit:contain">
            <h3 style="font-size:17px;font-weight:700;margin-bottom:8px">3. Express Doorstep Delivery</h3>
            <p style="font-size:13px;color:var(--text-secondary)">Free delivery on all orders above ₹299 dispatched from Mumbai Hub!</p>
        </div>
    </div>

    <div style="text-align:center">
        <a href="{{ url('/') }}" class="btn-primary" style="padding:14px 36px;font-size:16px">Start Shopping Now →</a>
    </div>
</main>
@endsection

