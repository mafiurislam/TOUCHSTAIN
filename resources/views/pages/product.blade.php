@extends('layouts.app')

@section('content')
@php
    $images = is_array($product->image) && count($product->image) > 0 ? $product->image : [''];
    $mainImg = $images[0] ? asset('storage/product/' . $images[0]) : asset('assets/image/placeholder.png');
    $displayPrice = (float)$product->price;
    $originalPrice = (float)($product->mrp ?? 0);
    $capVal = $product->capacity ?? 1;
    $capFmt = ($capVal == floor($capVal)) ? (int)$capVal : $capVal;
    $unitText = $capFmt . ' ' . ($product->unit ?? 'ml');
    $inWish = in_array($product->id, $wishlistIds ?? []);
@endphp
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <a href="{{ url('/shop') }}">Shop</a> <span>›</span>
        <span id="prod-breadcrumb-name">{{ $product->name }}</span>
    </div>

    <div class="product-detail-card" id="product-detail-container">
        <div class="product-detail-grid">
            <!-- Left: Gallery -->
            <div class="prod-gallery-main">
                <div class="main-img-stage">
                    <img id="prod-main-img" src="{{ $mainImg }}" alt="{{ $product->name }}" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                </div>
                <div class="thumb-row">
                    @foreach($images as $idx => $img)
                        @php $tUrl = $img ? asset('storage/product/' . $img) : asset('assets/image/placeholder.png'); @endphp
                        <div class="thumb-box {{ $idx === 0 ? 'active' : '' }}" onclick="document.getElementById('prod-main-img').src='{{ $tUrl }}'">
                            <img src="{{ $tUrl }}" alt="Thumbnail">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Details -->
            <div class="prod-info-col">
                <div class="prod-badge-row">
                    <span class="stock-pill {{ $product->total_stock > 0 ? '' : 'out' }}">
                        {{ $product->total_stock > 0 ? '✓ In Stock (' . $product->total_stock . ' available)' : 'Out of Stock' }}
                    </span>
                    <span class="stock-pill" style="background:#FEF3C7;color:#D97706">⭐ Earn {{ max(1, floor($displayPrice * 0.05)) }} Loyalty Pts</span>
                </div>

                <h1>{{ $product->name }}</h1>

                <div class="rating-row" style="font-size:14px">
                    <span class="stars">★★★★★</span>
                    <span style="color:var(--text-secondary)">4.8 Rating • {{ $product->popularity_count ?: 12 }} Verified Purchases</span>
                </div>

                <div class="prod-detail-price" style="display:flex;align-items:baseline;gap:12px;flex-wrap:wrap">
                    @if($originalPrice > $displayPrice)
                        <span style="color:#DC2626;text-decoration:line-through;font-size:19px;font-weight:700">₹{{ number_format($originalPrice, 2) }}</span>
                    @endif
                    <span class="big-price">₹{{ number_format($displayPrice, 2) }}</span>
                </div>

                <div class="prod-meta-table">
                    <div class="meta-item">
                        <span class="label">Pack Size / Unit</span>
                        <span class="val">{{ $unitText }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="label">Dispatch Center</span>
                        <span class="val">TOUCHSTAINS Mumbai Hub</span>
                    </div>
                    <div class="meta-item">
                        <span class="label">Delivery Charge</span>
                        <span class="val" style="color:var(--primary)">FREE on orders over ₹{{ $settings['free_delivery_over_amount'] ?? 299 }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="label">Order Quantity</span>
                        <span class="val">1 Unit (Tap + to add more)</span>
                    </div>
                </div>

                <!-- Live Quantity Stepper & Live Total Amount -->
                <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap;margin-top:12px;padding:14px 16px;background:#F8FAFC;border:1px solid var(--border);border-radius:12px">
                    <div>
                        <div style="font-size:11.5px;font-weight:700;color:var(--text-muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:0.3px">Quantity</div>
                        <div class="qty-stepper" style="height:44px;padding:0 8px">
                            <button type="button" onclick="updateDetailQty(-1)" aria-label="Decrease quantity">−</button>
                            <input type="number" id="detail-qty-input" value="1" min="1" max="{{ max(1, (int)($product->total_stock ?: 99)) }}" style="width:45px;text-align:center;border:none;background:transparent;color:#fff;font-weight:800;font-size:16px" readonly>
                            <button type="button" onclick="updateDetailQty(1)" aria-label="Increase quantity">+</button>
                        </div>
                    </div>

                    <div style="padding-left:14px;border-left:1px solid var(--border)">
                        <div style="font-size:11.5px;font-weight:700;color:var(--text-muted);margin-bottom:3px;text-transform:uppercase;letter-spacing:0.3px">Total Amount</div>
                        <div style="display:flex;align-items:baseline;gap:8px;flex-wrap:wrap">
                            <span id="detail-live-total" style="font-size:24px;font-weight:800;color:#01684B">₹{{ number_format($displayPrice, 2) }}</span>
                            <span id="detail-live-formula" style="font-size:12px;font-weight:600;color:#64748B">(1 × ₹{{ number_format($displayPrice, 2) }})</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px">
                    <button type="button" class="btn-primary" style="height:46px;padding:0 26px;font-size:15px" onclick="GetgozoApp.addToCart({{ $product->id }}, getDetailQty(), event)">
                        🛒 Add to Shopping Bag
                    </button>

                    <button type="button" class="btn-outline" id="detail-buy-now-btn" style="height:46px;padding:0 24px;font-size:15px;font-weight:700" onclick="GetgozoApp.buyNow({{ $product->id }}, getDetailQty(), event)">
                        ⚡ Buy Now (₹{{ number_format($displayPrice, 2) }})
                    </button>

                    <button type="button" class="wish-btn {{ $inWish ? 'active' : '' }}" data-wish-btn="{{ $product->id }}" style="position:static;width:46px;height:46px;font-size:20px" onclick="GetgozoApp.toggleWishlist({{ $product->id }}, event)">
                        {{ $inWish ? '♥' : '♡' }}
                    </button>
                </div>

                <script>
                    (function () {
                        const UNIT_PRICE = {{ (float)$displayPrice }};
                        const MAX_STOCK = {{ max(1, (int)($product->total_stock ?: 99)) }};
                        window.getDetailQty = function () {
                            const el = document.getElementById('detail-qty-input');
                            return Math.max(1, parseInt(el ? el.value : 1, 10) || 1);
                        };
                        window.updateDetailQty = function (delta) {
                            const el = document.getElementById('detail-qty-input');
                            if (!el) return;
                            const cur = parseInt(el.value || '1', 10) || 1;
                            const next = Math.max(1, Math.min(MAX_STOCK, cur + delta));
                            el.value = next;
                            const total = (UNIT_PRICE * next).toFixed(2);
                            const totalEl = document.getElementById('detail-live-total');
                            const formulaEl = document.getElementById('detail-live-formula');
                            const buyBtn = document.getElementById('detail-buy-now-btn');
                            if (totalEl) totalEl.textContent = '₹' + total;
                            if (formulaEl) formulaEl.textContent = '(' + next + ' × ₹' + UNIT_PRICE.toFixed(2) + ')';
                            if (buyBtn) buyBtn.innerHTML = '⚡ Buy Now (₹' + total + ')';
                        };
                    })();
                </script>

                <div style="margin-top:16px;padding:14px;background:var(--primary-lighter);border-radius:var(--radius-sm);font-size:13px;color:var(--text-secondary)">
                    <strong>Description:</strong> {{ $product->description ?: ($product->name . ' (' . $unitText . ') — Fresh quality guaranteed by Getgozo Jadupur.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Ratings & Reviews (SQLite) -->
    <section class="section-block" style="margin-top:32px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:24px">
        <h2 style="font-size:18px;font-weight:800;margin-bottom:16px">⭐ Customer Ratings &amp; Reviews ({{ isset($reviews) ? $reviews->count() : 0 }})</h2>

        <form action="{{ url('/product/' . $product->id . '/review') }}" method="POST" style="background:#F8FAFC;border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:20px">
            @csrf
            <div style="display:grid;grid-template-columns:180px 1fr auto;gap:12px;align-items:end;flex-wrap:wrap">
                <div>
                    <label style="font-size:12px;font-weight:700;display:block;margin-bottom:5px">Your Rating</label>
                    <select name="rating" class="form-control" style="padding:9px 10px">
                        <option value="5">★★★★★ (5 - Excellent)</option>
                        <option value="4">★★★★☆ (4 - Very Good)</option>
                        <option value="3">★★★☆☆ (3 - Good)</option>
                        <option value="2">★★☆☆☆ (2 - Fair)</option>
                        <option value="1">★☆☆☆☆ (1 - Poor)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;display:block;margin-bottom:5px">Write a Review</label>
                    <input type="text" name="comment" class="form-control" placeholder="Share your experience with this product..." required>
                </div>
                <button type="submit" class="btn-primary" style="padding:10px 20px;height:42px">Submit Review</button>
            </div>
        </form>

        @if(isset($reviews) && $reviews->count() > 0)
            <div style="display:grid;gap:12px">
                @foreach($reviews as $rev)
                    <div style="padding:14px;border:1px solid var(--border);border-radius:10px">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                            <strong style="font-size:13.5px">{{ $rev->user->name ?? 'Verified Customer' }}</strong>
                            <span style="color:#F59E0B;font-size:13px">{{ str_repeat('★', (int)$rev->rating) }}{{ str_repeat('☆', max(0, 5 - (int)$rev->rating)) }}</span>
                        </div>
                        <p style="font-size:13px;color:var(--text-secondary);margin:0">{{ $rev->comment }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p style="font-size:13px;color:var(--text-muted);margin:0">No reviews yet. Be the first to review {{ $product->name }}!</p>
        @endif
    </section>

    <!-- Related Products from SQLite -->
    <section class="section-block" style="margin-top:36px">
        <div class="section-header">
            <div class="section-title-wrap">
                <h2 class="section-title">Similar Products in This Category</h2>
            </div>
            <a href="{{ url('/shop') }}" class="view-all-link">View All →</a>
        </div>
        <div class="product-grid" id="related-products-grid">
            @foreach($relatedProducts as $relProd)
                @include('partials.product-card', ['product' => $relProd, 'wishlistIds' => $wishlistIds])
            @endforeach
        </div>
    </section>
</main>
@endsection

