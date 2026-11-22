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
                        <th style="text-align:center">🏷️ Product Coupon</th>
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
                            <td style="text-align:center;white-space:nowrap">
                                @if(!empty($prod->coupon_code))
                                    <div style="display:inline-flex;align-items:center;gap:6px">
                                        <span style="background:#FEF3C7;color:#92400E;border:1px dashed #F59E0B;padding:4px 9px;border-radius:6px;font-weight:800;font-size:12px;display:inline-flex;align-items:center;gap:4px">
                                            🎟️ {{ $prod->coupon_code }}
                                        </span>
                                        <button type="button" onclick="openAssignCouponModal({{ $prod->id }}, '{{ addslashes($prod->name) }}', {{ $prod->coupon_id ?: 0 }})" class="btn-secondary" style="padding:4px 8px;font-size:11px;color:#01684B;border-color:#A7F3D0;background:#F0FDF4" title="Change or remove coupon">
                                            ✏️
                                        </button>
                                    </div>
                                @else
                                    <button type="button" onclick="openAssignCouponModal({{ $prod->id }}, '{{ addslashes($prod->name) }}', 0)" class="btn-secondary" style="padding:4px 10px;font-size:11.5px;font-weight:700;color:#01684B;border-color:#A7F3D0;background:#F0FDF4;display:inline-flex;align-items:center;gap:4px">
                                        ➕ Add Coupon
                                    </button>
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
                            <td colspan="10" style="text-align:center;padding:36px;color:#94A3B8">No products found matching filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Assign Coupon Modal -->
    <div id="assignCouponModal" class="modal-overlay" onclick="if(event.target===this) closeAssignCouponModal()" style="position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:9999;padding:16px">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;box-shadow:0 20px 40px rgba(0,0,0,0.2);overflow:hidden;animation:modalFadeIn 0.2s ease-out">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)">
                <h3 style="margin:0;font-size:16px;font-weight:800;display:flex;align-items:center;gap:8px">
                    🏷️ Set Coupon Offer on Product
                </h3>
                <button type="button" onclick="closeAssignCouponModal()" style="border:none;background:none;font-size:20px;line-height:1;cursor:pointer;color:var(--text-muted)">&times;</button>
            </div>
            <form id="assignCouponForm" method="POST" style="padding:20px">
                @csrf
                <div style="margin-bottom:14px;background:#F8FAFC;border:1px solid var(--border);border-radius:8px;padding:10px 12px">
                    <span style="font-size:12px;color:var(--text-muted);display:block;font-weight:600">Selected Product:</span>
                    <strong id="assignCouponProdName" style="font-size:14px;color:var(--text-dark)"></strong>
                </div>

                <div style="margin-bottom:18px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:8px">Select Coupon to Display on this Product:</label>
                    <select name="coupon_id" id="assignCouponSelect" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13.5px;background:#fff">
                        <option value="">-- No Coupon (Remove Offer) --</option>
                        @foreach($activeCoupons as $coup)
                            <option value="{{ $coup->id }}">
                                🎟️ {{ $coup->code }} — {{ $coup->title }} ({{ $coup->discount_type === 'percent' ? $coup->discount . '% OFF' : '₹' . $coup->discount . ' OFF' }})
                            </option>
                        @endforeach
                    </select>
                    <small style="display:block;color:var(--text-muted);font-size:11.5px;margin-top:6px;line-height:1.4">
                        💡 The coupon code and discount will be prominently highlighted with an eye-catching badge on this product across the Homepage, Shop page, and Product Details page.
                    </small>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end">
                    <button type="button" onclick="closeAssignCouponModal()" class="btn-secondary" style="padding:8px 16px">Cancel</button>
                    <button type="submit" class="btn-theme" style="padding:8px 20px">💾 Set Coupon on Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openAssignCouponModal(productId, productName, currentCouponId) {
        const form = document.getElementById('assignCouponForm');
        form.action = `/admin/products/${productId}/assign-coupon`;
        document.getElementById('assignCouponProdName').textContent = productName;
        document.getElementById('assignCouponSelect').value = currentCouponId || '';
        document.getElementById('assignCouponModal').style.display = 'flex';
    }
    function closeAssignCouponModal() {
        document.getElementById('assignCouponModal').style.display = 'none';
    }
    </script>

@endsection
