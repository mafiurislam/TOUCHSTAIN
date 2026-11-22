@php
    $imgFile = is_array($product->image) && count($product->image) > 0 ? $product->image[0] : '';
    $imgUrl = $imgFile ? asset('storage/product/' . $imgFile) : asset('assets/image/placeholder.png');
    $displayPrice = (float)$product->price;
    $originalPrice = (float)($product->mrp ?? 0);
    $cap = $product->capacity ?? 1;
    $capFormatted = ($cap == floor($cap)) ? (int)$cap : $cap;
    $unitText = $capFormatted . ' ' . ($product->unit ?? 'pc');
    $inWish = in_array($product->id, $wishlistIds ?? []);
    $ratingVal = (is_array($product->rating) && count($product->rating) > 0 && isset($product->rating[0]['average']))
        ? number_format((float)$product->rating[0]['average'], 1)
        : (str_contains(strtolower($product->name), 'nutrichoice') ? '5.0' : '0.0');
@endphp
<div class="gf-vcard" data-product-id="{{ $product->id }}">
    <div class="gf-vcard-imgbox" style="position:relative">
        @if(!empty($product->coupon_discount_label))
            <div class="gf-card-coupon-badge" style="position:absolute;top:8px;left:8px;z-index:3;background:linear-gradient(135deg,#DC2626,#EF4444);color:#ffffff;padding:3px 8px;border-radius:6px;font-size:10.5px;font-weight:800;letter-spacing:0.3px;box-shadow:0 2px 6px rgba(220,38,38,0.4);display:inline-flex;align-items:center;gap:3px;pointer-events:none">
                <span>🎟️ {{ $product->coupon_discount_label }}</span>
            </div>
        @elseif(!empty($product->coupon_code))
            <div class="gf-card-coupon-badge" style="position:absolute;top:8px;left:8px;z-index:3;background:linear-gradient(135deg,#DC2626,#EF4444);color:#ffffff;padding:3px 8px;border-radius:6px;font-size:10.5px;font-weight:800;letter-spacing:0.3px;box-shadow:0 2px 6px rgba(220,38,38,0.4);display:inline-flex;align-items:center;gap:3px;pointer-events:none">
                <span>🎟️ OFFER</span>
            </div>
        @endif
        <div class="gf-vcard-actions">
            <button type="button" class="gf-action-sq {{ $inWish ? 'active' : '' }}" data-wish-btn="{{ $product->id }}" onclick="GetgozoApp.toggleWishlist({{ $product->id }}, event)" title="Add to Favourites">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="{{ $inWish ? '#01684B' : 'none' }}" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </button>
            <button type="button" class="gf-action-sq" onclick="GetgozoApp.addToCart({{ $product->id }}, 1, event)" title="Add to Bag">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </button>
        </div>

        <a href="{{ url('/product/' . $product->id) }}" class="gf-vcard-imglink">
            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/image/placeholder.png') }}'">
        </a>
    </div>

    <div class="gf-vcard-body">
        <div class="gf-rating-line">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="1">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <span>{{ $ratingVal }}</span>
        </div>

        <a href="{{ url('/product/' . $product->id) }}" class="gf-vcard-title" title="{{ $product->name }}">
            {{ $product->name }}
        </a>

        @if(!empty($product->coupon_title) || !empty($product->coupon_discount_label) || !empty($product->coupon_code))
            <div style="background:#FEF3C7;border:1px dashed #F59E0B;color:#92400E;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:space-between;gap:6px;margin:4px 0 6px" title="{{ $product->coupon_title ?? 'Special Offer' }}">
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:72%">🏷️ {{ $product->coupon_title ?: 'Special Offer' }}</span>
                @if(!empty($product->coupon_discount_label))
                    <span style="font-size:10px;color:#047857;font-weight:800;background:#D1FAE5;border:1px solid #A7F3D0;padding:1px 6px;border-radius:4px;white-space:nowrap">{{ $product->coupon_discount_label }}</span>
                @endif
            </div>
        @endif

        <div class="gf-vcard-footer">
            <span class="gf-unit-text">{{ $unitText }}</span>
            <div class="gf-price-wrap" style="display:flex;align-items:baseline;gap:6px;flex-wrap:wrap">
                @if($originalPrice > $displayPrice)
                    <span class="gf-orig-price" style="color:#DC2626;text-decoration:line-through;font-size:12px;font-weight:600">₹ {{ number_format($originalPrice, 2) }}</span>
                @endif
                <span class="gf-cur-price">₹ {{ number_format($displayPrice, 2) }}</span>
            </div>
        </div>
    </div>
</div>
