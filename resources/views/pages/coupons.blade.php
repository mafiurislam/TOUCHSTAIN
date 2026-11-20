@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="coupon">Available Coupons</span>
    </div>

    <div class="account-layout">
        @include('partials.account-sidebar', ['activeTab' => 'coupons'])

        <div>
            <h1 style="font-size:20px;font-weight:700;margin-bottom:6px" data-i18n="coupon">Available Coupons &amp; Promo Codes</h1>
            <p style="font-size:13px;color:var(--text-secondary);margin-bottom:20px">Click any coupon code below to copy and apply at your Shopping Bag or Checkout!</p>

            <div class="coupon-grid" id="coupons-page-grid">
                @foreach($coupons as $cp)
                    <div class="coupon-ticket">
                        <div class="coupon-left">
                            <span class="amt">
                                {{ $cp->discount_type === 'percent' ? round($cp->discount) . '%' : '₹' . round($cp->discount) }}
                            </span>
                            <span class="off">OFF</span>
                        </div>
                        <div class="coupon-right">
                            <h4>{{ $cp->title }}</h4>
                            <p>Min. Purchase: ₹{{ number_format($cp->min_purchase, 0) }} • Valid till {{ $cp->expire_date }}</p>
                            <button type="button" class="coupon-code-pill" onclick="navigator.clipboard?.writeText('{{ $cp->code }}'); GetgozoApp.showToast('Coupon {{ $cp->code }} copied!', 'success')">
                                📋 {{ $cp->code }} (Click to Copy)
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</main>
@endsection

