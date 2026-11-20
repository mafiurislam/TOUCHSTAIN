@extends('admin.layouts.master')

@section('title', 'General Website Settings')

@section('content')

    <div class="admin-card" style="max-width:850px">
        <div class="admin-card-header">
            <div style="display:flex;align-items:center;gap:14px">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:48px;width:auto;object-fit:contain;border:1px solid var(--border);border-radius:8px;padding:4px;background:#fff">
                <div>
                    <h1 class="admin-card-title">⚙️ General Website & Store Settings</h1>
                    <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Configure store contact details, currency, free shipping rules, and ordering limits</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px">
                <div style="grid-column: 1 / -1">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Store Name *</label>
                    <input type="text" name="ecommerce_name" value="{{ $settings['ecommerce_name'] ?? 'TOUCHSTAINS' }}" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="grid-column: 1 / -1">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Head Office Address *</label>
                    <textarea name="ecommerce_address" rows="2" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">{{ $settings['ecommerce_address'] ?? 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022' }}</textarea>
                </div>

                <div>
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Contact Phone Number *</label>
                    <input type="text" name="ecommerce_phone" value="{{ $settings['ecommerce_phone'] ?? '+91 7679416294 / 7039214465' }}" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div>
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Support Email Address *</label>
                    <input type="email" name="ecommerce_email" value="{{ $settings['ecommerce_email'] ?? 'touchstains@gmail.com' }}" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div>
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Minimum Order Value (₹)</label>
                    <input type="number" name="minimum_order_value" value="{{ $settings['minimum_order_value'] ?? 69 }}" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div>
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Free Delivery Threshold (₹)</label>
                    <input type="number" name="free_delivery_over_amount" value="{{ $settings['free_delivery_over_amount'] ?? 69 }}" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div>
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Razorpay API Key ID *</label>
                    <input type="text" name="razorpay_key_id" value="{{ $settings['razorpay_key_id'] ?? config('services.razorpay.key_id', 'rzp_test_U1cQQReArMfXP9') }}" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-family:monospace">
                </div>

                <div>
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Razorpay API Key Secret (Optional)</label>
                    <input type="password" name="razorpay_key_secret" value="{{ $settings['razorpay_key_secret'] ?? config('services.razorpay.key_secret', '') }}" placeholder="Enter Razorpay Key Secret if available" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-family:monospace">
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end">
                <button type="submit" class="btn-theme" style="padding:11px 26px">
                    💾 Save Settings
                </button>
            </div>
        </form>
    </div>

@endsection
