@extends('admin.layouts.master')

@section('title', 'Marketing & Coupons')

@section('content')

    <style>
    .coupons-layout-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 24px;
        align-items: start;
        width: 100%;
        box-sizing: border-box;
    }
    @media (max-width: 960px) {
        .coupons-layout-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
    </style>

    <div class="coupons-layout-grid">
        
        <!-- Left: Create Coupon Form -->
        <div class="admin-card">
            <h2 class="admin-card-title" style="margin-bottom:16px">🎟️ Create Discount Coupon</h2>

            @if(isset($errors) && $errors->any())
                <div style="background:#FEE2E2;border:1.5px solid #F87171;color:#991B1B;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px">
                    <strong style="display:block;margin-bottom:4px">⚠️ Please fix the following errors:</strong>
                    <ul style="margin:0;padding-left:20px">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('admin.coupons.store') }}" method="POST">
                @csrf

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Coupon Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Eid 50% discount / Diwali 20% OFF" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;box-sizing:border-box;font-size:13.5px">
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Coupon Code *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="e.g. ED-110" oninput="this.value = this.value.toUpperCase()" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;text-transform:uppercase;font-weight:800;letter-spacing:1px;box-sizing:border-box;font-size:14px">
                    <small style="color:var(--text-muted);font-size:11.5px;margin-top:4px;display:block">Customers enter this code at checkout to get discount.</small>
                </div>

                <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Discount Value *</label>
                        <input type="number" step="0.01" min="0.01" name="discount" value="{{ old('discount') }}" required placeholder="e.g. 50" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;box-sizing:border-box;font-size:13.5px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Discount Type</label>
                        <select name="discount_type" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;box-sizing:border-box;font-size:13.5px;background:#fff">
                            <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="amount" {{ old('discount_type') === 'amount' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Min Purchase (₹)</label>
                        <input type="number" step="1" min="0" name="min_purchase" value="{{ old('min_purchase', 0) }}" placeholder="0 for No Min" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;box-sizing:border-box;font-size:13.5px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Expiry Date</label>
                        <input type="date" name="expire_date" value="{{ old('expire_date', date('Y-12-31')) }}" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;box-sizing:border-box;font-size:13.5px">
                    </div>
                </div>

                <div style="margin-bottom:16px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Max Discount Cap (₹) <span style="font-weight:400;color:var(--text-muted)">(Optional)</span></label>
                    <input type="number" step="1" min="0" name="max_discount" value="{{ old('max_discount', 0) }}" placeholder="0 for no maximum limit" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;box-sizing:border-box;font-size:13.5px">
                    <small style="color:var(--text-muted);font-size:11px;margin-top:3px;display:block">For % discount, limit max discount amount (e.g. 500). Leave 0 for unlimited.</small>
                </div>

                <button type="submit" class="btn-theme" style="width:100%;justify-content:center;height:44px;font-size:14.5px;font-weight:700">
                    🎟️ Create Coupon
                </button>
            </form>
        </div>

        <!-- Right: Coupons Table -->
        <div class="admin-card">
            <div class="admin-card-header" style="margin-bottom:16px">
                <div>
                    <h2 class="admin-card-title">🎟️ Active Promotional Coupons ({{ count($coupons) }})</h2>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Codes customers can apply at checkout</p>
                </div>
            </div>

            <div style="overflow-x:auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Discount</th>
                            <th>Min Spend</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coup)
                            <tr>
                                <td>
                                    <span style="font-family:monospace;font-weight:800;font-size:13px;background:#EFF6FF;color:#1E40AF;padding:4px 9px;border-radius:6px;border:1px dashed #93C5FD;display:inline-block">
                                        {{ $coup->code }}
                                    </span>
                                </td>
                                <td><strong style="font-size:13px">{{ $coup->title }}</strong></td>
                                <td style="font-weight:800;color:var(--primary, #01684B)">
                                    {{ $coup->discount_type === 'percent' ? $coup->discount . '%' : '₹' . $coup->discount }} OFF
                                </td>
                                <td>{{ $coup->min_purchase > 0 ? '₹' . number_format($coup->min_purchase, 0) : 'No Min' }}</td>
                                <td style="font-size:12px;color:var(--text-muted)">{{ $coup->expire_date ?: 'Never' }}</td>
                                <td>
                                    <form action="{{ route('admin.coupons.toggle', $coup->id) }}" method="POST" style="margin:0;display:inline">
                                        @csrf
                                        <button type="submit" title="Click to Toggle Status" style="border:none;background:none;cursor:pointer;padding:0">
                                            @if($coup->status)
                                                <span style="background:#DCFCE7;color:#15803D;font-weight:700;font-size:11.5px;padding:3px 9px;border-radius:99px;border:1px solid #BBF7D0;display:inline-flex;align-items:center;gap:3px">
                                                    ✓ Active
                                                </span>
                                            @else
                                                <span style="background:#F1F5F9;color:#64748B;font-weight:700;font-size:11.5px;padding:3px 9px;border-radius:99px;border:1px solid #CBD5E1;display:inline-flex;align-items:center;gap:3px">
                                                    ✕ Inactive
                                                </span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align:right">
                                    <form action="{{ route('admin.coupons.delete', $coup->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete coupon {{ $coup->code }}?')" style="margin:0;display:inline">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:5px 10px;font-size:11.5px;color:#DC2626;border-color:#FCA5A5">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;padding:32px;color:#94A3B8">
                                    <div style="font-size:28px;margin-bottom:6px">🎟️</div>
                                    <strong>No coupons available yet.</strong>
                                    <p style="font-size:12px;margin:4px 0 0">Use the form on the left to create your first discount coupon code!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
