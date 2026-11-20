@extends('admin.layouts.master')

@section('title', 'Add New Product')

@push('styles')
<style>
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-size: 13px; font-weight: 700; color: #334155; }
    .form-group input, .form-group select, .form-group textarea {
        padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 9px; font-size: 13.5px;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    .checkbox-pill {
        display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; padding: 10px 14px; background: #F8FAFC; border: 1px solid var(--border); border-radius: 8px; cursor: pointer;
    }
    .img-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(118px, 1fr));
        gap: 14px;
    }
    .img-thumb-card {
        position: relative;
        background: #FFFFFF;
        border: 1.5px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.05);
        transition: transform 0.16s ease, box-shadow 0.16s ease, opacity 0.2s ease;
        display: flex;
        flex-direction: column;
    }
    .img-thumb-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.09);
        border-color: #94A3B8;
    }
    .img-thumb-box {
        width: 100%;
        height: 108px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #FFFFFF;
        padding: 8px;
    }
    .img-thumb-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
    }
    .img-corner-cross-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #EF4444;
        color: #FFFFFF;
        border: 2px solid #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.28);
        z-index: 6;
        transition: transform 0.15s ease, background 0.15s ease;
        line-height: 1;
        padding: 0;
    }
    .img-corner-cross-btn:hover {
        background: #DC2626;
        transform: scale(1.16);
    }
    .img-thumb-footer {
        padding: 6px 8px;
        background: #F8FAFC;
        border-top: 1px solid #E2E8F0;
        font-size: 11px;
        text-align: center;
        font-weight: 700;
    }
</style>
@endpush

@section('content')

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">✨ Add New Product</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Fill in product specifications, prices, stock, and upload images</p>
            </div>
            <a href="{{ route('admin.products') }}" class="btn-secondary">← Back to Product List</a>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Basic Details -->
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin-bottom:14px">1. Basic Information</h3>
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1">
                    <label for="name">Product Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g. TOUCHSTAINS Fabric Stain & Rust Remover 500ml" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select name="category_id" id="category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="brand_id">Brand</label>
                    <select name="brand_id" id="brand_id">
                        <option value="">Select Brand (Optional)</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="sku">SKU Code (Auto-generated if empty)</label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}" placeholder="e.g. TS-500ML-ORG">
                </div>

                <div class="form-group">
                    <label for="barcode">Barcode</label>
                    <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}" placeholder="e.g. 890123456789">
                </div>
            </div>

            <!-- Quantity / Volume & Pricing -->
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin:24px 0 14px">2. Product Quantity (ml / Litre) &amp; Pricing</h3>

            <!-- Quick Quantity Preset Chips -->
            <div style="margin-bottom:16px;padding:12px 14px;background:#F8FAFC;border:1px solid var(--border);border-radius:10px">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:8px">
                    <span style="font-size:12px;font-weight:700;color:#475569">⚡ Quick Quantity Select (or enter custom below):</span>
                    <span id="qtyPreviewBadge" style="font-size:12px;font-weight:800;color:#01684B;background:#ECFDF5;border:1px solid #A7F3D0;padding:3px 10px;border-radius:99px">
                        Storefront Display: {{ old('capacity', 100) }} {{ old('unit', 'ml') }}
                    </span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:7px">
                    @foreach([[50,'ml'], [100,'ml'], [200,'ml'], [250,'ml'], [500,'ml'], [1,'Litre'], [2,'Litre'], [5,'Litre'], [10,'Litre']] as $preset)
                        <button type="button" onclick="applyQtyPreset({{ $preset[0] }}, '{{ $preset[1] }}')" style="padding:5px 12px;border-radius:99px;border:1px solid #CBD5E1;background:#fff;color:#0F172A;font-size:12px;font-weight:700;cursor:pointer">
                            {{ $preset[0] }} {{ $preset[1] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="capacity">Quantity / Volume *</label>
                    <input type="number" step="0.01" min="0.01" name="capacity" id="capacity" value="{{ old('capacity', 100) }}" placeholder="e.g. 50, 100, 1, 5, 10" oninput="updateQtyPreview()" required>
                </div>

                <div class="form-group">
                    <label for="unit">Quantity Unit (ml / Litre) *</label>
                    <select name="unit" id="unit" onchange="updateQtyPreview()" required>
                        @foreach(['ml', 'Litre', 'gm', 'kg', 'Bottle', 'Pack', 'Kit', 'pc'] as $uOpt)
                            <option value="{{ $uOpt }}" {{ old('unit', 'ml') === $uOpt ? 'selected' : '' }}>{{ $uOpt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="mrp">Original Price / MRP (₹)</label>
                    <input type="number" step="0.01" name="mrp" id="mrp" value="{{ old('mrp') }}" placeholder="e.g. 399 (Shown in red strikethrough)">
                </div>

                <div class="form-group">
                    <label for="price">Selling Price (₹) *</label>
                    <input type="number" step="0.01" name="price" id="price" value="{{ old('price', 299) }}" placeholder="e.g. 299 (Shown in green)" required>
                </div>

                <div class="form-group">
                    <label for="total_stock">Initial Stock Quantity *</label>
                    <input type="number" name="total_stock" id="total_stock" value="{{ old('total_stock', 50) }}" required>
                </div>

                <div class="form-group">
                    <label for="bulk_min_qty">Bulk Pricing Min Qty</label>
                    <input type="number" name="bulk_min_qty" id="bulk_min_qty" value="{{ old('bulk_min_qty', 5) }}">
                </div>

                <div class="form-group">
                    <label for="bulk_price">Bulk Special Price (₹)</label>
                    <input type="number" step="0.01" name="bulk_price" id="bulk_price" value="{{ old('bulk_price') }}" placeholder="e.g. 249">
                </div>
            </div>

            <!-- Media & Description -->
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin:24px 0 14px">3. Product Media &amp; Description</h3>
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1">
                    <label for="images" style="font-weight:700;font-size:13px">🖼️ Product Images (Multiple allowed)</label>
                    <div style="border:1.5px dashed #CBD5E1;border-radius:10px;padding:12px 14px;background:#fff">
                        <input type="file" name="images[]" id="images" multiple accept="image/*" style="width:100%;font-size:13px">
                        <small style="color:var(--text-muted);font-size:11.5px;display:block;margin-top:6px">
                            Select 1 or more images (PNG, JPG, WebP up to 5MB each). Click the red <strong style="color:#EF4444">✕</strong> corner button on any preview to remove before saving.
                        </small>
                        <div id="createImagesPreviewGrid" class="img-gallery-grid" style="margin-top:12px;display:none"></div>
                    </div>
                </div>

                <div class="form-group" style="grid-column: 1 / -1">
                    <label for="description">Full Description</label>
                    <textarea name="description" id="description" rows="4" placeholder="Detailed product benefits, usage instructions, fabric safety guide...">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Badges & Visibility -->
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin:24px 0 14px">4. Badges & Visibility</h3>
            <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:30px">
                <label class="checkbox-pill">
                    <input type="checkbox" name="is_featured" value="1" checked style="accent-color:var(--primary);width:18px;height:18px">
                    ⭐ Featured Product
                </label>
                <label class="checkbox-pill">
                    <input type="checkbox" name="is_best_seller" value="1" checked style="accent-color:var(--primary);width:18px;height:18px">
                    🔥 Best Seller Label
                </label>
                <label class="checkbox-pill">
                    <input type="checkbox" name="is_new_arrival" value="1" style="accent-color:var(--primary);width:18px;height:18px">
                    🆕 New Arrival
                </label>
                <label class="checkbox-pill">
                    <input type="checkbox" name="status" value="1" checked style="accent-color:var(--primary);width:18px;height:18px">
                    ✅ Active & Visible in Store
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px">
                <a href="{{ route('admin.products') }}" class="btn-secondary" style="padding:11px 22px">Cancel</a>
                <button type="submit" class="btn-theme" style="padding:11px 28px">💾 Save Product</button>
            </div>
        </form>
    </div>

@push('scripts')
<script>
    function updateQtyPreview() {
        const cap = document.getElementById('capacity')?.value || '';
        const unit = document.getElementById('unit')?.value || 'ml';
        const badge = document.getElementById('qtyPreviewBadge');
        if (badge) badge.textContent = 'Storefront Display: ' + (cap || '1') + ' ' + unit;
    }
    function applyQtyPreset(val, unit) {
        const capEl = document.getElementById('capacity');
        const unitEl = document.getElementById('unit');
        if (capEl) capEl.value = val;
        if (unitEl) unitEl.value = unit;
        updateQtyPreview();
    }

    let createSelectedFiles = [];
    const createImgInput = document.getElementById('images');
    const createPreviewGrid = document.getElementById('createImagesPreviewGrid');

    function syncCreateFilesInput() {
        if (!createImgInput || typeof DataTransfer === 'undefined') return;
        const dt = new DataTransfer();
        createSelectedFiles.forEach(f => dt.items.add(f));
        createImgInput.files = dt.files;
    }

    function renderCreateImagesPreview() {
        if (!createPreviewGrid) return;
        createPreviewGrid.innerHTML = '';
        if (createSelectedFiles.length === 0) {
            createPreviewGrid.style.display = 'none';
            return;
        }
        createPreviewGrid.style.display = 'grid';
        createSelectedFiles.forEach((file, idx) => {
            const card = document.createElement('div');
            card.className = 'img-thumb-card';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'img-corner-cross-btn';
            btn.title = 'Remove this image';
            btn.textContent = '✕';
            btn.onclick = function () {
                createSelectedFiles.splice(idx, 1);
                syncCreateFilesInput();
                renderCreateImagesPreview();
            };

            const box = document.createElement('div');
            box.className = 'img-thumb-box';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;
            box.appendChild(img);

            const footer = document.createElement('div');
            footer.className = 'img-thumb-footer';
            if (idx === 0) {
                footer.innerHTML = '<span style="color:#166534;background:#DCFCE7;padding:2px 6px;border-radius:5px">★ Main Thumbnail</span>';
            } else {
                footer.innerHTML = '<span style="color:#64748B">Image #' + (idx + 1) + '</span>';
            }

            card.appendChild(btn);
            card.appendChild(box);
            card.appendChild(footer);
            createPreviewGrid.appendChild(card);
        });
    }

    createImgInput?.addEventListener('change', function (e) {
        const added = Array.from(e.target.files || []);
        if (added.length > 0) {
            createSelectedFiles = createSelectedFiles.concat(added);
            syncCreateFilesInput();
            renderCreateImagesPreview();
        }
    });
</script>
@endpush
@endsection
