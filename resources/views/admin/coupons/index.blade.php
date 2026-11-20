@extends('admin.layouts.master')

@section('title', 'Marketing & Coupons')

@section('content')

    <div style="display:grid;grid-template-columns:1fr 1.8fr;gap:24px;align-items:start">
        
        <!-- Left: Create Coupon Form -->
        <div class="admin-card">
            <h2 class="admin-card-title" style="margin-bottom:16px">🎟️ Create Discount Coupon</h2>
            
            <form action="{{ route('admin.coupons.store') }}" method="POST">
                @csrf

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Coupon Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Diwali Super Sale 20% OFF" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Coupon Code *</label>
                    <input type="text" name="code" required placeholder="e.g. TOUCH20" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;text-transform:uppercase;font-weight:800;letter-spacing:1px">
                </div>

                <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Discount Value *</label>
                        <input type="number" step="0.1" name="discount" required placeholder="20" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Discount Type</label>
                        <select name="discount_type" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                            <option value="percent">Percentage (%)</option>
                            <option value="amount">Fixed Amount (₹)</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Min Purchase (₹)</label>
                        <input type="number" name="min_purchase" value="0" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Expiry Date</label>
                        <input type="date" name="expire_date" value="{{ date('Y-12-31') }}" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                </div>

                <button type="submit" class="btn-theme" style="width:100%;justify-content:center">
                    💾 Create Coupon
                </button>
            </form>
        </div>

        <!-- Right: Coupons Table -->
        <div class="admin-card">
            <div class="admin-card-header">
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
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coup)
                            <tr>
                                <td>
                                    <span style="font-family:monospace;font-weight:800;font-size:13px;background:#EFF6FF;color:#1E40AF;padding:3px 8px;border-radius:6px;border:1px dashed #93C5FD">
                                        {{ $coup->code }}
                                    </span>
                                </td>
                                <td><strong style="font-size:13px">{{ $coup->title }}</strong></td>
                                <td style="font-weight:800;color:var(--primary)">
                                    {{ $coup->discount_type === 'percent' ? $coup->discount . '%' : '₹' . $coup->discount }} OFF
                                </td>
                                <td>{{ $coup->min_purchase > 0 ? '₹' . $coup->min_purchase : 'No Min' }}</td>
                                <td style="font-size:12px;color:var(--text-muted)">{{ $coup->expire_date }}</td>
                                <td style="text-align:right">
                                    <form action="{{ route('admin.coupons.delete', $coup->id) }}" method="POST" onsubmit="return confirm('Delete coupon {{ $coup->code }}?')" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:4px 8px;font-size:11px;color:#DC2626">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:24px;color:#94A3B8">No coupons available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
