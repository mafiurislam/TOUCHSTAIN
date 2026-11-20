@extends('admin.layouts.master')

@section('title', 'Brand Management')

@section('content')

    <div style="display:grid;grid-template-columns:1fr 1.8fr;gap:24px;align-items:start">
        
        <!-- Left: Add Brand Form -->
        <div class="admin-card">
            <h2 class="admin-card-title" style="margin-bottom:16px">🏷️ Add New Brand</h2>
            
            <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Brand Name *</label>
                    <input type="text" name="name" required placeholder="e.g. TouchStains Original" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:18px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Brand Logo (Optional)</label>
                    <input type="file" name="image" accept="image/*" style="width:100%;padding:8px;border:1.5px dashed var(--border);border-radius:8px">
                </div>

                <button type="submit" class="btn-theme" style="width:100%;justify-content:center">
                    💾 Save Brand
                </button>
            </form>
        </div>

        <!-- Right: Brands Table -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">🏷️ Active Brands ({{ count($brands) }})</h2>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Product manufacturer and brand labels</p>
                </div>
            </div>

            <div style="overflow-x:auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Brand Name</th>
                            <th>Slug</th>
                            <th>Products Count</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($brands as $brand)
                            <tr>
                                <td style="color:var(--text-muted);font-weight:700">{{ $brand->id }}</td>
                                <td>
                                    <strong style="font-size:13.5px">{{ $brand->name }}</strong>
                                </td>
                                <td><code style="font-size:11.5px">{{ $brand->slug }}</code></td>
                                <td style="font-weight:800;color:var(--primary)">{{ $brand->products_count }}</td>
                                <td style="text-align:right">
                                    <form action="{{ route('admin.brands.delete', $brand->id) }}" method="POST" onsubmit="return confirm('Delete brand {{ addslashes($brand->name) }}?')" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:4px 8px;font-size:11px;color:#DC2626">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:24px;color:#94A3B8">No brands found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
