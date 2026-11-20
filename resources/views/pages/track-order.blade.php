@extends('layouts.app')

@section('content')
@php
    $shipment = $order ? $order->shipment : null;
    $statusOrder = [
        'pending' => 1,
        'confirmed' => 2,
        'processing' => 3,
        'out_for_delivery' => 4,
        'delivered' => 5
    ];

    if ($shipment) {
        $shipStatusSteps = [
            'pending' => 2,
            'awb_assigned' => 2,
            'pickup_scheduled' => 3,
            'picked_up' => 3,
            'in_transit' => 3,
            'reached_hub' => 3,
            'out_for_delivery' => 4,
            'delivered' => 5,
        ];
        $currentStep = $shipStatusSteps[$shipment->status] ?? 2;
    } else {
        $currentStep = $order ? ($statusOrder[$order->order_status] ?? 2) : 2;
    }
@endphp
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="track_order">Track Order</span>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;margin-bottom:24px">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;margin-bottom:24px">
            <div>
                <h1 style="font-size:22px;font-weight:800" data-i18n="track_order">Live Order Tracking</h1>
                <p style="font-size:13px;color:var(--text-secondary)">Tracking Order <strong>#{{ $order->id ?? '100248' }}</strong> from <strong>TOUCHSTAINS Logistics Center</strong></p>
            </div>
            <form action="{{ url('/track-order') }}" method="GET" style="display:flex;gap:8px">
                <input type="text" name="id" value="{{ request('id', $order->id ?? '100248') }}" class="form-control" placeholder="Enter Order ID (e.g. 100248)" style="width:200px">
                <button type="submit" class="btn-primary">Track</button>
            </form>
        </div>

        @if($shipment)
            <!-- Live Shiprocket Logistics Bar -->
            <div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
                <div style="display:flex;align-items:center;gap:12px">
                    <span style="font-size:24px">🚚</span>
                    <div>
                        <div style="font-size:14px;font-weight:800;color:#166534">
                            Dispatched via {{ $shipment->courier_name ?: 'Shiprocket Express Logistics' }}
                        </div>
                        <div style="font-size:12px;color:#4B5563;margin-top:2px">
                            AWB Tracking Code: <strong style="font-family:monospace;color:#111827">{{ $shipment->awb_number ?: 'Pending assignment' }}</strong>
                            @if($shipment->etd)
                                • Expected Delivery: <strong>{{ \Carbon\Carbon::parse($shipment->etd)->format('D, d M Y') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="padding:4px 12px;border-radius:99px;font-size:12px;font-weight:800;background:{{ $shipment->status === 'delivered' ? '#D1FAE5' : '#FEF3C7' }};color:{{ $shipment->status === 'delivered' ? '#065F46' : '#92400E' }}">
                        {{ $shipment->formatted_status }}
                    </span>
                    @if($shipment->tracking_url)
                        <a href="{{ $shipment->tracking_url }}" target="_blank" class="btn-outline" style="font-size:12px;padding:6px 12px;background:#fff">
                            🌐 Live Partner Portal ↗
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- 5-Step Progress Tracker -->
        <div class="tracking-stepper">
            <div class="track-step {{ $currentStep >= 1 ? ($currentStep > 1 ? 'done' : 'active') : '' }}">
                <div class="step-circle"><img src="{{ asset('assets/image/order_placed.png') }}" alt="Placed"></div>
                <h4>Order Placed</h4>
                <p>Received at Hub</p>
            </div>
            <div class="track-step {{ $currentStep >= 2 ? ($currentStep > 2 ? 'done' : 'active') : '' }}">
                <div class="step-circle">✓</div>
                <h4>Order Confirmed</h4>
                <p>Verified by Store</p>
            </div>
            <div class="track-step {{ $currentStep >= 3 ? ($currentStep > 3 ? 'done' : 'active') : '' }}">
                <div class="step-circle">📦</div>
                <h4>Packed & Handed Over</h4>
                <p>Courier Dispatched</p>
            </div>
            <div class="track-step {{ $currentStep >= 4 ? ($currentStep > 4 ? 'done' : 'active') : '' }}">
                <div class="step-circle"><img src="{{ asset('assets/image/out_for_delivery.png') }}" alt="Out" onerror="this.style.display='none'">🚚</div>
                <h4>Out for Delivery</h4>
                <p>In Transit to You</p>
            </div>
            <div class="track-step {{ $currentStep >= 5 ? 'done' : '' }}">
                <div class="step-circle">🏠</div>
                <h4>Delivered</h4>
                <p>Destination Reached</p>
            </div>
        </div>

        @if($shipment && $shipment->trackingEvents && count($shipment->trackingEvents) > 0)
            <!-- Live Checkpoints Timeline -->
            <div style="background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:20px;margin-top:24px">
                <h3 style="font-size:15px;font-weight:800;color:#111827;margin:0 0 16px;border-bottom:1px solid #F3F4F6;padding-bottom:10px">
                    📍 Live Transit Checkpoints
                </h3>
                <div style="display:flex;flex-direction:column;gap:14px;position:relative;padding-left:24px;border-left:2px solid #01684B">
                    @foreach($shipment->trackingEvents()->orderByDesc('event_time')->get() as $event)
                        <div style="position:relative">
                            <div style="position:absolute;left:-29px;top:2px;width:12px;height:12px;border-radius:50%;background:#01684B;border:2px solid #fff"></div>
                            <div style="font-size:13.5px;font-weight:700;color:#111827">{{ $event->activity }}</div>
                            <div style="font-size:12px;color:#6B7280;margin-top:2px">
                                {{ $event->location ?: 'Central Logistics Hub' }} • {{ $event->event_time->format('d M Y, h:i A') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:24px">
            <div style="background:var(--primary-lighter);border:1px solid var(--primary);border-radius:var(--radius-md);padding:18px;display:flex;align-items:center;gap:14px">
                <img src="{{ asset('assets/image/delivery_boy_marker.png') }}" alt="Delivery Partner" style="width:52px;height:52px;object-fit:contain">
                <div>
                    <span style="font-size:11px;font-weight:700;color:var(--primary);text-transform:uppercase">TOUCHSTAINS Express Shipping</span>
                    <h4 style="font-size:15px;font-weight:700">PAN India Delivery Network</h4>
                    <p style="font-size:12px;color:var(--text-secondary)">Support Helpline: <a href="tel:+917679416294" style="color:var(--primary);font-weight:700">+91 7679416294</a> / <a href="tel:+917039214465" style="color:var(--primary);font-weight:700">7039214465</a></p>
                </div>
            </div>
            <div style="background:var(--bg-body);border:1px solid var(--border);border-radius:var(--radius-md);padding:18px;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h4 style="font-size:14px;font-weight:700">Order Total: ₹{{ number_format($order->order_amount ?? 185, 2) }}</h4>
                    <p style="font-size:12px;color:var(--text-secondary)">Payment: {{ strtoupper(str_replace('_', ' ', $order->payment_method ?? 'COD')) }}</p>
                </div>
                <a href="{{ url('/order-details?id=' . ($order->id ?? 100248)) }}" class="btn-outline">View Full Invoice</a>
            </div>
        </div>
    </div>
</main>
@endsection

