@extends('layouts.app')

@section('content')
<main class="gf-container">

    <!-- 1. EXACT 3-CARD BANNER CAROUSEL FROM SCREENSHOT (AUTO-SLIDING) -->
    <section class="gf-banner-section" id="heroBannerSection">
        <div class="gf-banner-carousel-wrap" id="heroBannerWrap">
            <div class="gf-banner-track" id="heroBannerTrack">
                @foreach($banners as $index => $banner)
                    <div class="gf-banner-slide" data-index="{{ $index }}">
                        @if($banner->is_video)
                            <div class="gf-banner-card gf-banner-video-card" title="{{ $banner->title }}">
                                <video src="{{ $banner->media_url }}" autoplay muted loop playsinline controls preload="metadata"></video>
                                <a href="{{ $banner->product_id ? url('/product/' . $banner->product_id) : url('/shop') }}" class="gf-banner-video-cta">
                                    <span>Shop Now →</span>
                                </a>
                            </div>
                        @else
                            <a href="{{ $banner->product_id ? url('/product/' . $banner->product_id) : url('/shop') }}" class="gf-banner-card" title="{{ $banner->title }}">
                                <img src="{{ $banner->media_url }}" alt="{{ $banner->title }}" loading="{{ $index < 3 ? 'eager' : 'lazy' }}">
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="gf-banner-dots" id="heroBannerDots">
            @foreach($banners as $index => $banner)
                <button type="button" class="gf-banner-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" aria-label="Slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
    </section>

    <!-- COLORFUL TRUST & VALUE HIGHLIGHT BAR (Hidden on Mobile Devices) -->
    <div class="gf-trust-bar d-none d-md-grid">
        <div class="gf-trust-card">
            <div class="gf-trust-icon" style="background:linear-gradient(135deg,#06B6D4,#3B82F6)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <h4>Certified ISO 9001:2015</h4>
                <p>100% Authentic Quality</p>
            </div>
        </div>
        <div class="gf-trust-card">
            <div class="gf-trust-icon" style="background:linear-gradient(135deg,#10B981,#059669)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <h4>Fast Express Shipping</h4>
                <p>Safe delivery across India</p>
            </div>
        </div>
        <div class="gf-trust-card">
            <div class="gf-trust-icon" style="background:linear-gradient(135deg,#F59E0B,#D97706)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div>
                <h4>Razorpay &amp; Cash on Delivery</h4>
                <p>100% Secure Online &amp; COD Payment</p>
            </div>
        </div>
        <div class="gf-trust-card">
            <div class="gf-trust-icon" style="background:linear-gradient(135deg,#EC4899,#8B5CF6)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <h4>Instant Stain Vanish</h4>
                <p>2 drops in dry clothes magic</p>
            </div>
        </div>
    </div>

    <!-- 1.5 LIVE VIDEO DEMO SECTION (Managed from Admin Dashboard -> Live Demo Videos) -->
    @if(isset($demoVideos) && $demoVideos->count() > 0)
    <section class="gf-section gf-video-section" id="live-demos">
      <div class="gf-section-head">
        <div>
          <div class="gf-video-badge">
            <span class="gf-video-badge-dot"></span>
            <span>Live Action Demos</span>
          </div>
          <h2 class="gf-section-title">Instant Stain Vanish • Live Demos</h2>
          <p style="font-size:12.5px;color:var(--text-medium);margin-top:2px;">
            Watch how tough fabric stains, rust marks, and ink leaks disappear in seconds
          </p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <button type="button" class="gf-carousel-arrow prev" style="position:static;opacity:1;pointer-events:auto;transform:none;" onclick="document.getElementById('homeBladeVideoTrack').scrollBy({left:-480,behavior:'smooth'})" aria-label="Previous">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
          </button>
          <button type="button" class="gf-carousel-arrow next" style="position:static;transform:none;" onclick="document.getElementById('homeBladeVideoTrack').scrollBy({left:480,behavior:'smooth'})" aria-label="Next">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
          </button>
        </div>
      </div>

      <div class="gf-carousel-wrap">
        <div class="gf-video-track" id="homeBladeVideoTrack">
          @foreach($demoVideos as $demoVideo)
          <div class="gf-video-card">
            <div class="gf-video-box">
              @if(!empty($demoVideo->tag_label))
                <span class="gf-video-tag-pill">▶ {{ $demoVideo->tag_label }}</span>
              @endif
              <video src="{{ $demoVideo->video_url }}" playsinline controls preload="metadata"></video>
            </div>
            <div class="gf-video-info">
              <h3 class="gf-video-title">{{ $demoVideo->title }}</h3>
              @if(!empty($demoVideo->subtitle))
                <span class="gf-video-subtitle">{{ $demoVideo->subtitle }}</span>
              @endif
              <a href="{{ $demoVideo->product_url }}" class="gf-video-btn">
                <span>View Product</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
              </a>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </section>
    @endif

    <!-- 2. EXACT POPULAR CATEGORIES (7 Circular Photos Centered) -->
    <section class="gf-section">
        <div class="gf-section-head">
            <h2 class="gf-section-title" data-i18n="popular_category">Popular Categories</h2>
        </div>

        <div class="gf-pop-cat-row" id="home-categories-grid">
            @foreach($parentCategories as $cat)
                <a href="{{ url('/categories?cat=' . $cat->id) }}" class="gf-pop-cat-item">
                    <div class="gf-pop-cat-circle">
                        <img src="{{ asset('storage/category/' . $cat->image) }}" alt="{{ $cat->name }}" loading="lazy" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                    </div>
                    <span class="gf-pop-cat-name">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 3. DAILY NEEDS (Horizontal Carousel of Vertical Product Cards + Floating Arrow) -->
    <section class="gf-section">
        <div class="gf-section-head">
            <h2 class="gf-section-title" data-i18n="daily_needs">Daily Needs</h2>
            <a href="{{ url('/shop?type=daily_needs') }}" class="gf-view-all" data-i18n="view_all">View All</a>
        </div>
        <div class="gf-carousel-wrap">
            <button type="button" class="gf-carousel-arrow prev" onclick="document.getElementById('home-daily-needs-track').scrollBy({left:-480,behavior:'smooth'})" aria-label="Previous">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </button>
            <div class="gf-carousel-track" id="home-daily-needs-track">
                @foreach($dailyNeeds as $product)
                    @include('partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
                @endforeach
            </div>
            <button type="button" class="gf-carousel-arrow next" onclick="document.getElementById('home-daily-needs-track').scrollBy({left:480,behavior:'smooth'})" aria-label="Next">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </div>
    </section>

    <!-- 4. FEATURED ITEMS (Horizontal Carousel of Horizontal Product Cards + Floating Arrow) -->
    <section class="gf-section">
        <div class="gf-section-head">
            <h2 class="gf-section-title" data-i18n="featured_items">Featured Items</h2>
            <a href="{{ url('/shop?type=featured') }}" class="gf-view-all" data-i18n="view_all">View All</a>
        </div>
        <div class="gf-carousel-wrap">
            <button type="button" class="gf-carousel-arrow prev" onclick="document.getElementById('home-featured-track').scrollBy({left:-560,behavior:'smooth'})" aria-label="Previous">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </button>
            <div class="gf-hcard-track" id="home-featured-track">
                @foreach($featuredProducts as $product)
                    @include('partials.product-card-horizontal', ['product' => $product, 'wishlistIds' => $wishlistIds])
                @endforeach
            </div>
            <button type="button" class="gf-carousel-arrow next" onclick="document.getElementById('home-featured-track').scrollBy({left:560,behavior:'smooth'})" aria-label="Next">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </div>
    </section>

    <!-- 5. MOST REVIEWED ITEMS (Horizontal Carousel of Vertical Product Cards + Floating Arrow) -->
    <section class="gf-section">
        <div class="gf-section-head">
            <h2 class="gf-section-title" data-i18n="most_reviewed_items">Most Reviewed Items</h2>
            <a href="{{ url('/shop?type=most_reviewed') }}" class="gf-view-all" data-i18n="view_all">View All</a>
        </div>
        <div class="gf-carousel-wrap">
            <button type="button" class="gf-carousel-arrow prev" onclick="document.getElementById('home-reviewed-track').scrollBy({left:-480,behavior:'smooth'})" aria-label="Previous">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </button>
            <div class="gf-carousel-track" id="home-reviewed-track">
                @foreach($mostReviewed as $product)
                    @include('partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
                @endforeach
            </div>
            <button type="button" class="gf-carousel-arrow next" onclick="document.getElementById('home-reviewed-track').scrollBy({left:480,behavior:'smooth'})" aria-label="Next">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </div>
    </section>

    <!-- 6. ALL ITEMS (240 Products from SQLite) -->
    <section class="gf-section">
        <div class="gf-section-head">
            <h2 class="gf-section-title" data-i18n="all_items">All Items</h2>
            <a href="{{ url('/shop') }}" class="gf-view-all" data-i18n="view_all">View All</a>
        </div>
        <div class="product-grid" id="home-all-products-grid">
            @foreach($allProducts->take(15) as $product)
                @include('partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
            @endforeach
        </div>
        <div id="home-load-more-wrap" style="text-align:center;margin-top:28px">
            <button class="btn-outline" onclick="GetgozoApp.loadMoreHomeProducts()" style="padding:10px 28px;font-size:13.5px">
                See More ({{ max(0, $allProducts->count() - 15) }})
            </button>
        </div>
    </section>

</main>

<!-- Floating Green WhatsApp Button -->
<a href="https://wa.me/917679416294?text=Hello%20TOUCHSTAINS!" target="_blank" rel="noopener" class="gf-whatsapp-fab" title="Chat on WhatsApp (+91 7679416294 / 7039214465)">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="#FFFFFF">
        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824z"/>
    </svg>
</a>
@endsection
