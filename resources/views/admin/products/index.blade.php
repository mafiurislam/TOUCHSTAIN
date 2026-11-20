@extends('admin.layouts.master')

@section('title', 'Product Management')

@section('content')

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">📦 Product Management</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Manage catalog, pricing, variants, and stock availability</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <a href="{{ route('admin.products.export') }}" class="btn-secondary">📥 Export CSV</a>
                <a href="{{ route('admin.price-sheet') }}" class="btn-secondary" style="border-color:#10B981;color:#047857">📋 Bulk Price Sheet</a>
                <a href="{{ route('admin.products.create') }}" class="btn-theme">+ Add New Product</a>
            </div>
        </div>

        <!-- Filter Ribbon -->
        <form action="{{ route('admin.products') }}" method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;background:#F8FAFC;padding:14px;border-radius:12px;border:1px solid var(--border)">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, SKU or barcode..." class="search-input" style="flex:1.5;min-width:200px">
            
            <select name="category_id" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="stock_status" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
                <option value="">All Stock Status</option>
                <option value="in" {{ request('stock_status') === 'in' ? 'selected' : '' }}>In Stock (>10)</option>
                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock (1-10)</option>
                <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
            </select>

            <button type="submit" class="btn-theme" style="padding:9px 18px">Filter</button>
            <a href="{{ route('admin.products') }}" class="btn-secondary">Reset</a>
        </form>

        <!-- Product Table -->
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Product Details</th>
                        <th>SKU / Barcode</th>
                        <th>Category</th>
                        <th>Original Price</th>
                        <th>Selling Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                        <tr>
                            <td style="color:var(--text-muted);font-weight:700">{{ $prod->id }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px">
                                    <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" style="width:46px;height:46px;border-radius:8px;object-fit:contain;background:#F8FAFC;border:1px solid var(--border);padding:2px" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                                    <div>
                                        <div style="font-weight:700;font-size:13.5px;color:var(--text-dark)">{{ $prod->name }}</div>
                                        <div style="font-size:11.5px;color:#01684B;font-weight:700">Quantity: {{ $prod->unit_label }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-family:monospace;font-weight:700;font-size:12px">{{ $prod->sku ?: 'TS-00' . $prod->id }}</div>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $prod->barcode }}</div>
                            </td>
                            <td>
                                <span style="font-weight:600;font-size:12.5px">{{ $prod->category->name ?? 'General' }}</span>
                            </td>
                            <td style="color:#DC2626;text-decoration:line-through;font-weight:600;font-size:13px">
                                @if(!empty($prod->mrp) && (float)$prod->mrp > 0)
                                    ₹{{ number_format((float)$prod->mrp, 2) }}
                                @else
                                    <span style="text-decoration:none;color:#94A3B8">—</span>
                                @endif
                            </td>
                            <td style="font-weight:800;color:#01684B;font-size:14px">₹{{ number_format((float)$prod->price, 2) }}</td>
                            <td>
                                @if($prod->total_stock <= 0)
                                    <span class="status-badge status-danger">Out of Stock</span>
                                @elseif($prod->total_stock <= 10)
                                    <span class="status-badge status-warning">Low: {{ $prod->total_stock }}</span>
                                @else
                                    <span class="status-badge status-success">{{ $prod->total_stock }} in stock</span>
                                @endif
                            </td>
                            <td>
                                @if($prod->status)
                                    <span class="status-badge status-success">Active</span>
                                @else
                                    <span class="status-badge status-danger">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:right">
                                <div style="display:inline-flex;gap:6px">
                                    <a href="{{ route('admin.products.edit', $prod->id) }}" class="btn-secondary" style="padding:5px 10px;font-size:11.5px">Edit</a>
                                    <form action="{{ route('admin.products.delete', $prod->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete product {{ addslashes($prod->name) }}?')" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:5px 10px;font-size:11.5px;color:#DC2626;border-color:#FCA5A5">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:36px;color:#94A3B8">No products found matching filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px">
            {{ $products->links() }}
        </div>
    </div>

@endsection
