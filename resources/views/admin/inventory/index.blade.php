@extends('admin.layouts.master')

@section('title', 'Inventory & Stock Management')

@section('content')

    <!-- Stock KPI Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
        <div class="kpi-white-card">
            <div class="kpi-title">Total Units In Stock</div>
            <div class="kpi-number" style="color:var(--primary);margin-top:4px">{{ number_format($stats['total_items']) }}</div>
            <div class="kpi-trend">Across {{ $stats['total_products'] }} Products</div>
        </div>
        <div class="kpi-white-card">
            <div class="kpi-title">Low Stock Alert (1-10)</div>
            <div class="kpi-number" style="color:#D97706;margin-top:4px">{{ $stats['low_stock'] }}</div>
            <div class="kpi-trend" style="background:#FEF3C7;color:#92400E">Reorder Required</div>
        </div>
        <div class="kpi-white-card">
            <div class="kpi-title">Out of Stock (0 units)</div>
            <div class="kpi-number" style="color:#DC2626;margin-top:4px">{{ $stats['out_of_stock'] }}</div>
            <div class="kpi-trend" style="background:#FEE2E2;color:#991B1B">Urgent Replenish</div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">🏢 Stock Inventory Management</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Track stock levels, record warehouse inward/outward adjustments</p>
            </div>
            <div style="display:flex;gap:10px">
                <a href="{{ route('admin.inventory', ['filter' => 'low']) }}" class="btn-secondary" style="border-color:#F59E0B;color:#D97706">⚠️ View Low Stock ({{ $stats['low_stock'] }})</a>
                <a href="{{ route('admin.inventory', ['filter' => 'out']) }}" class="btn-secondary" style="border-color:#EF4444;color:#DC2626">✕ Out of Stock ({{ $stats['out_of_stock'] }})</a>
            </div>
        </div>

        <!-- Filter Ribbon -->
        <form action="{{ route('admin.inventory') }}" method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;background:#F8FAFC;padding:14px;border-radius:12px;border:1px solid var(--border)">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product name or SKU..." class="search-input" style="flex:1.5;min-width:200px">
            <button type="submit" class="btn-theme" style="padding:9px 18px">Filter</button>
            <a href="{{ route('admin.inventory') }}" class="btn-secondary">Reset</a>
        </form>

        <!-- Stock Table -->
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Product Details</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Available Stock</th>
                        <th>Stock Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                        <tr>
                            <td style="color:var(--text-muted);font-weight:700">{{ $prod->id }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" style="width:38px;height:38px;border-radius:6px;object-fit:contain;background:#F8FAFC;border:1px solid var(--border);padding:2px">
                                    <span style="font-weight:700;font-size:13.5px">{{ $prod->name }}</span>
                                </div>
                            </td>
                            <td><code style="font-weight:700">{{ $prod->sku ?: 'TS-00' . $prod->id }}</code></td>
                            <td>{{ $prod->category->name ?? 'General' }}</td>
                            <td style="font-size:15px;font-weight:800">{{ $prod->total_stock }}</td>
                            <td>
                                @if($prod->total_stock <= 0)
                                    <span class="status-badge status-danger">Out of Stock</span>
                                @elseif($prod->total_stock <= 10)
                                    <span class="status-badge status-warning">Low ({{ $prod->total_stock }})</span>
                                @else
                                    <span class="status-badge status-success">In Stock</span>
                                @endif
                            </td>
                            <td style="text-align:right">
                                <button type="button" onclick="openAdjustModal({{ $prod->id }}, '{{ addslashes($prod->name) }}', {{ $prod->total_stock }})" class="btn-theme" style="padding:5px 12px;font-size:11.5px">
                                    ± Adjust Stock
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:36px;color:#94A3B8">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Recent Inventory Logs -->
    <div class="admin-card">
        <h2 class="admin-card-title" style="margin-bottom:14px">📋 Recent Inventory Adjustment History</h2>
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Balance After</th>
                        <th>Notes / Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                        <tr>
                            <td style="font-size:12px;color:var(--text-muted);white-space:nowrap">{{ $log->created_at ? $log->created_at->format('d M H:i') : 'N/A' }}</td>
                            <td style="font-weight:700">{{ $log->product->name ?? 'Product' }}</td>
                            <td>
                                <span class="status-badge {{ $log->type === 'stock_in' || $log->type === 'order_restore' ? 'status-success' : 'status-danger' }}">
                                    {{ strtoupper(str_replace('_', ' ', $log->type)) }}
                                </span>
                            </td>
                            <td style="font-weight:800">{{ $log->quantity }}</td>
                            <td style="font-weight:800;color:var(--primary)">{{ $log->current_stock }}</td>
                            <td style="font-size:12px;color:var(--text-muted)">{{ $log->notes ?: 'Manual Adjustment' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:20px;color:#94A3B8">No stock adjustment logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div id="adjustModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:9999;padding:20px">
        <div style="background:#fff;border-radius:14px;max-width:460px;width:100%;padding:26px;box-shadow:var(--shadow-lg)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #F1F5F9;padding-bottom:12px">
                <h3 style="font-size:16px;font-weight:800;color:var(--text-dark);margin:0">± Adjust Stock</h3>
                <button type="button" onclick="closeAdjustModal()" style="border:none;background:none;font-size:20px;cursor:pointer;color:#64748B">&times;</button>
            </div>

            <form id="adjustForm" method="POST">
                @csrf
                <div style="margin-bottom:12px">
                    <span style="font-size:11.5px;color:var(--text-muted);display:block">Product:</span>
                    <strong id="modalProdName" style="font-size:14px;color:var(--text-dark)"></strong>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Current Stock: <strong id="modalCurrStock"></strong> units</div>
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Adjustment Type *</label>
                    <select name="type" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                        <option value="stock_in">➕ Stock In (Receive New Stock)</option>
                        <option value="stock_out">➖ Stock Out (Damage / Loss / Defect)</option>
                        <option value="manual_adjustment">📝 Set Exact Count</option>
                    </select>
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Quantity to Adjust *</label>
                    <input type="number" name="quantity" min="1" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:18px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Reason / Remarks</label>
                    <input type="text" name="notes" placeholder="e.g. Warehouse Batch Restock" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px">
                    <button type="button" onclick="closeAdjustModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-theme">Update Inventory</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openAdjustModal(id, name, current) {
        document.getElementById('modalProdName').innerText = name;
        document.getElementById('modalCurrStock').innerText = current;
        document.getElementById('adjustForm').action = "{{ url('/admin/inventory') }}/" + id + "/adjust";
        document.getElementById('adjustModal').style.display = 'flex';
    }
    function closeAdjustModal() {
        document.getElementById('adjustModal').style.display = 'none';
    }
</script>
@endpush
