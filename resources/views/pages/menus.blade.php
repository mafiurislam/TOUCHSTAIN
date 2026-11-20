@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="menu">All Menus &amp; Quick Navigation</span>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;margin-bottom:28px">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
            <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:52px;object-fit:contain" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
            <div>
                <h1 style="font-size:22px;font-weight:800;margin-bottom:4px">TOUCHSTAINS Complete Menu Hub</h1>
                <p style="font-size:13px;color:var(--text-secondary)">Quick access to every section, feature, and policy of TOUCHSTAINS (Mumbai Head Office - PAN India).</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px">
            <a href="{{ url('/') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/home.png') }}" alt=""></div><div class="feature-text"><h4>Home Page</h4><p>Banners &amp; Deals</p></div></a>
            <a href="{{ url('/categories') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/category.png') }}" alt=""></div><div class="feature-text"><h4>All Categories</h4><p>7 Parents • 18 Subs</p></div></a>
            <a href="{{ url('/shop') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/search.png') }}" alt=""></div><div class="feature-text"><h4>Shop Catalog</h4><p>All 240 Products</p></div></a>
            <a href="{{ url('/cart') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/cart.png') }}" alt=""></div><div class="feature-text"><h4>Shopping Bag</h4><p>View Cart Items</p></div></a>
            <a href="{{ url('/checkout') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/cash_on_delivery.png') }}" alt=""></div><div class="feature-text"><h4>Checkout</h4><p>Razorpay &amp; COD</p></div></a>
            <a href="{{ url('/orders') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/my_order.png') }}" alt=""></div><div class="feature-text"><h4>My Orders</h4><p>Order History</p></div></a>
            <a href="{{ url('/track-order') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/track_order.png') }}" alt=""></div><div class="feature-text"><h4>Track Order</h4><p>Live Status</p></div></a>
            <a href="{{ url('/wallet') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/wallet.png') }}" alt=""></div><div class="feature-text"><h4>Wallet &amp; Loyalty</h4><p>₹{{ number_format($currentUser->wallet_balance, 0) }} • {{ $currentUser->loyalty_point }} Pts</p></div></a>
            <a href="{{ url('/wishlist') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/heart_outline.png') }}" alt=""></div><div class="feature-text"><h4>Favourite List</h4><p>Saved Products</p></div></a>
            <a href="{{ url('/coupons') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/coupon.png') }}" alt=""></div><div class="feature-text"><h4>Coupons</h4><p>Promo Codes</p></div></a>
            <a href="{{ url('/address') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/location.png') }}" alt=""></div><div class="feature-text"><h4>Addresses</h4><p>Delivery Locations</p></div></a>
            <a href="{{ url('/chat') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/chat.png') }}" alt=""></div><div class="feature-text"><h4>Live Chat</h4><p>Customer Care</p></div></a>
            <a href="{{ url('/about') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/about_us.png') }}" alt=""></div><div class="feature-text"><h4>About Us</h4><p>Our Story</p></div></a>
            <a href="{{ url('/contact') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/customer_support.png') }}" alt=""></div><div class="feature-text"><h4>Contact Us</h4><p>Map &amp; Helpline</p></div></a>
            <a href="{{ url('/faq') }}" class="feature-item"><div class="feature-icon"><img src="{{ asset('assets/image/faq.png') }}" alt=""></div><div class="feature-text"><h4>FAQ</h4><p>Help Center</p></div></a>
            <a href="{{ url('/admin') }}" class="feature-item" style="border-color:var(--primary);background:var(--primary-lighter)"><div class="feature-icon"><img src="{{ asset('assets/image/settings.png') }}" alt=""></div><div class="feature-text"><h4>Admin Panel</h4><p>Manage SQLite DB</p></div></a>
        </div>
    </div>
</main>
@endsection

