@extends('layouts.app')

@section('content')
<main class="container" style="max-width:650px;padding:48px 16px">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:40px 28px;text-align:center;box-shadow:var(--shadow-md)">
        <div style="width:84px;height:84px;border-radius:50%;background:#ECFDF5;color:#047857;display:flex;align-items:center;justify-content:center;font-size:42px;margin:0 auto 20px;box-shadow:0 4px 14px rgba(4,120,87,0.15)">
            ✓
        </div>
        <h1 style="font-size:26px;font-weight:800;color:#047857;margin-bottom:8px" data-i18n="order_placed_successfully">Order Placed Successfully!</h1>
        <p style="color:var(--text-secondary);font-size:14px;margin-bottom:22px">
            Thank you for shopping with <strong>TOUCHSTAINS</strong>! Your order has been confirmed in our system and is being prepared for fast express dispatch.
        </p>

        <div style="background:var(--bg-body);border:1px dashed var(--primary);border-radius:var(--radius-md);padding:18px;margin-bottom:24px;text-align:left">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <span style="color:var(--text-secondary)">Order ID:</span>
                <strong style="color:var(--primary);font-size:16px" id="success-order-id">#{{ $order->id ?? request('id', '100248') }}</strong>
            </div>
            @if($order)
                <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                    <span style="color:var(--text-secondary)">Order Amount:</span>
                    <strong style="color:#111827;font-size:16px">₹{{ number_format($order->order_amount, 2) }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                    <span style="color:var(--text-secondary)">Payment Method:</span>
                    <strong style="text-transform:uppercase;color:#047857">
                        @if($order->isRazorpay())
                            ⚡ Razorpay (Online Payment)
                        @else
                            💵 Cash on Delivery (COD)
                        @endif
                    </strong>
                </div>
                <div style="display:flex;justify-content:space-between">
                    <span style="color:var(--text-secondary)">Payment Status:</span>
                    <span>
                        @if($order->payment_status === 'paid')
                            <span style="background:#DCFCE7;color:#166534;font-size:12px;font-weight:800;padding:2px 10px;border-radius:99px;border:1px solid #BBF7D0">✓ PAID</span>
                        @else
                            <span style="background:#FEF3C7;color:#92400E;font-size:12px;font-weight:800;padding:2px 10px;border-radius:99px;border:1px solid #FDE68A">PAY ON DELIVERY</span>
                        @endif
                    </span>
                </div>
                @if($order->utr_number)
                    <div style="display:flex;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:1px dashed var(--border)">
                        <span style="color:var(--text-secondary)">Razorpay Payment ID:</span>
                        <code style="background:#EFF6FF;color:#1D4ED8;padding:2px 8px;border-radius:6px;font-weight:700">{{ $order->utr_number }}</code>
                    </div>
                @endif
            @endif
        </div>

        @if($order && $order->isRazorpay())
            <!-- Razorpay Payment Details Card -->
            <div style="margin-bottom:24px;text-align:left;border:1.5px solid #86EFAC;border-radius:12px;overflow:hidden;box-shadow:0 4px 14px rgba(4,120,87,0.08)">
                <div style="background:linear-gradient(135deg, #065F46 0%, #059669 100%);padding:14px 18px;display:flex;align-items:center;justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:22px">⚡</span>
                        <div>
                            <h4 style="color:#fff;margin:0;font-size:14.5px;font-weight:700">Razorpay Payment Verified</h4>
                            <span style="color:#D1FAE5;font-size:12px">Instant 256-Bit SSL Encrypted Transaction</span>
                        </div>
                    </div>
                    <span style="background:#ECFDF5;color:#065F46;border:1px solid #A7F3D0;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:99px">✓ Confirmed</span>
                </div>
                <div style="padding:16px 18px;background:#fff">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px">
                        <div>
                            <span style="font-size:11.5px;color:#6B7280;display:block">Razorpay Payment ID</span>
                            <strong style="font-family:monospace;font-size:13.5px;color:#0F172A">{{ $order->razorpay_payment_id ?? 'Verified' }}</strong>
                        </div>
                        <div>
                            <span style="font-size:11.5px;color:#6B7280;display:block">Razorpay Order ID</span>
                            <strong style="font-family:monospace;font-size:13.5px;color:#0F172A">{{ $order->razorpay_order_id ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($order && $order->isCod())
            <!-- Cash on Delivery Notice Card -->
            <div style="margin-bottom:24px;text-align:left;border:1.5px solid #93C5FD;border-radius:12px;overflow:hidden;box-shadow:0 4px 14px rgba(59,130,246,0.08)">
                <div style="background:linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);padding:14px 18px;display:flex;align-items:center;justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:22px">💵</span>
                        <div>
                            <h4 style="color:#fff;margin:0;font-size:14.5px;font-weight:700">Cash on Delivery Order</h4>
                            <span style="color:#DBEAFE;font-size:12px">Please keep exact cash or UPI ready at delivery</span>
                        </div>
                    </div>
                    <span style="background:#EFF6FF;color:#1E40AF;border:1px solid #BFDBFE;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:99px">Pending Delivery</span>
                </div>
                <div style="padding:14px 18px;background:#F8FAFC;font-size:12.5px;color:#334155;line-height:1.5">
                    Our delivery partner will reach your address: <strong>{{ $order->delivery_address['address'] ?? 'Your delivery location' }}</strong>. You can pay with cash or scan the courier agent's UPI QR code upon parcel arrival.
                </div>
            </div>
        @endif

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="{{ url('/track-order?id=' . ($order->id ?? request('id', 100248))) }}" class="btn-primary" style="background:#01684B;border-color:#01684B">
                🚚 Track My Order Live
            </a>
            <a href="{{ url('/order-details?id=' . ($order->id ?? request('id', 100248))) }}" class="btn-outline">
                📄 View Order Invoice
            </a>
            <a href="{{ url('/') }}" class="btn-outline">
                🏠 Back to Home
            </a>
        </div>
    </div>
</main>
@endsection
