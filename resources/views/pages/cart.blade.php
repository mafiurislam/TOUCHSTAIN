@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="shopping_bag">Shopping Bag</span>
    </div>

    <div class="cart-page-grid" id="cart-page-container">
        <!-- Left: Cart Items -->
        <div class="cart-table-card">
            <div style="padding:18px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
                <h1 style="font-size:18px;font-weight:700" data-i18n="shopping_bag">Shopping Bag</h1>
                <button class="btn-outline" style="padding:6px 12px;font-size:12px;color:var(--danger);border-color:var(--danger)" onclick="GetgozoApp.clearCart()">Clear Bag</button>
            </div>
            <div class="free-delivery-bar-wrap" id="cart-free-delivery-msg">
                Checking free delivery eligibility...
            </div>
            <div id="cart-page-items-list" style="padding:0 20px"></div>
        </div>

        <!-- Right: Order Summary & Promo Code -->
        <div>
            <div class="summary-card">
                <h3 data-i18n="order_summary">Order Summary</h3>
                <div style="margin-bottom:16px">
                    <label style="font-size:12px;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px">Have a Coupon Code? Try <strong>GOZO20</strong> or <strong>FRESH50</strong></label>
                    <div style="display:flex;gap:8px">
                        <input type="text" id="cart-coupon-input" class="form-control" placeholder="Enter coupon code" style="text-transform:uppercase">
                        <button type="button" class="btn-primary" onclick="GetgozoApp.applyCouponCode(document.getElementById('cart-coupon-input').value)">Apply</button>
                    </div>
                </div>
                <div class="summary-row">
                    <span data-i18n="subtotal">Subtotal</span>
                    <span id="cart-summary-mrp">₹0</span>
                </div>
                <div class="summary-row discount" id="cart-coupon-row" style="display:none">
                    <span>Coupon Discount</span>
                    <span id="cart-summary-coupon">- ₹0</span>
                </div>
                <div class="summary-row">
                    <span data-i18n="delivery_fee">Delivery Fee (Free over ₹{{ $settings['free_delivery_over_amount'] ?? 69 }})</span>
                    <span id="cart-summary-delivery">₹0</span>
                </div>
                <div class="summary-row total">
                    <span data-i18n="total_amount">Total Payable</span>
                    <span class="amount" id="cart-summary-total">₹0</span>
                </div>
                <div style="margin-top:12px;padding:10px;background:#FEF3C7;border-radius:8px;font-size:12px;color:#B45309;font-weight:600;text-align:center" id="cart-loyalty-earn-msg">
                    🎁 You will earn 5% Loyalty Points on this order!
                </div>
                <a href="{{ url('/checkout') }}" class="btn-primary" style="width:100%;justify-content:center;margin-top:16px;padding:14px" data-i18n="proceed_to_checkout">
                    Proceed to Checkout →
                </a>
                <a href="{{ url('/shop') }}" class="btn-outline" style="width:100%;justify-content:center;margin-top:10px">
                    ← Continue Shopping
                </a>
            </div>
        </div>
    </div>
</main>
@endsection

