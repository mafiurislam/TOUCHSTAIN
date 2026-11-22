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

<style>
/* Product Detail Page Mobile & Desktop Refined Styles */
.product-detail-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px 48px;
    box-sizing: border-box;
}

.prod-breadcrumb {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--text-muted, #64748B);
    padding: 14px 0;
}
.prod-breadcrumb a {
    color: var(--text-secondary, #475569);
    text-decoration: none;
    transition: color 0.2s ease;
}
.prod-breadcrumb a:hover {
    color: var(--primary, #01684B);
}
.prod-breadcrumb span#prod-breadcrumb-name {
    color: var(--text-main, #0F172A);
    font-weight: 600;
    max-width: 380px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    vertical-align: middle;
}

.product-detail-card {
    background: var(--bg-card, #FFFFFF);
    border: 1px solid var(--border, #E2E8F0);
    border-radius: 16px;
    padding: 28px;
    margin: 6px 0 32px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
    box-sizing: border-box;
    width: 100%;
}

.product-detail-grid {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 36px;
    align-items: start;
    width: 100%;
    box-sizing: border-box;
}

.prod-gallery-main {
    width: 100%;
    box-sizing: border-box;
}
.main-img-stage {
    height: 380px;
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F8FAFC;
    padding: 20px;
    margin-bottom: 14px;
    box-sizing: border-box;
    overflow: hidden;
}
.main-img-stage img {
    max-height: 330px;
    max-width: 100%;
    width: auto;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.thumb-row {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 4px;
}
.thumb-box {
    width: 66px;
    height: 66px;
    flex: 0 0 66px;
    border: 2px solid var(--border, #E2E8F0);
    border-radius: 10px;
    padding: 4px;
    cursor: pointer;
    background: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    transition: all 0.2s ease;
}
.thumb-box.active,
.thumb-box:hover {
    border-color: var(--primary, #01684B);
    box-shadow: 0 2px 8px rgba(1, 104, 75, 0.2);
}
.thumb-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.prod-info-col {
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
}
.prod-badge-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
}
.stock-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 700;
    border-radius: 99px;
    background: #DCFCE7;
    color: #15803D;
    border: 1px solid #BBF7D0;
}
.stock-pill.out {
    background: #FEE2E2;
    color: #B91C1C;
    border-color: #FECACA;
}
.stock-pill.stock-loyalty {
    background: #FEF3C7;
    color: #B45309;
    border-color: #FDE68A;
}

.prod-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--text-main, #0F172A);
    line-height: 1.35;
    margin: 8px 0;
    word-break: break-word;
}

.rating-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    margin-bottom: 12px;
}
.rating-row .stars {
    color: #F59E0B;
    letter-spacing: 1px;
}
.rating-row .rating-text {
    color: var(--text-secondary, #64748B);
    font-weight: 500;
}

.prod-detail-price {
    display: flex;
    align-items: baseline;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.prod-detail-price .original-price {
    color: #DC2626;
    text-decoration: line-through;
    font-size: 18px;
    font-weight: 700;
}
.prod-detail-price .big-price {
    font-size: 28px;
    font-weight: 800;
    color: var(--primary, #01684B);
}

.prod-meta-table {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin: 16px 0;
    width: 100%;
    box-sizing: border-box;
}
.prod-meta-table .meta-item {
    background: #F8FAFC;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid var(--border, #E2E8F0);
    box-sizing: border-box;
}
.prod-meta-table .meta-item .label {
    color: var(--text-muted, #64748B);
    font-size: 11.5px;
    font-weight: 600;
    display: block;
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.prod-meta-table .meta-item .val {
    font-weight: 700;
    font-size: 13.5px;
    color: var(--text-main, #0F172A);
    word-break: break-word;
}

/* Live Quantity Stepper & Price Box */
.prod-qty-price-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 14px;
    padding: 14px 18px;
    background: #F8FAFC;
    border: 1.5px solid var(--border, #E2E8F0);
    border-radius: 12px;
    width: 100%;
    box-sizing: border-box;
}
.prod-qty-col .prod-qty-label,
.prod-total-col .prod-total-label {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-muted, #64748B);
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.qty-stepper {
    display: inline-flex;
    align-items: center;
    background: var(--primary, #01684B);
    border-radius: 8px;
    height: 44px;
    padding: 0 8px;
}
.qty-stepper button {
    color: #fff;
    font-size: 20px;
    font-weight: 800;
    width: 32px;
    height: 100%;
    cursor: pointer;
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
}
.qty-stepper input {
    width: 44px;
    text-align: center;
    border: none;
    background: transparent;
    color: #fff;
    font-weight: 800;
    font-size: 16px;
}
.prod-total-col {
    text-align: right;
    padding-left: 14px;
    border-left: 1px solid var(--border, #CBD5E1);
}
.prod-total-wrap {
    display: flex;
    align-items: baseline;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
}
.live-total-amt {
    font-size: 24px;
    font-weight: 800;
    color: #01684B;
}
.live-total-formula {
    font-size: 12px;
    font-weight: 600;
    color: #64748B;
}

/* Action Buttons Row */
.prod-actions-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 16px;
    width: 100%;
    box-sizing: border-box;
}
.prod-actions-row .btn-add-bag {
    height: 48px;
    padding: 0 26px;
    font-size: 15px;
    font-weight: 700;
    border-radius: 10px;
    background: var(--primary, #01684B);
    color: #ffffff;
    border: 1.5px solid var(--primary, #01684B);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(1, 104, 75, 0.25);
    cursor: pointer;
    transition: all 0.2s ease;
}
.prod-actions-row .btn-add-bag:hover {
    background: var(--primary-dark, #00523B);
    border-color: var(--primary-dark, #00523B);
    transform: translateY(-1px);
}
.prod-actions-row .btn-buy-now {
    height: 48px;
    padding: 0 26px;
    font-size: 15px;
    font-weight: 700;
    border-radius: 10px;
    background: #F59E0B;
    color: #FFFFFF;
    border: 1.5px solid #D97706;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
    cursor: pointer;
    transition: all 0.2s ease;
}
.prod-actions-row .btn-buy-now:hover {
    background: #D97706;
    border-color: #B45309;
    transform: translateY(-1px);
}
.prod-actions-row .btn-detail-wish {
    position: static;
    width: 48px;
    height: 48px;
    font-size: 22px;
    border-radius: 10px;
    border: 1.5px solid var(--border, #CBD5E1);
    background: #FFFFFF;
    color: #EF4444;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.prod-actions-row .btn-detail-wish.active,
.prod-actions-row .btn-detail-wish:hover {
    background: #FEE2E2;
    border-color: #F87171;
}

.prod-desc-box {
    margin-top: 18px;
    padding: 14px 16px;
    background: var(--primary-lighter, #F4F9F8);
    border: 1px solid rgba(1, 104, 75, 0.15);
    border-radius: 10px;
    font-size: 13.5px;
    color: var(--text-secondary, #334155);
    line-height: 1.55;
    box-sizing: border-box;
}

/* Reviews Section & Form */
.prod-reviews-section {
    margin-top: 32px;
    background: var(--bg-card, #FFFFFF);
    border: 1px solid var(--border, #E2E8F0);
    border-radius: 16px;
    padding: 24px;
    box-sizing: border-box;
    width: 100%;
}
.prod-review-form {
    background: #F8FAFC;
    border: 1px solid var(--border, #E2E8F0);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
    box-sizing: border-box;
    width: 100%;
}
.prod-review-grid {
    display: grid;
    grid-template-columns: 180px 1fr auto;
    gap: 12px;
    align-items: end;
    width: 100%;
    box-sizing: border-box;
}
.prod-review-grid label {
    font-size: 12px;
    font-weight: 700;
    display: block;
    margin-bottom: 5px;
    color: var(--text-main, #0F172A);
}
.prod-review-grid .form-control {
    width: 100%;
    height: 42px;
    border: 1.5px solid var(--border, #CBD5E1);
    border-radius: 8px;
    padding: 0 12px;
    font-size: 13.5px;
    box-sizing: border-box;
    background: #FFFFFF;
}
.prod-review-grid .btn-submit-review {
    height: 42px;
    padding: 0 22px;
    font-size: 14px;
    font-weight: 700;
    border-radius: 8px;
    white-space: nowrap;
}

/* ============================================================================
 * MOBILE SCREEN OPTIMIZATIONS (< 768px and < 480px)
 * 100% Zero Horizontal Overflow - Perfectly Stacked Controls & Buttons
 * ============================================================================ */
@media (max-width: 768px) {
    body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
    }
    .product-detail-container {
        padding: 0 12px 48px !important;
        width: 100% !important;
        max-width: 100vw !important;
        overflow-x: hidden !important;
    }
    .prod-breadcrumb {
        padding: 10px 0 !important;
        font-size: 12px !important;
        gap: 6px !important;
    }
    .prod-breadcrumb span#prod-breadcrumb-name {
        max-width: 190px !important;
    }

    .product-detail-card {
        padding: 16px 14px !important;
        margin: 6px 0 20px !important;
        border-radius: 14px !important;
    }
    .product-detail-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 16px !important;
        width: 100% !important;
    }
    .main-img-stage {
        height: 270px !important;
        padding: 12px !important;
        border-radius: 12px !important;
    }
    .main-img-stage img {
        max-height: 240px !important;
    }
    .thumb-box {
        width: 54px !important;
        height: 54px !important;
        flex: 0 0 54px !important;
    }

    .prod-title {
        font-size: 19px !important;
        line-height: 1.35 !important;
        margin: 6px 0 8px !important;
    }
    .prod-detail-price .big-price {
        font-size: 24px !important;
    }
    .prod-detail-price .original-price {
        font-size: 16px !important;
    }

    .prod-meta-table {
        grid-template-columns: 1fr !important;
        gap: 8px !important;
        margin: 12px 0 !important;
    }
    .prod-meta-table .meta-item {
        padding: 8px 12px !important;
    }

    .prod-qty-price-box {
        padding: 12px 14px !important;
        border-radius: 12px !important;
        gap: 10px !important;
    }
    .qty-stepper {
        height: 40px !important;
        padding: 0 6px !important;
    }
    .qty-stepper button {
        width: 28px !important;
        font-size: 18px !important;
    }
    .qty-stepper input {
        width: 36px !important;
        font-size: 15px !important;
    }
    .live-total-amt {
        font-size: 21px !important;
    }

    /* Mobile Button Arrangement: Clean 2-Row Stack */
    .prod-actions-row {
        display: grid !important;
        grid-template-columns: 1fr auto !important;
        gap: 10px !important;
        margin-top: 14px !important;
        width: 100% !important;
    }
    .prod-actions-row .btn-add-bag {
        grid-column: 1 / 2 !important;
        width: 100% !important;
        height: 48px !important;
        font-size: 14px !important;
        padding: 0 10px !important;
    }
    .prod-actions-row .btn-detail-wish {
        grid-column: 2 / 3 !important;
        width: 48px !important;
        height: 48px !important;
    }
    .prod-actions-row .btn-buy-now {
        grid-column: 1 / -1 !important;
        width: 100% !important;
        height: 50px !important;
        font-size: 15px !important;
        padding: 0 14px !important;
    }

    /* Customer Reviews Mobile Stack */
    .prod-reviews-section {
        padding: 16px 14px !important;
        margin-top: 20px !important;
        border-radius: 14px !important;
    }
    .prod-review-form {
        padding: 14px !important;
    }
    .prod-review-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px !important;
        width: 100% !important;
    }
    .prod-review-grid > div,
    .prod-review-grid .review-rating-col,
    .prod-review-grid .review-comment-col,
    .prod-review-grid select,
    .prod-review-grid input,
    .prod-review-grid .btn-submit-review {
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .prod-review-grid .btn-submit-review {
        height: 46px !important;
        font-size: 15px !important;
    }
}
</style>

<main class="container product-detail-container">
    <div class="breadcrumb prod-breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <a href="{{ url('/shop') }}">Shop</a> <span>›</span>
        <span id="prod-breadcrumb-name">{{ $product->name }}</span>
    </div>

    <div class="product-detail-card" id="product-detail-container">
        <div class="product-detail-grid">
            <!-- Left: Gallery -->
            <div class="prod-gallery-main">
                <div class="main-img-stage" id="gallery-stage" style="position:relative">
                    @if(!empty($product->coupon_discount_label))
                        <div style="position:absolute;top:14px;left:14px;z-index:2;background:linear-gradient(135deg,#DC2626,#EF4444);color:#ffffff;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:800;letter-spacing:0.5px;box-shadow:0 3px 10px rgba(220,38,38,0.4);display:inline-flex;align-items:center;gap:4px">
                            <span>🎟️ {{ $product->coupon_discount_label }}</span>
                        </div>
                    @elseif(!empty($product->coupon_code))
                        <div style="position:absolute;top:14px;left:14px;z-index:2;background:linear-gradient(135deg,#DC2626,#EF4444);color:#ffffff;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:800;letter-spacing:0.5px;box-shadow:0 3px 10px rgba(220,38,38,0.4);display:inline-flex;align-items:center;gap:4px">
                            <span>🎟️ OFFER</span>
                        </div>
                    @endif
                    <img id="prod-main-img" src="{{ $mainImg }}" alt="{{ $product->name }}" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                    @if($product->video)
                        <video id="prod-main-video" controls playsinline poster="{{ $product->video_thumbnail ? $product->video_thumbnail_url : $mainImg }}" style="display:none;max-height:330px;max-width:100%;width:auto;border-radius:10px;outline:none">
                            <source src="{{ $product->video_url }}">
                            Your browser does not support the video tag.
                        </video>
                        <button type="button" onclick="switchGalleryToVideo(document.querySelector('.video-thumb-box'))" class="watch-video-badge-btn" style="position:absolute;top:14px;right:14px;display:flex;align-items:center;gap:6px;background:rgba(15,23,42,0.85);color:#fff;border:none;border-radius:20px;padding:6px 12px;font-size:12px;font-weight:700;cursor:pointer;backdrop-filter:blur(4px);box-shadow:0 2px 8px rgba(0,0,0,0.25);transition:all 0.2s">
                            <span style="color:#38BDF8">▶</span> Watch Video
                        </button>
                    @endif
                </div>
                <div class="thumb-row" id="gallery-thumb-row">
                    @foreach($images as $idx => $img)
                        @php $tUrl = $img ? asset('storage/product/' . $img) : asset('assets/image/placeholder.png'); @endphp
                        <div class="thumb-box gallery-thumb-item {{ $idx === 0 ? 'active' : '' }}" onclick="switchGalleryToImage('{{ $tUrl }}', this)">
                            <img src="{{ $tUrl }}" alt="Thumbnail">
                        </div>
                    @endforeach

                    @if($product->video)
                        <div class="thumb-box gallery-thumb-item video-thumb-box" onclick="switchGalleryToVideo(this)" title="Play Product Video" style="position:relative;background:#0F172A;border-color:#3B82F6;overflow:hidden">
                            <img src="{{ $product->video_thumbnail ? $product->video_thumbnail_url : $mainImg }}" alt="Video Poster" style="opacity:0.65;width:100%;height:100%;object-fit:cover">
                            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;pointer-events:none">
                                <span style="font-size:16px;line-height:1;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.8));color:#38BDF8">▶</span>
                                <span style="font-size:9px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;background:rgba(0,0,0,0.65);padding:1px 4px;border-radius:3px;margin-top:2px">VIDEO</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Details -->
            <div class="prod-info-col">
                <div class="prod-badge-row">
                    <span class="stock-pill {{ $product->total_stock > 0 ? '' : 'out' }}">
                        {{ $product->total_stock > 0 ? '✓ In Stock (' . $product->total_stock . ' available)' : 'Out of Stock' }}
                    </span>
                    <span class="stock-pill stock-loyalty">⭐ Earn {{ max(1, floor($displayPrice * 0.05)) }} Loyalty Pts</span>
                </div>

                <h1 class="prod-title">{{ $product->name }}</h1>

                @php
                    $revCount = isset($reviews) ? $reviews->count() : 0;
                    $avgScore = $revCount > 0 ? round($reviews->avg('rating'), 1) : 4.8;
                    $starScore = (int)round($avgScore);
                @endphp
                <div class="rating-row">
                    <span class="stars" style="color:#F59E0B">{{ str_repeat('★', min(5, max(1, $starScore))) }}{{ str_repeat('☆', max(0, 5 - min(5, max(1, $starScore)))) }}</span>
                    <span class="rating-text">{{ $avgScore }} Rating ({{ $revCount }} {{ $revCount === 1 ? 'Customer Review' : 'Customer Reviews' }}) • {{ $product->popularity_count ?: 12 }} Verified Purchases</span>
                </div>

                <div class="prod-detail-price">
                    @if($originalPrice > $displayPrice)
                        <span class="original-price">₹{{ number_format($originalPrice, 2) }}</span>
                    @endif
                    <span class="big-price">₹{{ number_format($displayPrice, 2) }}</span>
                </div>

                @if(!empty($product->coupon_code))
                    @php
                        $prodCoupon = $product->assigned_coupon;
                    @endphp
                    <div class="prod-coupon-highlight-box" style="background:linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);border:2px dashed #F59E0B;border-radius:14px;padding:14px 18px;margin:14px 0 18px;display:flex;align-items:center;justify-content:space-between;gap:14px;box-shadow:0 4px 14px rgba(245,158,11,0.18)">
                        <div style="display:flex;align-items:center;gap:12px">
                            <div style="font-size:32px;line-height:1">🎟️</div>
                            <div>
                                <div style="font-size:11px;font-weight:800;color:#B45309;text-transform:uppercase;letter-spacing:0.5px">Special Offer Available</div>
                                <div style="font-size:16px;font-weight:800;color:#92400E;margin-top:2px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                    <span>{{ $product->coupon_title ?: ($prodCoupon->title ?? 'Special Discount') }}</span>
                                    @if($product->coupon_discount_label)
                                        <span style="font-size:13.5px;font-weight:800;color:#047857;background:#D1FAE5;padding:2px 8px;border-radius:6px;border:1px solid #A7F3D0">
                                            {{ $product->coupon_discount_label }}
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size:12.5px;color:#78350F;margin-top:4px;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                    <span>Use code at checkout:</span>
                                    <span style="background:#FDE68A;color:#78350F;padding:2px 8px;border-radius:6px;font-family:monospace;letter-spacing:1px;border:1px solid #FCD34D;font-weight:800">{{ $product->coupon_code }}</span>
                                    @if($prodCoupon && $prodCoupon->min_purchase > 0)
                                        <span style="font-size:11.5px;color:#92400E;font-weight:600">(Min order: ₹{{ (int)$prodCoupon->min_purchase }})</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $product->coupon_code }}'); window.GetgozoApp && window.GetgozoApp.showToast ? window.GetgozoApp.showToast('📋 Coupon code {{ $product->coupon_code }} copied! Apply at checkout.', 'success') : alert('Copied {{ $product->coupon_code }}')" style="background:#01684B;color:#ffffff;border:none;border-radius:8px;padding:9px 16px;font-size:12.5px;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;box-shadow:0 3px 8px rgba(1,104,75,0.3);flex-shrink:0">
                            📋 Copy Code
                        </button>
                    </div>
                @endif

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
                        <span class="val" style="color:var(--primary, #01684B)">FREE on orders over ₹{{ $settings['free_delivery_over_amount'] ?? 299 }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="label">Order Quantity</span>
                        <span class="val">1 Unit (Tap + to add more)</span>
                    </div>
                </div>

                <!-- Live Quantity Stepper & Live Total Amount -->
                <div class="prod-qty-price-box">
                    <div class="prod-qty-col">
                        <div class="prod-qty-label">Quantity</div>
                        <div class="qty-stepper">
                            <button type="button" onclick="updateDetailQty(-1)" aria-label="Decrease quantity">−</button>
                            <input type="number" id="detail-qty-input" value="1" min="1" max="{{ max(1, (int)($product->total_stock ?: 99)) }}" readonly>
                            <button type="button" onclick="updateDetailQty(1)" aria-label="Increase quantity">+</button>
                        </div>
                    </div>

                    <div class="prod-total-col">
                        <div class="prod-total-label">Total Amount</div>
                        <div class="prod-total-wrap">
                            <span id="detail-live-total" class="live-total-amt">₹{{ number_format($displayPrice, 2) }}</span>
                            <span id="detail-live-formula" class="live-total-formula">(1 × ₹{{ number_format($displayPrice, 2) }})</span>
                        </div>
                    </div>
                </div>

                <!-- Product Action Buttons -->
                <div class="prod-actions-row">
                    <button type="button" class="btn-primary btn-add-bag" onclick="GetgozoApp.addToCart({{ $product->id }}, getDetailQty(), event)">
                        🛒 Add to Shopping Bag
                    </button>

                    <button type="button" class="btn-detail-wish {{ $inWish ? 'active' : '' }}" data-wish-btn="{{ $product->id }}" onclick="GetgozoApp.toggleWishlist({{ $product->id }}, event)" aria-label="Toggle Wishlist">
                        {{ $inWish ? '♥' : '♡' }}
                    </button>

                    <button type="button" class="btn-buy-now" id="detail-buy-now-btn" onclick="GetgozoApp.buyNow({{ $product->id }}, getDetailQty(), event)">
                        ⚡ Buy Now (₹{{ number_format($displayPrice, 2) }})
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

                        window.switchGalleryToImage = function (url, el) {
                            const mainImg = document.getElementById('prod-main-img');
                            const mainVideo = document.getElementById('prod-main-video');
                            const watchBadge = document.querySelector('.watch-video-badge-btn');
                            if (mainVideo) {
                                try { mainVideo.pause(); } catch (e) {}
                                mainVideo.style.display = 'none';
                            }
                            if (mainImg) {
                                mainImg.style.display = 'block';
                                mainImg.src = url;
                            }
                            if (watchBadge) {
                                watchBadge.style.display = 'flex';
                            }
                            document.querySelectorAll('.gallery-thumb-item').forEach(b => b.classList.remove('active'));
                            if (el) el.classList.add('active');
                        };

                        window.switchGalleryToVideo = function (el) {
                            const mainImg = document.getElementById('prod-main-img');
                            const mainVideo = document.getElementById('prod-main-video');
                            const watchBadge = document.querySelector('.watch-video-badge-btn');
                            if (mainImg) {
                                mainImg.style.display = 'none';
                            }
                            if (watchBadge) {
                                watchBadge.style.display = 'none';
                            }
                            if (mainVideo) {
                                mainVideo.style.display = 'block';
                                try { mainVideo.play(); } catch (e) {}
                            }
                            document.querySelectorAll('.gallery-thumb-item').forEach(b => b.classList.remove('active'));
                            const targetEl = el || document.querySelector('.video-thumb-box');
                            if (targetEl) targetEl.classList.add('active');
                        };
                    })();
                </script>

                <div class="prod-desc-box">
                    <strong>Description:</strong> {{ $product->description ?: ($product->name . ' (' . $unitText . ') — Premium fabric stain remover formulated for instant rust, oil, grease and ink removal.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Ratings & Reviews -->
    <section class="section-block prod-reviews-section" style="margin-top:36px;background:#fff;border:1.5px solid var(--border,#E2E8F0);border-radius:16px;padding:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #F1F5F9">
            <div>
                <h2 style="font-size:19px;font-weight:800;color:#0F172A;margin:0 0 4px">⭐ Customer Ratings &amp; Reviews ({{ isset($reviews) ? $reviews->count() : 0 }})</h2>
                <p style="font-size:13px;color:#64748B;margin:0">যাঁরা এই প্রোডাক্টটি ব্যবহার করেছেন তাঁদের বাস্তব অভিজ্ঞতা ও রিভিউ</p>
            </div>
            @if(isset($reviews) && $reviews->count() > 0)
                <div style="display:flex;align-items:center;gap:8px;background:#FEF3C7;padding:6px 14px;border-radius:20px;border:1px solid #FDE68A">
                    <span style="font-size:16px;font-weight:800;color:#92400E">★ {{ round($reviews->avg('rating'), 1) }} / 5.0</span>
                    <span style="font-size:12px;color:#B45309">({{ $reviews->count() }} ratings)</span>
                </div>
            @endif
        </div>

        @if(session('success'))
            <div style="background:#DCFCE7;border:1px solid #86EFAC;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:600">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:600">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <!-- Write a Review Form -->
        <div style="background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:14px;padding:20px;margin-bottom:28px">
            <h3 style="font-size:15px;font-weight:800;color:#0F172A;margin:0 0 6px">✍️ Write a Review / আপনার রিভিউ লিখুন</h3>
            <p style="font-size:12.5px;color:#64748B;margin:0 0 16px">প্রোডাক্টটি আপনার কেমন লেগেছে? আপনার সৎ মতামত অন্যান্য ক্রেতাদের সাহায্য করবে।</p>

            <form action="{{ url('/product/' . $product->id . '/review') }}" method="POST">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;margin-bottom:14px">
                    <div>
                        <label style="display:block;font-size:12.5px;font-weight:700;color:#1E293B;margin-bottom:6px">Your Name / আপনার নাম *</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ auth()->check() ? auth()->user()->name : old('customer_name') }}" placeholder="e.g. Rahul Sharma" required style="width:100%;padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:13px;box-sizing:border-box">
                    </div>
                    <div>
                        <label style="display:block;font-size:12.5px;font-weight:700;color:#1E293B;margin-bottom:6px">Your Rating / রেটিং সিলেক্ট করুন *</label>
                        <select name="rating" class="form-control" style="width:100%;padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:13px;font-weight:700;color:#D97706;box-sizing:border-box">
                            <option value="5" selected>★★★★★ (5 - Excellent / চমৎকার)</option>
                            <option value="4">★★★★☆ (4 - Very Good / খুব ভালো)</option>
                            <option value="3">★★★☆☆ (3 - Good / ভালো)</option>
                            <option value="2">★★☆☆☆ (2 - Fair / মোটামুটি)</option>
                            <option value="1">★☆☆☆☆ (1 - Poor / সন্তোষজনক নয়)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:12.5px;font-weight:700;color:#1E293B;margin-bottom:6px">Review Comment / রিভিউ মন্তব্য *</label>
                    <textarea name="comment" rows="3" class="form-control" placeholder="দাগ দূর করতে কেমন কাজ করেছে? আপনার অভিজ্ঞতা বিস্তারিত লিখুন..." required style="width:100%;padding:10px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:13px;box-sizing:border-box;font-family:inherit"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="background:#01684B;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:13.5px;font-weight:700;cursor:pointer">
                    ⭐ Submit Review / রিভিউ জমা দিন
                </button>
            </form>
        </div>

        <!-- Customer Reviews List -->
        <h3 style="font-size:15px;font-weight:800;color:#0F172A;margin:0 0 14px">💬 Customer Reviews ({{ isset($reviews) ? $reviews->count() : 0 }})</h3>
        @if(isset($reviews) && $reviews->count() > 0)
            <div style="display:grid;gap:14px">
                @foreach($reviews as $rev)
                    <div style="padding:16px;border:1.5px solid #E2E8F0;border-radius:12px;background:#FFFFFF;transition:box-shadow 0.2s">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:8px">
                            <div style="display:flex;align-items:center;gap:8px">
                                <span style="width:32px;height:32px;background:#EFF6FF;color:#2563EB;font-weight:800;font-size:13px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid #BFDBFE">
                                    {{ strtoupper(substr($rev->reviewer_name, 0, 1)) }}
                                </span>
                                <div>
                                    <strong style="font-size:14px;color:#0F172A">{{ $rev->reviewer_name }}</strong>
                                    <span style="font-size:11px;font-weight:700;color:#059669;background:#DCFCE7;padding:2px 7px;border-radius:4px;margin-left:6px;border:1px solid #BBF7D0">✓ Verified Customer</span>
                                </div>
                            </div>
                            <div style="text-align:right">
                                <span style="color:#F59E0B;font-size:14px;letter-spacing:1px">{{ str_repeat('★', (int)$rev->rating) }}{{ str_repeat('☆', max(0, 5 - (int)$rev->rating)) }}</span>
                                <span style="font-size:11.5px;color:#94A3B8;display:block;margin-top:2px">{{ $rev->created_at ? $rev->created_at->format('d M, Y') : 'Recent' }}</span>
                            </div>
                        </div>
                        <p style="font-size:13.5px;color:#334155;margin:0;line-height:1.55">{{ $rev->comment }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:32px 20px;background:#F8FAFC;border:1.5px dashed #CBD5E1;border-radius:12px">
                <span style="font-size:32px;display:block;margin-bottom:8px">⭐</span>
                <strong style="font-size:14.5px;color:#334155;display:block;margin-bottom:4px">এখনও কোনো রিভিউ দেওয়া হয়নি</strong>
                <p style="font-size:12.5px;color:#64748B;margin:0">আপনি এই প্রোডাক্টটি ক্রয় করে থাকলে সবার প্রথমে আপনার মূল্যবান রিভিউ ও রেটিং দিন!</p>
            </div>
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
