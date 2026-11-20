@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <a href="{{ url('/orders') }}">My Orders</a> <span>›</span>
        <span>Order #{{ $order->id ?? '100248' }}</span>
    </div>

    @if(session('success'))
        <div style="background:#ECFDF5;border:1px solid #059669;color:#065F46;padding:14px 20px;border-radius:10px;font-weight:700;margin-bottom:20px">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#FEF2F2;border:1px solid #DC2626;color:#991B1B;padding:14px 20px;border-radius:10px;font-weight:600;margin-bottom:20px">
            ⚠️ {{ $errors->first() }}
        </div>
    @endif

    @if($order)
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;box-shadow:var(--shadow-sm)">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;padding-bottom:20px;border-bottom:1px solid var(--border)">
                <div>
                    <div style="display:flex;align-items:center;gap:12px">
                        <h1 style="font-size:22px;font-weight:800;color:var(--primary)">Order #{{ $order->id }}</h1>
                        <span style="padding:4px 12px;border-radius:99px;font-size:12px;font-weight:700;text-transform:uppercase;background:var(--primary-light);color:var(--primary)">
                            {{ str_replace('_', ' ', $order->order_status) }}
                        </span>
                    </div>
                    <p style="font-size:13px;color:var(--text-secondary);margin-top:4px">
                        Placed on {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : $order->delivery_date }} • Time Slot: <strong>{{ $order->time_slot }}</strong>
                    </p>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <button type="button" class="btn-outline" onclick="window.print()">🖨️ Print Invoice</button>
                    <a href="{{ url('/track-order?id=' . $order->id) }}" class="btn-primary">🚚 Live Track</a>
                </div>
            </div>

            <!-- Payment Summary Banner (Razorpay or Cash on Delivery) -->
            <div style="margin:20px 0">
                @if(!$order->isCod())
                    <div style="background:#EFF6FF;border:1.5px solid #93C5FD;border-radius:12px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
                        <div style="display:flex;align-items:center;gap:12px">
                            <div style="background:#02042B;color:#fff;padding:8px 12px;border-radius:8px;font-weight:800;font-size:13px">◆ Razorpay</div>
                            <div>
                                <h3 style="font-size:15px;font-weight:800;color:#1E3A8A;margin:0">Paid Online via Razorpay</h3>
                                <p style="font-size:12.5px;color:#1E40AF;margin:2px 0 0">
                                    Amount: <strong>₹{{ number_format($order->order_amount, 2) }}</strong>
                                    @if($order->utr_number)
                                        • Payment ID: <code style="background:#fff;padding:2px 6px;border-radius:4px;font-weight:700">{{ $order->utr_number }}</code>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span style="background:#DCFCE7;color:#166534;padding:4px 12px;border-radius:99px;font-size:12px;font-weight:800">✓ PAID</span>
                    </div>
                @else
                    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:12px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
                        <div style="display:flex;align-items:center;gap:12px">
                            <span style="font-size:24px">🚚</span>
                            <div>
                                <h3 style="font-size:15px;font-weight:800;color:#065F46;margin:0">Cash on Delivery (COD)</h3>
                                <p style="font-size:12.5px;color:#166534;margin:2px 0 0">
                                    Pay <strong>₹{{ number_format($order->order_amount, 2) }}</strong> in cash upon receiving your order at doorstep.
                                </p>
                            </div>
                        </div>
                        <span style="background:#FEF3C7;color:#92400E;padding:4px 12px;border-radius:99px;font-size:12px;font-weight:800">{{ strtoupper($order->payment_status) }}</span>
                    </div>
                @endif
            </div>

            <!-- 🚚 Shiprocket Delivery & Logistics Card -->
            @php
                $shipment = $order->shipment;
            @endphp
            @if($shipment)
                <div style="background:#fff;border:1.5px solid #10B981;border-radius:var(--radius-md);padding:20px;margin:20px 0;box-shadow:0 4px 12px rgba(16,185,129,0.08)">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;border-bottom:1px solid #E5E7EB;padding-bottom:14px;margin-bottom:16px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span style="font-size:24px">🚚</span>
                            <div>
                                <h3 style="font-size:16px;font-weight:800;color:#065F46;margin:0">
                                    Delivery Partner: {{ $shipment->courier_name ?: 'Shiprocket Express Logistics' }}
                                </h3>
                                <p style="font-size:12px;color:#6B7280;margin:2px 0 0">
                                    AWB Tracking Number: <strong style="font-family:monospace;color:#111827">{{ $shipment->awb_number ?: 'Pending Assignment' }}</strong>
                                    @if($shipment->etd)
                                        • Estimated Delivery: <strong>{{ \Carbon\Carbon::parse($shipment->etd)->format('d M Y') }}</strong>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                            <span style="padding:4px 12px;border-radius:99px;font-size:12px;font-weight:800;background:{{ $shipment->status === 'delivered' ? '#D1FAE5' : '#FEF3C7' }};color:{{ $shipment->status === 'delivered' ? '#065F46' : '#92400E' }}">
                                {{ $shipment->formatted_status }}
                            </span>
                            <a href="{{ url('/admin/delivery/shipments/' . $shipment->id . '/print-label') }}" target="_blank" class="btn-outline" style="padding:6px 12px;font-size:12px;border-color:#01684B;color:#01684B">
                                🖨️ Print Label
                            </a>
                            <a href="{{ url('/admin/delivery/shipments/' . $shipment->id . '/print-manifest') }}" target="_blank" class="btn-outline" style="padding:6px 12px;font-size:12px;border-color:#6B7280;color:#374151">
                                📄 Manifest
                            </a>
                        </div>
                    </div>

                    <!-- Tracking Timeline Stepper -->
                    <div style="margin-top:14px">
                        <h4 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:12px">Shipment Activity &amp; Tracking Timeline</h4>
                        <div style="display:flex;flex-direction:column;gap:12px;position:relative;padding-left:24px;border-left:2px solid #E5E7EB">
                            @forelse($shipment->trackingEvents()->orderByDesc('event_time')->take(5)->get() as $evt)
                                <div style="position:relative">
                                    <div style="position:absolute;left:-29px;top:2px;width:12px;height:12px;border-radius:50%;background:#01684B;border:2px solid #fff"></div>
                                    <div style="font-size:13px;font-weight:700;color:#111827">{{ $evt->activity }}</div>
                                    <div style="font-size:11.5px;color:#6B7280">
                                        {{ $evt->location ?: 'Transit Hub' }} • {{ $evt->event_time->format('d M Y, h:i A') }}
                                    </div>
                                </div>
                            @empty
                                <div style="font-size:12.5px;color:#6B7280">
                                    Shipment registered with {{ $shipment->courier_name ?: 'Courier' }}. Live tracking checkpoint updates will appear once picked up from warehouse.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;margin:20px 0">
                <div style="background:var(--bg-body);padding:16px;border-radius:var(--radius-md);border:1px solid var(--border)">
                    <h4 style="font-size:13px;color:var(--text-muted);text-transform:uppercase;margin-bottom:8px">Delivery Address</h4>
                    <p style="font-weight:700">{{ $order->delivery_address['contact_person_name'] ?? $currentUser->name }}</p>
                    <p style="font-size:13px;color:var(--text-secondary)">{{ $order->delivery_address['address'] ?? 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022' }}</p>
                    <p style="font-size:13px;color:var(--text-secondary)">Phone: {{ $order->delivery_address['contact_person_number'] ?? $currentUser->phone }}</p>
                </div>
                <div style="background:var(--bg-body);padding:16px;border-radius:var(--radius-md);border:1px solid var(--border)">
                    <h4 style="font-size:13px;color:var(--text-muted);text-transform:uppercase;margin-bottom:8px">Store &amp; Payment Info</h4>
                    <p style="font-weight:700">Dispatch: Mumbai Head Office (Sion East)</p>
                    <p style="font-size:13px;color:var(--text-secondary)">Payment Method: <strong>{{ $order->isRazorpay() ? 'Razorpay (Online Payment)' : strtoupper(str_replace('_', ' ', $order->payment_method)) }}</strong></p>
                    <p style="font-size:13px;color:var(--text-secondary)">Payment Status: <strong style="text-transform:uppercase;color:{{ $order->payment_status === 'paid' ? '#047857' : 'var(--primary)' }}">{{ $order->payment_status }}</strong></p>
                    @if($order->razorpay_payment_id)
                        <p style="font-size:12.5px;color:var(--text-secondary)">Razorpay Payment ID: <strong style="font-family:monospace;color:#0F172A">{{ $order->razorpay_payment_id }}</strong></p>
                    @endif
                </div>
            </div>

            <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Ordered Items ({{ $order->items->count() }})</h3>
            <div style="border:1px solid var(--border);border-radius:var(--radius-md);overflow:hidden;margin-bottom:20px">
                @foreach($order->items as $item)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border);gap:14px;flex-wrap:wrap">
                        <div style="display:flex;align-items:center;gap:14px">
                            <img src="{{ $item->product_image ? asset('storage/product/' . $item->product_image) : asset('assets/image/placeholder.png') }}" alt="{{ $item->product_name }}" style="width:54px;height:54px;object-fit:contain;background:#f9fafb;border-radius:8px;padding:4px" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                            <div>
                                <a href="{{ url('/product/' . $item->product_id) }}" style="font-weight:700;font-size:14px">{{ $item->product_name }}</a>
                                <div style="font-size:12px;color:var(--text-secondary)">Unit: {{ $item->unit }} • Qty: {{ $item->quantity }}</div>
                            </div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-weight:700;color:var(--primary)">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">₹{{ number_format($item->price, 2) }} × {{ $item->quantity }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="max-width:360px;margin-left:auto">
                <div class="summary-row">
                    <span>Delivery Charge</span>
                    <span>{{ $order->delivery_charge > 0 ? '₹' . number_format($order->delivery_charge, 2) : 'FREE' }}</span>
                </div>
                @if($order->coupon_discount_amount > 0)
                    <div class="summary-row discount">
                        <span>Coupon Discount ({{ $order->coupon_code }})</span>
                        <span>- ₹{{ number_format($order->coupon_discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="summary-row total">
                    <span>Total Paid / Payable</span>
                    <span class="amount">₹{{ number_format($order->order_amount, 2) }}</span>
                </div>
            </div>
        </div>
    @endif
</main>
@endsection
