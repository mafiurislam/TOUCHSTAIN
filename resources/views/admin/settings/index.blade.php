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

                <!-- Payment Methods On / Off Switch (User Requested Feature) -->
                <div style="grid-column: 1 / -1;background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:12px;padding:18px;margin-top:6px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                        <div>
                            <h3 style="margin:0;font-size:15px;font-weight:800;color:#0F172A">💳 Payment Methods (On / Off Controls)</h3>
                            <p style="margin:2px 0 0;font-size:12px;color:#64748B">Turn online Razorpay or Cash on Delivery ON or OFF. Disabled methods are automatically hidden on checkout.</p>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(230px, 1fr));gap:16px">
                        <!-- UPI Direct Toggle -->
                        <div style="background:#fff;border:1.5px solid {{ ($settings['payment_upi_active'] ?? '1') !== '0' ? '#C4B5FD' : '#FCA5A5' }};border-radius:10px;padding:14px">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                <strong style="font-size:13.5px;color:#0F172A">📱 UPI Payment (QR)</strong>
                                <span style="font-size:11px;font-weight:800;padding:2px 8px;border-radius:6px;background:{{ ($settings['payment_upi_active'] ?? '1') !== '0' ? '#EDE9FE' : '#FEE2E2' }};color:{{ ($settings['payment_upi_active'] ?? '1') !== '0' ? '#5B21B6' : '#991B1B' }}">
                                    {{ ($settings['payment_upi_active'] ?? '1') !== '0' ? '● ACTIVE (ON)' : '○ DISABLED (OFF)' }}
                                </span>
                            </div>
                            <p style="font-size:11.5px;color:#64748B;margin:0 0 10px;line-height:1.4">
                                Direct PhonePe QR code scanning and app-to-app UPI transfer with zero gateway fee.
                            </p>
                            <select name="payment_upi_active" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:6px;font-size:12.5px;font-weight:700">
                                <option value="1" {{ ($settings['payment_upi_active'] ?? '1') !== '0' ? 'selected' : '' }}>🟢 Enabled (ON) — Show on Checkout</option>
                                <option value="0" {{ ($settings['payment_upi_active'] ?? '1') === '0' ? 'selected' : '' }}>🔴 Disabled (OFF) — Hide on Checkout</option>
                            </select>
                        </div>

                        <!-- Razorpay Online Toggle -->
                        <div style="background:#fff;border:1.5px solid {{ ($settings['payment_razorpay_active'] ?? '1') !== '0' ? '#86EFAC' : '#FCA5A5' }};border-radius:10px;padding:14px">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                <strong style="font-size:13.5px;color:#0F172A">⚡ Razorpay Online</strong>
                                <span style="font-size:11px;font-weight:800;padding:2px 8px;border-radius:6px;background:{{ ($settings['payment_razorpay_active'] ?? '1') !== '0' ? '#DCFCE7' : '#FEE2E2' }};color:{{ ($settings['payment_razorpay_active'] ?? '1') !== '0' ? '#166534' : '#991B1B' }}">
                                    {{ ($settings['payment_razorpay_active'] ?? '1') !== '0' ? '● ACTIVE (ON)' : '○ DISABLED (OFF)' }}
                                </span>
                            </div>
                            <p style="font-size:11.5px;color:#64748B;margin:0 0 10px;line-height:1.4">
                                Accept online cards, netbanking, and wallets via Razorpay gateway.
                            </p>
                            <select name="payment_razorpay_active" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:6px;font-size:12.5px;font-weight:700">
                                <option value="1" {{ ($settings['payment_razorpay_active'] ?? '1') !== '0' ? 'selected' : '' }}>🟢 Enabled (ON) — Show on Checkout</option>
                                <option value="0" {{ ($settings['payment_razorpay_active'] ?? '1') === '0' ? 'selected' : '' }}>🔴 Disabled (OFF) — Hide on Checkout</option>
                            </select>
                        </div>

                        <!-- COD Toggle -->
                        <div style="background:#fff;border:1.5px solid {{ ($settings['payment_cod_active'] ?? '1') !== '0' ? '#86EFAC' : '#FCA5A5' }};border-radius:10px;padding:14px">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                <strong style="font-size:13.5px;color:#0F172A">💵 Cash on Delivery (COD)</strong>
                                <span style="font-size:11px;font-weight:800;padding:2px 8px;border-radius:6px;background:{{ ($settings['payment_cod_active'] ?? '1') !== '0' ? '#DCFCE7' : '#FEE2E2' }};color:{{ ($settings['payment_cod_active'] ?? '1') !== '0' ? '#166534' : '#991B1B' }}">
                                    {{ ($settings['payment_cod_active'] ?? '1') !== '0' ? '● ACTIVE (ON)' : '○ DISABLED (OFF)' }}
                                </span>
                            </div>
                            <p style="font-size:11.5px;color:#64748B;margin:0 0 10px;line-height:1.4">
                                Allow customers to pay in cash or rider UPI upon delivery.
                            </p>
                            <select name="payment_cod_active" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:6px;font-size:12.5px;font-weight:700">
                                <option value="1" {{ ($settings['payment_cod_active'] ?? '1') !== '0' ? 'selected' : '' }}>🟢 Enabled (ON) — Show on Checkout</option>
                                <option value="0" {{ ($settings['payment_cod_active'] ?? '1') === '0' ? 'selected' : '' }}>🔴 Disabled (OFF) — Hide on Checkout</option>
                            </select>
                        </div>
                    </div>
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
