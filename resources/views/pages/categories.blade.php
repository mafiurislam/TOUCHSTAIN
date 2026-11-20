@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span id="cat-breadcrumb-name">{{ $selectedCategory->name ?? 'All Categories' }}</span>
    </div>

    <div class="categories-layout">
        <!-- Left Sidebar: 7 Parent Categories from SQLite -->
        <aside class="cat-sidebar-list" id="cat-page-sidebar">
            @foreach($parentCategories as $cat)
                <a href="{{ url('/categories?cat=' . $cat->id) }}" class="cat-side-item {{ $selectedCatId == $cat->id ? 'active' : '' }}">
                    <img src="{{ asset('storage/category/' . $cat->image) }}" alt="{{ $cat->name }}" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                    <div>
                        <div>{{ $cat->name }}</div>
                        <div style="font-size:11px;opacity:0.75">{{ $cat->products_count ?? 0 }} items</div>
                    </div>
                </a>
            @endforeach
        </aside>

        <!-- Right Content: Category Header, Subcategory Chips & Product Grid -->
        <div>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:18px 22px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                <div>
                    <h1 style="font-size:20px;font-weight:700" id="cat-page-title">{{ $selectedCategory->name ?? 'Category' }}</h1>
                    <p style="font-size:13px;color:var(--text-secondary)" id="cat-page-count">{{ $categoryProducts->count() }} products found</p>
                </div>
                <a href="{{ url('/shop?cat=' . $selectedCatId) }}" class="btn-outline" style="padding:8px 16px;font-size:12px">Filter &amp; Sort in Shop →</a>
            </div>

            <!-- Subcategory Chips from SQLite -->
            <div class="sub-cat-chips-bar" id="cat-page-subchips">
                <a href="{{ url('/categories?cat=' . $selectedCatId . '&sub=all') }}" class="sub-cat-chip {{ $selectedSubId === 'all' ? 'active' : '' }}">
                    All {{ $selectedCategory->name ?? '' }} ({{ $selectedCategory->products_count ?? 0 }})
                </a>
                @if($selectedCategory && $selectedCategory->children)
                    @foreach($selectedCategory->children as $sub)
                        <a href="{{ url('/categories?cat=' . $selectedCatId . '&sub=' . $sub->id) }}" class="sub-cat-chip {{ (string)$selectedSubId === (string)$sub->id ? 'active' : '' }}">
                            {{ $sub->name }} ({{ $sub->products_count ?? 0 }})
                        </a>
                    @endforeach
                @endif
            </div>

            <!-- Category Products Grid from SQLite -->
            <div class="product-grid" id="cat-page-products">
                @forelse($categoryProducts as $product)
                    @include('partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
                @empty
                    <div class="empty-state" style="grid-column:1/-1">
                        <img src="{{ asset('assets/image/empty_box.png') }}" alt="Empty">
                        <h3>No items in this subcategory</h3>
                        <p>Try selecting another category from the left sidebar.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</main>
@endsection

