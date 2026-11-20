@extends('admin.layouts.master')

@section('title', 'Category Management')

@section('content')

    <div style="display:grid;grid-template-columns:1fr 1.8fr;gap:24px;align-items:start">
        
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
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Priority / Order</label>
                    <input type="number" name="priority" value="1" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:18px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Category Image</label>
                    <input type="file" name="image" accept="image/*" style="width:100%;padding:8px;border:1.5px dashed var(--border);border-radius:8px">
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
                            <th>Priority</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            <tr>
                                <td style="color:var(--text-muted);font-weight:700">{{ $cat->id }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" style="width:36px;height:36px;border-radius:6px;object-fit:cover;background:#F8FAFC;border:1px solid var(--border)" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                                        <strong style="font-size:13.5px">{{ $cat->name }}</strong>
                                    </div>
                                </td>
                                <td>
                                    @if($cat->children && count($cat->children) > 0)
                                        <div style="display:flex;gap:4px;flex-wrap:wrap">
                                            @foreach($cat->children as $ch)
                                                <span style="font-size:11px;background:#EFF6FF;color:#1E40AF;padding:2px 8px;border-radius:999px;font-weight:600">
                                                    {{ $ch->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="font-size:11.5px;color:var(--text-muted)">None</span>
                                    @endif
                                </td>
                                <td style="font-weight:700">{{ $cat->priority }}</td>
                                <td style="text-align:right">
                                    <form action="{{ route('admin.categories.delete', $cat->id) }}" method="POST" onsubmit="return confirm('Delete category {{ addslashes($cat->name) }} and its subcategories?')" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:4px 8px;font-size:11px;color:#DC2626">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:24px;color:#94A3B8">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
