@extends('admin.layouts.master')

@section('title', 'Edit Product #' . $product->id)

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
                <h1 class="admin-card-title">✏️ Edit Product #{{ $product->id }}</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Editing {{ $product->name }}</p>
            </div>
            <a href="{{ route('admin.products') }}" class="btn-secondary">← Back to Products</a>
        </div>

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Basic Details -->
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin-bottom:14px">1. Basic Information</h3>
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1">
                    <label for="name">Product Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select name="category_id" id="category_id" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="brand_id">Brand</label>
                    <select name="brand_id" id="brand_id">
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="sku">SKU Code</label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}">
                </div>

                <div class="form-group">
                    <label for="barcode">Barcode</label>
                    <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}">
                </div>
            </div>

            <!-- Quantity / Volume & Pricing -->
            @php
                $curCap = $product->capacity ?? 1;
                $curCapFmt = ($curCap == floor($curCap)) ? (int)$curCap : $curCap;
                $curUnit = $product->unit ?: 'ml';
                $unitOptions = ['ml', 'Litre', 'gm', 'kg', 'Bottle', 'Pack', 'Kit', 'pc'];
                if (!in_array($curUnit, $unitOptions)) {
                    $unitOptions[] = $curUnit;
                }
            @endphp
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin:24px 0 14px">2. Product Quantity (ml / Litre) &amp; Pricing</h3>

            <!-- Quick Quantity Preset Chips -->
            <div style="margin-bottom:16px;padding:12px 14px;background:#F8FAFC;border:1px solid var(--border);border-radius:10px">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:8px">
                    <span style="font-size:12px;font-weight:700;color:#475569">⚡ Quick Quantity Select (or enter custom below):</span>
                    <span id="qtyPreviewBadge" style="font-size:12px;font-weight:800;color:#01684B;background:#ECFDF5;border:1px solid #A7F3D0;padding:3px 10px;border-radius:99px">
                        Storefront Display: {{ old('capacity', $curCapFmt) }} {{ old('unit', $curUnit) }}
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
                    <input type="number" step="0.01" min="0.01" name="capacity" id="capacity" value="{{ old('capacity', $curCapFmt) }}" placeholder="e.g. 50, 100, 1, 5, 10" oninput="updateQtyPreview()" required>
                </div>

                <div class="form-group">
                    <label for="unit">Quantity Unit (ml / Litre) *</label>
                    <select name="unit" id="unit" onchange="updateQtyPreview()" required>
                        @foreach($unitOptions as $uOpt)
                            <option value="{{ $uOpt }}" {{ old('unit', $curUnit) === $uOpt ? 'selected' : '' }}>{{ $uOpt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="mrp">Original Price / MRP (₹)</label>
                    <input type="number" step="0.01" name="mrp" id="mrp" value="{{ old('mrp', $product->mrp) }}" placeholder="e.g. 399 (Shown in red strikethrough)">
                </div>

                <div class="form-group">
                    <label for="price">Selling Price (₹) *</label>
                    <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $product->price) }}" placeholder="e.g. 299 (Shown in green)" required>
                </div>

                <div class="form-group">
                    <label for="total_stock">Stock Quantity *</label>
                    <input type="number" name="total_stock" id="total_stock" value="{{ old('total_stock', $product->total_stock) }}" required>
                </div>

                <div class="form-group">
                    <label for="bulk_min_qty">Bulk Pricing Min Qty</label>
                    <input type="number" name="bulk_min_qty" id="bulk_min_qty" value="{{ old('bulk_min_qty', $product->bulk_min_qty ?? 5) }}">
                </div>

                <div class="form-group">
                    <label for="bulk_price">Bulk Special Price (₹)</label>
                    <input type="number" step="0.01" name="bulk_price" id="bulk_price" value="{{ old('bulk_price', $product->bulk_price) }}">
                </div>
            </div>

            <!-- Media & Description -->
            @php
                $galleryImages = $product->images_list;
            @endphp
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin:24px 0 14px">3. Product Images &amp; Description</h3>
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1">
                    <!-- Current Thumbnails / Full Gallery Grid -->
                    <div style="margin-bottom:16px;background:#F8FAFC;border:1.5px solid var(--border);border-radius:12px;padding:14px 16px">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px">
                            <div style="display:flex;align-items:center;gap:8px">
                                <span style="font-weight:800;font-size:13.5px;color:#0F172A">🖼️ Current Product Images</span>
                                <span id="currentImagesBadge" class="status-badge status-info">
                                    {{ count($galleryImages) }} {{ count($galleryImages) === 1 ? 'Image' : 'Images' }}
                                </span>
                            </div>
                            <span style="font-size:12px;color:#64748B">
                                Click the red <strong style="color:#EF4444">✕</strong> button on any corner to remove that image
                            </span>
                        </div>

                        <div id="productGalleryGrid" class="img-gallery-grid">
                            @forelse($galleryImages as $idx => $imgName)
                                <div class="img-thumb-card" id="existingThumb-{{ $idx }}" data-img-name="{{ $imgName }}">
                                    <input type="hidden" name="keep_images[]" value="{{ $imgName }}">

                                    <button
                                        type="button"
                                        class="img-corner-cross-btn"
                                        onclick="deleteProductThumbnail({{ $product->id }}, '{{ addslashes($imgName) }}', 'existingThumb-{{ $idx }}')"
                                        title="Remove this image"
                                        aria-label="Remove image"
                                    >
                                        ✕
                                    </button>

                                    <div class="img-thumb-box">
                                        <img src="{{ \App\Models\Product::getProductImageUrl($imgName) }}" alt="{{ $product->name }}" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                                    </div>

                                    <div class="img-thumb-footer">
                                        @if($idx === 0)
                                            <span class="thumb-role-label" style="color:#166534;background:#DCFCE7;padding:2px 6px;border-radius:5px">★ Main Thumbnail</span>
                                        @else
                                            <span class="thumb-role-label" style="color:#64748B">Image #{{ $idx + 1 }}</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div id="emptyGalleryNotice" style="grid-column:1 / -1;text-align:center;padding:22px;color:#94A3B8;font-size:12.5px;background:#fff;border-radius:10px;border:1.5px dashed var(--border)">
                                    No custom product images uploaded yet. Upload photos below to add to this product's gallery.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Add More Images with Instant Multi-Preview & Corner Remove -->
                    <label for="images" style="font-weight:700;font-size:13px">➕ Add More Images</label>
                    <div style="border:1.5px dashed #CBD5E1;border-radius:10px;padding:12px 14px;background:#fff">
                        <input type="file" name="images[]" id="images" multiple accept="image/*" style="width:100%;font-size:13px">
                        <small style="color:var(--text-muted);font-size:11.5px;display:block;margin-top:6px">
                            Select 1 or more images (PNG, JPG, WebP up to 5MB each). Newly selected photos will preview below before you save.
                        </small>
                        <div id="newImagesPreviewGrid" class="img-gallery-grid" style="margin-top:12px;display:none"></div>
                    </div>
                </div>

                <div class="form-group" style="grid-column: 1 / -1">
                    <label for="description">Full Description</label>
                    <textarea name="description" id="description" rows="4">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <!-- Badges & Visibility -->
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin:24px 0 14px">4. Badges & Visibility</h3>
            <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:30px">
                <label class="checkbox-pill">
                    <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }} style="accent-color:var(--primary);width:18px;height:18px">
                    ⭐ Featured Product
                </label>
                <label class="checkbox-pill">
                    <input type="checkbox" name="is_best_seller" value="1" {{ $product->is_best_seller ? 'checked' : '' }} style="accent-color:var(--primary);width:18px;height:18px">
                    🔥 Best Seller Label
                </label>
                <label class="checkbox-pill">
                    <input type="checkbox" name="is_new_arrival" value="1" {{ $product->is_new_arrival ? 'checked' : '' }} style="accent-color:var(--primary);width:18px;height:18px">
                    🆕 New Arrival
                </label>
                <label class="checkbox-pill">
                    <input type="checkbox" name="status" value="1" {{ $product->status ? 'checked' : '' }} style="accent-color:var(--primary);width:18px;height:18px">
                    ✅ Active & Visible in Store
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px">
                <a href="{{ route('admin.products') }}" class="btn-secondary" style="padding:11px 22px">Cancel</a>
                <button type="submit" class="btn-theme" style="padding:11px 28px">💾 Update Product</button>
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

    function refreshGalleryBadges() {
        const cards = document.querySelectorAll('#productGalleryGrid .img-thumb-card');
        const badge = document.getElementById('currentImagesBadge');
        if (badge) {
            badge.textContent = cards.length + (cards.length === 1 ? ' Image' : ' Images');
        }
        cards.forEach((card, index) => {
            const label = card.querySelector('.thumb-role-label');
            if (!label) return;
            if (index === 0) {
                label.textContent = '★ Main Thumbnail';
                label.style.color = '#166534';
                label.style.background = '#DCFCE7';
                label.style.padding = '2px 6px';
                label.style.borderRadius = '5px';
            } else {
                label.textContent = 'Image #' + (index + 1);
                label.style.color = '#64748B';
                label.style.background = 'transparent';
                label.style.padding = '0';
            }
        });
        if (cards.length === 0) {
            const grid = document.getElementById('productGalleryGrid');
            if (grid) {
                grid.innerHTML = '<div id="emptyGalleryNotice" style="grid-column:1 / -1;text-align:center;padding:22px;color:#94A3B8;font-size:12.5px;background:#fff;border-radius:10px;border:1.5px dashed var(--border)">No custom product images uploaded yet. Upload photos below to add to this product\'s gallery.</div>';
            }
        }
    }

    function deleteProductThumbnail(productId, imageName, cardId) {
        if (!confirm('Remove this image from the product gallery?')) {
            return;
        }

        const card = document.getElementById(cardId);
        if (card) {
            card.style.opacity = '0.45';
            card.style.pointerEvents = 'none';
        }

        const token = document.querySelector('input[name="_token"]')?.value || '';

        fetch('{{ url("/admin/products") }}/' + productId + '/delete-image', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ image_name: imageName })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (card) card.remove();
                refreshGalleryBadges();
            } else {
                alert(data.message || 'Could not remove image.');
                if (card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
            }
        })
        .catch(() => {
            alert('Network error while deleting image. Please try again.');
            if (card) {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
        });
    }

    // Live multi-image preview with corner cross removal before uploading
    let selectedNewFiles = [];
    const imagesInputEl = document.getElementById('images');
    const newPreviewGrid = document.getElementById('newImagesPreviewGrid');

    function syncFileInputFromSelected() {
        if (!imagesInputEl || typeof DataTransfer === 'undefined') return;
        const dt = new DataTransfer();
        selectedNewFiles.forEach(f => dt.items.add(f));
        imagesInputEl.files = dt.files;
    }

    function renderNewImagesPreview() {
        if (!newPreviewGrid) return;
        newPreviewGrid.innerHTML = '';
        if (selectedNewFiles.length === 0) {
            newPreviewGrid.style.display = 'none';
            return;
        }
        newPreviewGrid.style.display = 'grid';
        selectedNewFiles.forEach((file, idx) => {
            const card = document.createElement('div');
            card.className = 'img-thumb-card';
            card.style.borderColor = '#3B82F6';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'img-corner-cross-btn';
            btn.title = 'Remove selected image';
            btn.textContent = '✕';
            btn.onclick = function () {
                selectedNewFiles.splice(idx, 1);
                syncFileInputFromSelected();
                renderNewImagesPreview();
            };

            const box = document.createElement('div');
            box.className = 'img-thumb-box';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;
            box.appendChild(img);

            const footer = document.createElement('div');
            footer.className = 'img-thumb-footer';
            footer.innerHTML = '<span style="color:#1D4ED8;background:#EFF6FF;padding:2px 6px;border-radius:5px">New #' + (idx + 1) + '</span>';

            card.appendChild(btn);
            card.appendChild(box);
            card.appendChild(footer);
            newPreviewGrid.appendChild(card);
        });
    }

    imagesInputEl?.addEventListener('change', function (e) {
        const added = Array.from(e.target.files || []);
        if (added.length > 0) {
            selectedNewFiles = selectedNewFiles.concat(added);
            syncFileInputFromSelected();
            renderNewImagesPreview();
        }
    });
</script>
@endpush
@endsection
