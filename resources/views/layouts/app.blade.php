<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'TOUCHSTAINS — Fabric Stain & Rust Remover' }}</title>
    <meta name="description" content="TOUCHSTAINS — Professional Fabric Stain & Rust Remover for all clothes. Certified ISO 9001:2015. Fast Delivery across India.">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=2.3">
    <link rel="apple-touch-icon" href="{{ asset('icons/Icon-192.png') }}?v=2.3">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/laravel-app.css') }}?v={{ time() }}">
</head>
<body data-page="{{ $pageId ?? 'home' }}">

    <!-- EXACT GROFRESH MAIN HEADER FROM SCREENSHOT -->
    <header class="gf-header" id="siteHeader">
        <div class="gf-container gf-header-inner">
            <!-- Left: Logo + Home + Categories v -->
            <div class="gf-header-left">
                <a href="{{ url('/') }}" class="gf-logo" title="TOUCHSTAINS Fabric Stain Remover">
                    <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
                </a>

                <nav class="gf-nav">
                    <a href="{{ url('/') }}" class="gf-nav-link {{ ($pageId ?? '') === 'home' ? 'active' : '' }}" data-i18n="home">Home</a>

                    <div class="gf-cat-menu-wrap" id="gf-cat-menu-wrap">
                        <button type="button" class="gf-nav-link gf-cat-trigger {{ ($pageId ?? '') === 'categories' ? 'active' : '' }}" onclick="document.getElementById('gf-cat-menu-wrap').classList.toggle('open')">
                            <span data-i18n="categories">Categories</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9l6 6 6-6"/></svg>
                        </button>

                        <!-- Mega Dropdown Menu -->
                        <div class="gf-cat-dropdown" id="header-cat-mega-menu">
                            @foreach($parentCategories as $cat)
                                <div class="gf-cat-item">
                                    <a href="{{ url('/categories?cat=' . $cat->id) }}" class="gf-cat-row">
                                        <img src="{{ asset('storage/category/' . $cat->image) }}" alt="{{ $cat->name }}" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                                        <span>{{ $cat->name }}</span>
                                        @if($cat->children && $cat->children->count() > 0)
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:auto"><path d="M9 18l6-6-6-6"/></svg>
                                        @endif
                                    </a>
                                    @if($cat->children && $cat->children->count() > 0)
                                        <div class="gf-subcat-flyout">
                                            <div class="gf-subcat-title">{{ $cat->name }}</div>
                                            @foreach($cat->children as $sub)
                                                <a href="{{ url('/categories?cat=' . $cat->id . '&sub=' . $sub->id) }}" class="gf-subcat-link">
                                                    <span>{{ $sub->name }}</span>
                                                    <span class="gf-subcat-count">{{ $sub->products_count ?? 0 }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Center: Search for Products -->
            <div class="gf-search-wrap header-search-wrap">
                <form class="gf-search-form" id="header-search-form" action="{{ url('/shop') }}" method="GET">
                    <input type="text" name="search" id="header-search-input" value="{{ request('search', '') }}" placeholder="Search for Products" data-i18n="search_items_here" autocomplete="off">
                    <button type="submit" class="gf-search-btn" aria-label="Search">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </form>
                <div class="search-suggestions" id="header-search-suggestions"></div>
            </div>

            <!-- Right: 3 Elevated Circular Action Buttons (Heart + Badge, Cart + Badge, Menu) -->
            <div class="gf-header-icons">
                <a href="{{ url('/wishlist') }}" class="gf-icon-btn" title="Favourite List">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <span class="gf-badge" data-wishlist-count>{{ count($wishlistIds ?? []) }}</span>
                </a>

                <button type="button" class="gf-icon-btn" onclick="GetgozoApp.openCartDrawer()" title="Shopping Bag">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="#01684B">
                        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                    <span class="gf-badge" data-cart-count>0</span>
                </button>

                <!-- Account / Login Icon with Dropdown (Registration & Login) -->
                <div class="ts-account-menu-wrap" id="tsAccountMenuWrap">
                    <button type="button"
                        class="gf-icon-btn ts-account-trigger {{ !empty($isCustomerRegistered) ? 'is-logged-in' : '' }}"
                        id="header-auth-pill"
                        onclick="window.TouchstainsAuth && window.TouchstainsAuth.toggleHeaderDropdown(event)"
                        title="{{ !empty($isCustomerRegistered) && isset($registeredUser) ? $registeredUser->name : 'Account: Login / Registration' }}"
                        aria-haspopup="true">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        @if(!empty($isCustomerRegistered))
                            <span class="ts-account-dot"></span>
                        @endif
                    </button>

                    <div class="ts-account-dropdown" id="tsAccountDropdown">
                        @if(!empty($isCustomerRegistered) && isset($registeredUser))
                            <div class="ts-dd-head">
                                <strong>{{ $registeredUser->name }}</strong>
                                <span>{{ $registeredUser->phone }}</span>
                            </div>
                            <a href="{{ url('/account') }}" class="ts-dd-item">
                                <span class="ts-dd-item-icon">👤</span>
                                <span class="ts-dd-item-text">
                                    <strong>My Profile</strong>
                                    <small>View &amp; edit details</small>
                                </span>
                            </a>
                            <a href="{{ url('/orders') }}" class="ts-dd-item">
                                <span class="ts-dd-item-icon">📦</span>
                                <span class="ts-dd-item-text">
                                    <strong>My Orders</strong>
                                    <small>Track &amp; manage orders</small>
                                </span>
                            </a>
                            <a href="{{ url('/address') }}" class="ts-dd-item">
                                <span class="ts-dd-item-icon">📍</span>
                                <span class="ts-dd-item-text">
                                    <strong>Saved Addresses</strong>
                                    <small>Delivery address book</small>
                                </span>
                            </a>
                            <a href="{{ url('/logout') }}" class="ts-dd-item ts-dd-item-danger">
                                <span class="ts-dd-item-icon">🚪</span>
                                <span class="ts-dd-item-text">
                                    <strong>Logout</strong>
                                    <small>Sign out of account</small>
                                </span>
                            </a>
                        @else
                            <div class="ts-dd-head">
                                <strong>Welcome to TOUCHSTAINS</strong>
                                <span>Access orders &amp; saved address</span>
                            </div>
                            <button type="button" class="ts-dd-item ts-dd-item-primary" onclick="window.TouchstainsAuth && window.TouchstainsAuth.open('register')">
                                <span class="ts-dd-item-icon">📝</span>
                                <span class="ts-dd-item-text">
                                    <strong>Registration</strong>
                                    <small>Create new customer account</small>
                                </span>
                            </button>
                            <button type="button" class="ts-dd-item" onclick="window.TouchstainsAuth && window.TouchstainsAuth.open('login')">
                                <span class="ts-dd-item-icon">🔑</span>
                                <span class="ts-dd-item-text">
                                    <strong>Login</strong>
                                    <small>Sign in with Mobile / Email</small>
                                </span>
                            </button>
                        @endif
                    </div>
                </div>

                <button type="button" class="gf-icon-btn gf-hamburger-btn" onclick="GetgozoApp.openSideMenu()" title="Menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.6" stroke-linecap="round">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="gf-container" style="margin-top:14px">
            <div style="background:#EDF4F2;border:1px solid #01684B;color:#01684B;padding:12px 18px;border-radius:10px;font-weight:600;display:flex;justify-content:space-between;align-items:center">
                <span>✅ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" style="font-size:18px;color:#01684B">✕</button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="gf-container" style="margin-top:14px">
            <div style="background:#FEE2E2;border:1px solid #EF4444;color:#B91C1C;padding:12px 18px;border-radius:10px;font-weight:600;display:flex;justify-content:space-between;align-items:center">
                <span>⚠️ {{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" style="font-size:18px;color:#B91C1C">✕</button>
            </div>
        </div>
    @endif

    <!-- PAGE CONTENT -->
    @yield('content')

    <!-- GROFRESH v7.7 FOOTER -->
    <footer class="gf-footer">
        <div class="gf-newsletter-strip">
            <div class="gf-container gf-newsletter-inner">
                <div class="gf-newsletter-text">
                    <h3 data-i18n="news_letter">Newsletter</h3>
                    <p data-i18n="subscribe_to_out_new_channel_to_get_latest_updates">Subscribe to our newsletter and unlock a world of exclusive offers!</p>
                </div>
                <form class="gf-newsletter-form" action="{{ url('/newsletter/subscribe') }}" method="POST" onsubmit="event.preventDefault(); GetgozoApp.showToast('Subscribed to Getgozo newsletter!', 'success'); this.reset();">
                    @csrf
                    <input type="email" name="email" data-i18n="your_email_address" placeholder="Your Email Address" required>
                    <button type="submit" data-i18n="subscribe">Subscribe</button>
                </form>
            </div>
        </div>

        <div class="gf-container">
            <div class="gf-footer-grid">
                <div class="gf-footer-brand">
                    <a href="{{ url('/') }}" title="TOUCHSTAINS">
                        <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" class="gf-footer-logo" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
                    </a>
                    <p>TOUCHSTAINS — Professional Fabric Stain &amp; Rust Remover for all types of clothes. Certified ISO 9001:2015. Instant results within seconds. Fast express delivery across India.</p>
                    <div class="gf-footer-contact">
                        <div>📍 {{ $settings['ecommerce_address'] ?? 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022' }}</div>
                        <div>📞 <a href="tel:+917679416294">+91 7679416294</a> / <a href="tel:+917039214465">7039214465</a></div>
                        <div>✉️ <a href="mailto:touchstains@gmail.com">touchstains@gmail.com</a></div>
                    </div>
                </div>

                <div class="gf-footer-col">
                    <h4 data-i18n="my_account">My Account</h4>
                    <ul>
                        <li><a href="{{ url('/account') }}" data-i18n="profile">Profile</a></li>
                        <li><a href="{{ url('/address') }}" data-i18n="address">Address</a></li>
                        <li><a href="{{ url('/orders') }}" data-i18n="my_order">My Orders</a></li>
                        <li><a href="{{ url('/track-order') }}" data-i18n="track_order">Order Track</a></li>
                    </ul>
                </div>

                <div class="gf-footer-col">
                    <h4 data-i18n="quick_links">Quick Links</h4>
                    <ul>
                        <li><a href="{{ url('/contact') }}" data-i18n="contact_us">Contact Us</a></li>
                        <li><a href="{{ url('/chat') }}" data-i18n="live_chat">Live Chat</a></li>
                        <li><a href="{{ url('/faq') }}" data-i18n="faq">FAQ</a></li>
                    </ul>
                </div>

                <div class="gf-footer-col">
                    <h4>Policies &amp; Info</h4>
                    <ul>
                        <li><a href="{{ url('/terms') }}" data-i18n="terms_and_condition">Terms &amp; Conditions</a></li>
                        <li><a href="{{ url('/privacy-policy') }}" data-i18n="privacy_policy">Privacy Policy</a></li>
                        <li><a href="{{ url('/return-policy') }}" data-i18n="return_policy">Return Policy</a></li>
                        <li><a href="{{ url('/refund-policy') }}" data-i18n="refund_policy">Refund Policy</a></li>
                        <li><a href="{{ url('/cancellation-policy') }}" data-i18n="cancellation_policy">Cancellation Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="gf-footer-bottom">
                <span>{{ $settings['footer_text'] ?? 'TOUCHSTAINS @ 2026 • All Rights Reserved | Developed by MS Solutions' }}</span>
                <div style="display:flex;gap:14px">
                    <a href="{{ url('/admin') }}" style="color:#01684B;font-weight:600">Admin Panel</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- MOBILE BOTTOM NAVIGATION BAR -->
    <nav class="mobile-bottom-nav">
        <a href="{{ url('/') }}" class="mob-nav-item {{ ($pageId ?? '') === 'home' ? 'active' : '' }}">
            <svg class="mob-nav-icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span data-i18n="home">Home</span>
        </a>
        <a href="{{ url('/categories') }}" class="mob-nav-item {{ ($pageId ?? '') === 'categories' ? 'active' : '' }}">
            <svg class="mob-nav-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
            <span data-i18n="categories">Categories</span>
        </a>
        <button type="button" class="mob-nav-item" onclick="GetgozoApp.openCartDrawer()">
            <svg class="mob-nav-icon" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span data-i18n="shopping_bag">Bag</span>
            <span class="badge-count" data-cart-count style="display:none">0</span>
        </button>
        <a href="{{ url('/orders') }}" class="mob-nav-item {{ ($pageId ?? '') === 'orders' ? 'active' : '' }}">
            <svg class="mob-nav-icon" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <span data-i18n="my_order">Orders</span>
        </a>
        <button type="button" class="mob-nav-item" onclick="GetgozoApp.openSideMenu()">
            <svg class="mob-nav-icon" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            <span data-i18n="menu">Menu</span>
        </button>
    </nav>

    <!-- SLIDE-OUT CART DRAWER -->
    <div class="drawer-backdrop" id="cart-drawer-backdrop" onclick="GetgozoApp.closeCartDrawer()"></div>
    <aside class="slide-drawer" id="cart-slide-drawer">
        <div class="drawer-header">
            <h3>
                <img src="{{ asset('assets/image/cart.png') }}" alt="Bag" style="width:22px;height:22px">
                <span data-i18n="shopping_bag">Shopping Bag</span>
                <span class="badge-count" data-cart-count style="position:static;display:none">0</span>
            </h3>
            <button class="drawer-close" onclick="GetgozoApp.closeCartDrawer()">✕</button>
        </div>
        <div class="free-delivery-bar-wrap" id="drawer-free-delivery-msg">
            Add ₹69 more for FREE Delivery!
            <div class="free-delivery-progress"><div class="free-delivery-fill" style="width:0%"></div></div>
        </div>
        <div class="drawer-body" id="cart-drawer-body"></div>
        <div class="drawer-footer" id="cart-drawer-footer">
            <div class="summary-row">
                <span data-i18n="subtotal">Subtotal</span>
                <span id="drawer-subtotal">₹0</span>
            </div>
            <div class="summary-row total">
                <span data-i18n="total">Estimated Total</span>
                <span class="amount" id="drawer-total">₹0</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:10px;margin-top:12px">
                <a href="{{ url('/cart') }}" class="btn-outline" style="justify-content:center">View Bag</a>
                <a href="{{ url('/checkout') }}" class="btn-primary" style="justify-content:center" data-i18n="proceed_to_checkout">Checkout →</a>
            </div>
        </div>
    </aside>

    <!-- SIDE MENU DRAWER -->
    <div class="drawer-backdrop" id="menu-drawer-backdrop" onclick="GetgozoApp.closeSideMenu()"></div>
    <aside class="slide-drawer" id="side-menu-drawer">
        <div class="side-menu-profile">
            <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="width:46px;height:46px;object-fit:contain;background:#fff;border-radius:10px;padding:3px" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
            <div class="u-info">
                @if(!empty($isCustomerRegistered))
                    <h4>{{ $currentUser->f_name }} {{ $currentUser->l_name }}</h4>
                    <p>{{ $currentUser->phone }}</p>
                @else
                    <h4>TOUCHSTAINS Guest</h4>
                    <p>Register before placing order</p>
                @endif
            </div>
            <button class="drawer-close" onclick="GetgozoApp.closeSideMenu()" style="margin-left:auto;background:rgba(255,255,255,0.2);color:#fff">✕</button>
        </div>
        <div class="side-menu-list">
            <a href="{{ url('/') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span data-i18n="home">Home</span>
            </a>
            <a href="{{ url('/categories') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                <span data-i18n="all_categories">All Categories</span>
            </a>
            <a href="{{ url('/shop') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><path d="M11 8v6M8 11h6"/></svg>
                <span data-i18n="all_items">Shop All Products (240)</span>
            </a>
            <a href="{{ url('/orders') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span data-i18n="my_order">My Orders</span>
            </a>
            <a href="{{ url('/track-order') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <span data-i18n="track_order">Track Order</span>
            </a>
            <a href="{{ url('/wishlist') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span data-i18n="favourite_list">Favourite List</span>
            </a>
            <a href="{{ url('/address') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span data-i18n="address">Saved Addresses</span>
            </a>
            <a href="{{ url('/notifications') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span data-i18n="notification">Notifications</span>
            </a>
            <a href="{{ url('/chat') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                <span data-i18n="live_chat">Customer Support Chat</span>
            </a>
            <a href="{{ url('/settings') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span data-i18n="settings">Language &amp; Theme Settings</span>
            </a>
            <a href="{{ url('/contact') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                <span data-i18n="contact_us">Help &amp; Contact Us</span>
            </a>
            <a href="{{ url('/faq') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span data-i18n="faq">FAQ</span>
            </a>
            <a href="{{ url('/terms') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span data-i18n="terms_and_condition">Terms &amp; Conditions</span>
            </a>
            <a href="{{ url('/privacy-policy') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                <span data-i18n="privacy_policy">Privacy Policy</span>
            </a>
            <a href="{{ url('/return-policy') }}" class="side-menu-link">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                <span data-i18n="return_policy">Return, Refund &amp; Cancellation</span>
            </a>
            <a href="{{ url('/admin') }}" class="side-menu-link" style="color:var(--primary);font-weight:700">
                <svg class="side-menu-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="M14 9h7"/><path d="M14 15h7"/><path d="M3 9h6"/><path d="M3 15h6"/></svg>
                <span>Laravel SQLite Admin Panel</span>
            </a>
            @if(!empty($isCustomerRegistered))
                <a href="{{ url('/logout') }}" class="side-menu-link" style="color:#DC2626;font-weight:700">
                    <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>Logout ({{ $registeredUser->f_name ?? 'Account' }})</span>
                </a>
            @else
                <button type="button" onclick="GetgozoApp.closeSideMenu(); window.TouchstainsAuth && window.TouchstainsAuth.open('register')" class="side-menu-link" style="color:#01684B;font-weight:700;width:100%;text-align:left;background:none;border:none;cursor:pointer">
                    <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    <span>Customer Registration</span>
                </button>
                <button type="button" onclick="GetgozoApp.closeSideMenu(); window.TouchstainsAuth && window.TouchstainsAuth.open('login')" class="side-menu-link" style="width:100%;text-align:left;background:none;border:none;cursor:pointer">
                    <svg class="side-menu-icon" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    <span data-i18n="login">Existing Customer Login</span>
                </button>
            @endif
        </div>
    </aside>

    <!-- CLIENT-SIDE DATA & ENGINE HYDRATED WITH LARAVEL SQLITE -->
    @include('partials.auth-modal')
    <script src="{{ asset('js/site-data.js') }}"></script>
    <script>
        window.GETGOZO_DATA = window.GETGOZO_DATA || {};
        @if(isset($allProducts))
        window.GETGOZO_DATA.allProducts = @json($allProducts);
        @endif
        window.LARAVEL_SQLITE_USER = @json($currentUser);
        window.LARAVEL_CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
    <script src="{{ asset('js/laravel-app.js') }}?v={{ time() }}"></script>
    @stack('scripts')
</body>
</html>
