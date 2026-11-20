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
        : '0.0';
@endphp
<div class="gf-hcard" data-product-id="{{ $product->id }}">
    <!-- Left Image Box (114x114) with Bottom-Right Cart Button -->
    <div class="gf-hcard-imgbox">
        <a href="{{ url('/product/' . $product->id) }}" class="gf-hcard-imglink">
            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/image/placeholder.png') }}'">
        </a>
        <button type="button" class="gf-action-sq gf-hcard-cartbtn" onclick="GetgozoApp.addToCart({{ $product->id }}, 1, event)" title="Add to Bag">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#01684B" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
        </button>
    </div>

    <!-- Right Info Column -->
    <div class="gf-hcard-info">
        <button type="button" class="gf-action-sq gf-hcard-wishbtn {{ $inWish ? 'active' : '' }}" data-wish-btn="{{ $product->id }}" onclick="GetgozoApp.toggleWishlist({{ $product->id }}, event)" title="Add to Favourites">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="{{ $inWish ? '#01684B' : 'none' }}" stroke="#01684B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
        </button>

        <div class="gf-rating-line">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="#FFB800" stroke="#FFB800" stroke-width="1">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <span>{{ $ratingVal }}</span>
        </div>

        <a href="{{ url('/product/' . $product->id) }}" class="gf-hcard-title" title="{{ $product->name }}">
            {{ $product->name }}
        </a>

        <div class="gf-unit-text" style="margin:4px 0 6px">{{ $unitText }}</div>

        <div class="gf-price-wrap" style="display:flex;align-items:baseline;gap:6px;flex-wrap:wrap">
            @if($originalPrice > $displayPrice)
                <span class="gf-orig-price" style="color:#DC2626;text-decoration:line-through;font-size:12px;font-weight:600">₹ {{ number_format($originalPrice, 2) }}</span>
            @endif
            <span class="gf-cur-price">₹ {{ number_format($displayPrice, 2) }}</span>
        </div>
    </div>
</div>
