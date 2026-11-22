@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span>Shop All Products</span>
    </div>

    <div class="page-layout-sidebar">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <div class="filter-group">
                <h4>Search Filter</h4>
                <input type="text" id="shop-search-input" class="form-control" value="{{ $searchQuery }}" placeholder="Type product name..." oninput="GetgozoApp.applyShopFilters()">
            </div>

            <div class="filter-group">
                <h4>Max Price: <span id="shop-price-val" style="color:var(--primary)">₹1500</span></h4>
                <input type="range" id="shop-price-range" class="price-range-slider" min="10" max="1500" step="10" value="1500" oninput="GetgozoApp.applyShopFilters()">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-top:4px">
                    <span>₹10</span>
                    <span>₹1500</span>
                </div>
            </div>

            <div class="filter-group">
                <h4>Availability</h4>
                <label class="filter-check-item">
                    <span><input type="checkbox" id="filter-in-stock" onchange="GetgozoApp.applyShopFilters()"> In Stock Only</span>
                </label>
            </div>

            <div class="filter-group">
                <h4>Categories ({{ $parentCategories->count() }})</h4>
                <div id="shop-cat-filters" style="max-height:380px;overflow-y:auto;padding-right:4px">
                    <label class="filter-check-item">
                        <span><input type="radio" name="shop_cat" value="all" {{ !$filterCat ? 'checked' : '' }} onchange="GetgozoApp.applyShopFilters()"> <strong>All Categories</strong></span>
                        <span class="count">{{ $allProducts->count() }}</span>
                    </label>
                    @foreach($parentCategories as $cat)
                        <label class="filter-check-item">
                            <span><input type="radio" name="shop_cat" value="{{ $cat->id }}" {{ (string)$filterCat === (string)$cat->id ? 'checked' : '' }} onchange="GetgozoApp.applyShopFilters()"> {{ $cat->name }}</span>
                            <span class="count">{{ $cat->products_count ?? 0 }}</span>
                        </label>
                        @foreach($cat->children as $sub)
                            <label class="filter-check-item" style="padding-left:18px;font-size:12px">
                                <span><input type="radio" name="shop_cat" value="{{ $sub->id }}" {{ (string)$filterSub === (string)$sub->id ? 'checked' : '' }} onchange="GetgozoApp.applyShopFilters()"> ↳ {{ $sub->name }}</span>
                                <span class="count">{{ $sub->products_count ?? 0 }}</span>
                            </label>
                        @endforeach
                    @endforeach
                </div>
            </div>

            <a href="{{ url('/shop') }}" class="btn-outline" style="width:100%;justify-content:center">Reset All Filters</a>
        </aside>

        <!-- Main Shop Catalog -->
        <div>
            <div class="shop-toolbar">
                <div>
                    <h1 style="font-size:18px;font-weight:700" id="shop-active-heading">
                        @if($filterType === 'daily_needs') 🔥 Daily Needs &amp; Flash Deals
                        @elseif($filterType === 'featured') ⭐ Featured Items
                        @elseif($filterType === 'most_reviewed') 🏆 Most Popular Items
                        @elseif($searchQuery) Search Results for "{{ $searchQuery }}"
                        @else All Products
                        @endif
                    </h1>
                    <p style="font-size:12px;color:var(--text-secondary)" id="shop-result-count">Showing {{ $filteredProducts->count() }} products</p>
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <select id="shop-sort-select" class="sort-select" onchange="GetgozoApp.applyShopFilters()">
                        <option value="default">Sort by: Newest First (Default)</option>
                        <option value="popularity">Sort by: Most Popular</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="name_asc">Name: A to Z</option>
                    </select>
                    <div class="view-mode-btns">
                        <button type="button" id="view-grid-btn" class="view-mode-btn active" onclick="GetgozoApp.setShopView('grid')" title="Grid View">⊞</button>
                        <button type="button" id="view-list-btn" class="view-mode-btn" onclick="GetgozoApp.setShopView('list')" title="List View">☰</button>
                    </div>
                </div>
            </div>

            <div class="product-grid" id="shop-products-grid">
                @forelse($filteredProducts as $product)
                    @include('partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
                @empty
                    <div class="empty-state" style="grid-column:1/-1">
                        <img src="{{ asset('assets/image/empty_box.png') }}" alt="No products">
                        <h3>No matching products found</h3>
                        <p>Try clearing your search or selecting another category.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</main>
@endsection

