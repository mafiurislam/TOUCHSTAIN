@extends('admin.layouts.master')

@section('title', 'Price Sheet Management')

@section('content')

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">📋 Professional Price Sheet Management</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Simultaneously update Product Prices and Bulk Pricing for your catalog</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <button type="button" class="btn-secondary" onclick="openImportModal()">📤 Import CSV</button>
                <a href="{{ route('admin.price-sheet.export') }}" class="btn-secondary">📥 Export CSV</a>
                <button type="submit" form="bulkPriceForm" class="btn-theme" style="background:#059669">💾 Bulk Save All</button>
            </div>
        </div>

        <!-- Filter Ribbon -->
        <form action="{{ route('admin.price-sheet') }}" method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;background:#F8FAFC;padding:14px;border-radius:12px;border:1px solid var(--border)">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product name or SKU..." class="search-input" style="flex:1.5;min-width:200px">
            
            <select name="category_id" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-theme" style="padding:9px 18px">Filter</button>
            <a href="{{ route('admin.price-sheet') }}" class="btn-secondary">Reset</a>
        </form>

        <!-- Price Sheet Table -->
        <form action="{{ route('admin.price-sheet.bulk-save') }}" method="POST" id="bulkPriceForm">
            @csrf

            <div style="overflow-x:auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th style="width:140px">Original Price (₹)</th>
                            <th style="width:140px">Selling Price (₹)</th>
                            <th style="width:110px">Bulk Min Qty</th>
                            <th style="width:120px">Bulk Price (₹)</th>
                            <th style="width:120px">Special Price (₹)</th>
                            <th style="text-align:right;width:100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $prod)
                            <tr id="row-{{ $prod->id }}">
                                <td style="color:var(--text-muted);font-weight:700">{{ $prod->id }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" style="width:36px;height:36px;border-radius:6px;object-fit:contain;background:#F8FAFC;border:1px solid var(--border);padding:2px">
                                        <span style="font-weight:700;font-size:13px">{{ \Illuminate\Support\Str::limit($prod->name, 26) }}</span>
                                    </div>
                                </td>
                                <td><code style="font-weight:700;font-size:11.5px">{{ $prod->sku ?: 'TS-00' . $prod->id }}</code></td>
                                <td>
                                    <input type="number" step="0.01" name="prices[{{ $prod->id }}][mrp]" id="mrp-{{ $prod->id }}" value="{{ $prod->mrp }}" placeholder="₹ Original" style="width:100%;padding:6px 10px;border:1.5px solid #FCA5A5;border-radius:6px;font-size:12.5px;font-weight:700;color:#DC2626;background:#FEF2F2">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="prices[{{ $prod->id }}][price]" id="price-{{ $prod->id }}" value="{{ $prod->price }}" style="width:100%;padding:6px 10px;border:1.5px solid #01684B;border-radius:6px;font-size:12.5px;font-weight:800;color:#01684B;background:#ECFDF5">
                                </td>
                                <td>
                                    <input type="number" name="prices[{{ $prod->id }}][bulk_min_qty]" id="bulk_qty-{{ $prod->id }}" value="{{ $prod->bulk_min_qty ?: 5 }}" style="width:100%;padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:12.5px">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="prices[{{ $prod->id }}][bulk_price]" id="bulk_price-{{ $prod->id }}" value="{{ $prod->bulk_price }}" placeholder="₹ Bulk" style="width:100%;padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:12.5px">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="prices[{{ $prod->id }}][special_price]" id="special_price-{{ $prod->id }}" value="{{ $prod->special_price }}" placeholder="₹ Special" style="width:100%;padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:12.5px">
                                </td>
                                <td style="text-align:right">
                                    <button type="button" onclick="quickSaveRow({{ $prod->id }})" id="btn-save-{{ $prod->id }}" class="btn-theme" style="padding:6px 12px;font-size:11.5px">
                                        ⚡ Save
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center;padding:36px;color:#94A3B8">No products found in price sheet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
                <div>{{ $products->links() }}</div>
                <button type="submit" class="btn-theme" style="background:#059669;padding:11px 26px">💾 Bulk Save All Modified Rows</button>
            </div>
        </form>
    </div>

    <!-- Modal for CSV Import -->
    <div id="importModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:9999;padding:20px">
        <div style="background:#fff;border-radius:14px;max-width:500px;width:100%;padding:26px;box-shadow:var(--shadow-lg)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #F1F5F9;padding-bottom:12px">
                <h3 style="font-size:16px;font-weight:800;color:var(--text-dark);margin:0">📤 Import Price Sheet CSV</h3>
                <button type="button" onclick="closeImportModal()" style="border:none;background:none;font-size:20px;cursor:pointer;color:#64748B">&times;</button>
            </div>

            <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:16px;line-height:1.5">
                Download the current price sheet using <strong>Export CSV</strong>, modify prices in Microsoft Excel or Google Sheets, then upload the file here to update hundreds of prices instantly.
            </p>

            <form action="{{ route('admin.price-sheet.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:20px">
                    <label style="font-size:13px;font-weight:700;display:block;margin-bottom:8px">Select Modified CSV File *</label>
                    <input type="file" name="csv_file" accept=".csv,text/csv" required style="width:100%;padding:10px;border:1.5px dashed var(--border);border-radius:8px">
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px">
                    <button type="button" onclick="closeImportModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-theme">Upload & Apply Prices</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function quickSaveRow(id) {
        const btn = document.getElementById('btn-save-' + id);
        btn.disabled = true;
        btn.innerText = '⏳';

        const payload = {
            mrp: document.getElementById('mrp-' + id).value,
            price: document.getElementById('price-' + id).value,
            bulk_min_qty: document.getElementById('bulk_qty-' + id).value,
            bulk_price: document.getElementById('bulk_price-' + id).value,
            special_price: document.getElementById('special_price-' + id).value,
        };

        fetch("{{ url('/admin/price-sheet') }}/" + id + "/quick-update", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerText = '✓ Saved';
            btn.style.background = '#10B981';
            setTimeout(() => {
                btn.innerText = '⚡ Save';
                btn.style.background = 'var(--primary)';
            }, 2500);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerText = '❌ Error';
            btn.style.background = '#EF4444';
            alert('Failed to update price: ' + err.message);
        });
    }

    function openImportModal() {
        document.getElementById('importModal').style.display = 'flex';
    }

    function closeImportModal() {
        document.getElementById('importModal').style.display = 'none';
    }
</script>
@endpush
