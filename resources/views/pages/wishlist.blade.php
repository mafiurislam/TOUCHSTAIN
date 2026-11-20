@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="favourite_list">My Favourite List</span>
    </div>

    <div class="account-layout">
        @include('partials.account-sidebar', ['activeTab' => 'wishlist'])

        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
                <h1 style="font-size:20px;font-weight:700" data-i18n="favourite_list">My Favourite Products</h1>
                <a href="{{ url('/shop') }}" class="btn-outline" style="padding:8px 14px;font-size:12px">+ Browse More</a>
            </div>
            <div class="product-grid" id="wishlist-page-grid">
                @if($wishlistItems->count() > 0)
                    @foreach($wishlistItems as $product)
                        @include('partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</main>
@endsection

