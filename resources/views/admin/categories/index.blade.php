@extends('admin.layouts.master')

@section('title', 'Category Management')

@section('content')

    <style>
    .categories-layout-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 24px;
        align-items: start;
        width: 100%;
        box-sizing: border-box;
    }
    @media (max-width: 960px) {
        .categories-layout-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 16px;
    }
    .modal-overlay.open {
        display: flex;
    }
    .modal-box {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        overflow: hidden;
        animation: modalFadeIn 0.2s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(12px) scale(0.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    </style>

    <div class="categories-layout-grid">
        
        <!-- Left: Add New Category Form -->
        <div class="admin-card">
            <h2 class="admin-card-title" style="margin-bottom:16px">➕ Add New Category</h2>
            
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Fabric Cleaners" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Parent Category</label>
                    <select name="parent_id" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                        <option value="0">None (Main Category)</option>
                        @foreach($allParents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    <small style="color:var(--text-muted);font-size:11px;display:block;margin-top:4px">Select "None" for a top-level category, or choose a parent for a subcategory.</small>
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Priority / Order</label>
                    <input type="number" name="priority" value="1" min="0" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    <small style="color:var(--text-muted);font-size:11px;display:block;margin-top:4px">Lower numbers or higher priority display first.</small>
                </div>

                <div style="margin-bottom:18px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Category Image</label>
                    <input type="file" name="image" id="create-cat-img-input" accept="image/*" onchange="previewCatImg(this, 'create-cat-preview')" style="width:100%;padding:8px;border:1.5px dashed var(--border);border-radius:8px">
                    <div id="create-cat-preview" style="margin-top:10px;display:none">
                        <img src="" alt="Preview" style="max-height:80px;border-radius:8px;border:1px solid var(--border)">
                    </div>
                </div>

                <button type="submit" class="btn-theme" style="width:100%;justify-content:center">
                    💾 Create Category
                </button>
            </form>
        </div>

        <!-- Right: Categories List -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">🗂️ Categories List ({{ count($categories) }})</h2>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Unlimited main categories and subcategories</p>
                </div>
            </div>

            <div style="overflow-x:auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Category</th>
                            <th>Subcategories</th>
                            <th style="text-align:center">Priority</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            <tr>
                                <td style="color:var(--text-muted);font-weight:700">{{ $cat->id }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" style="width:40px;height:40px;border-radius:8px;object-fit:cover;background:#F8FAFC;border:1px solid var(--border)" onerror="this.src='{{ asset('assets/image/place_holder.png') }}'">
                                        <div>
                                            <strong style="font-size:13.5px;display:block">{{ $cat->name }}</strong>
                                            @if($cat->slug)
                                                <span style="font-size:11px;color:var(--text-muted)">/{{ $cat->slug }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($cat->children && count($cat->children) > 0)
                                        <div style="display:flex;gap:5px;flex-wrap:wrap">
                                            @foreach($cat->children as $ch)
                                                <span style="font-size:11px;background:#EFF6FF;color:#1E40AF;padding:3px 9px;border-radius:999px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                                                    {{ $ch->name }}
                                                    <button type="button" onclick="openEditCategoryModal({{ $ch->id }}, '{{ addslashes($ch->name) }}', {{ $ch->parent_id }}, {{ $ch->priority ?? 0 }}, '{{ $ch->image_url }}')" style="background:none;border:none;cursor:pointer;color:#1E40AF;font-size:10px;padding:0" title="Edit subcategory">✏️</button>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="font-size:11.5px;color:var(--text-muted)">None</span>
                                    @endif
                                </td>
                                <td style="font-weight:700;text-align:center">
                                    <span style="background:#F1F5F9;color:#334155;padding:2px 8px;border-radius:6px;font-size:12px">{{ $cat->priority }}</span>
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    <button type="button" onclick="openEditCategoryModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', {{ $cat->parent_id }}, {{ $cat->priority ?? 0 }}, '{{ $cat->image_url }}')" class="btn-secondary" style="padding:5px 10px;font-size:11.5px;margin-right:6px">
                                        ✏️ Edit
                                    </button>
                                    <form action="{{ route('admin.categories.delete', $cat->id) }}" method="POST" onsubmit="return confirm('Delete category {{ addslashes($cat->name) }} and all its subcategories?')" style="margin:0;display:inline">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:5px 10px;font-size:11.5px;color:#DC2626;border-color:#FCA5A5">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:32px;color:#94A3B8">
                                    <div style="font-size:32px;margin-bottom:8px">🗂️</div>
                                    <strong>No categories found.</strong>
                                    <p style="font-size:12px;margin:4px 0 0">Use the form on the left to create your first category!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Edit Category Modal -->
    <div id="editCatModal" class="modal-overlay" onclick="if(event.target===this) closeEditCategoryModal()">
        <div class="modal-box">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)">
                <h3 style="margin:0;font-size:16px;font-weight:800;display:flex;align-items:center;gap:8px">
                    ✏️ Edit Category
                </h3>
                <button type="button" onclick="closeEditCategoryModal()" style="border:none;background:none;font-size:20px;line-height:1;cursor:pointer;color:var(--text-muted)">&times;</button>
            </div>
            <form id="editCatForm" method="POST" enctype="multipart/form-data" style="padding:20px">
                @csrf
                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Category Name *</label>
                    <input type="text" name="name" id="editCatName" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Parent Category</label>
                    <select name="parent_id" id="editCatParentId" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                        <option value="0">None (Main Category)</option>
                        @foreach($allParents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Priority / Order</label>
                    <input type="number" name="priority" id="editCatPriority" min="0" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:18px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Replace Category Image (Optional)</label>
                    <div style="display:flex;align-items:center;gap:12px">
                        <img id="editCatCurrentImg" src="" alt="Current" style="width:50px;height:50px;border-radius:8px;object-fit:cover;border:1px solid var(--border);background:#F8FAFC">
                        <div style="flex:1">
                            <input type="file" name="image" accept="image/*" onchange="previewCatImg(this, 'edit-cat-preview-box')" style="width:100%;padding:6px;border:1.5px dashed var(--border);border-radius:8px">
                        </div>
                    </div>
                    <div id="edit-cat-preview-box" style="margin-top:8px;display:none">
                        <small style="display:block;color:var(--text-muted);margin-bottom:4px">New Image Preview:</small>
                        <img src="" alt="Preview" style="max-height:70px;border-radius:6px;border:1px solid var(--border)">
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                    <button type="button" onclick="closeEditCategoryModal()" class="btn-secondary" style="padding:8px 16px">Cancel</button>
                    <button type="submit" class="btn-theme" style="padding:8px 20px">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function previewCatImg(input, targetId) {
        const box = document.getElementById(targetId);
        if (!box) return;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = box.querySelector('img');
                if (img) img.src = e.target.result;
                box.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            box.style.display = 'none';
        }
    }

    function openEditCategoryModal(id, name, parentId, priority, imageUrl) {
        const form = document.getElementById('editCatForm');
        form.action = `/admin/categories/${id}/update`;
        document.getElementById('editCatName').value = name || '';
        document.getElementById('editCatParentId').value = parentId || 0;
        document.getElementById('editCatPriority').value = priority || 0;
        const curImg = document.getElementById('editCatCurrentImg');
        if (curImg) {
            curImg.src = imageUrl || '{{ asset("assets/image/place_holder.png") }}';
        }
        const previewBox = document.getElementById('edit-cat-preview-box');
        if (previewBox) previewBox.style.display = 'none';
        document.getElementById('editCatModal').classList.add('open');
    }

    function closeEditCategoryModal() {
        document.getElementById('editCatModal').classList.remove('open');
    }
    </script>

@endsection
