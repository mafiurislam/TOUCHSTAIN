@extends('layouts.app')

@php
    $firstItem = $order->items->first();
    $prod = $firstItem ? $firstItem->product : null;
    $prodImg = $firstItem ? $firstItem->product_image : null;
    if (!$prodImg && $prod && !empty($prod->images_list)) {
        $prodImg = $prod->images_list[0];
    }
    $imgUrl = \App\Models\Product::getProductImageUrl($prodImg);
    $shipment = $order->shipment;
    $awbNumber = ($shipment && $shipment->awb_number) ? $shipment->awb_number : ('SR' . $order->id);
    $courierName = ($shipment && $shipment->courier_name) ? $shipment->courier_name : 'Shiprocket Express Logistics';
    $trackingStatus = $shipment ? $shipment->formatted_status : (ucfirst($order->order_status ?: 'Confirmed'));

    $statusColors = [
        'confirmed' => ['bg' => '#DCFCE7', 'color' => '#166534', 'text' => 'Order Confirmed'],
        'processing' => ['bg' => '#EFF6FF', 'color' => '#1E40AF', 'text' => 'Packed & Ready'],
        'in_transit' => ['bg' => '#EDE9FE', 'color' => '#6D28D9', 'text' => 'In Transit'],
        'out_for_delivery' => ['bg' => '#FEF3C7', 'color' => '#92400E', 'text' => 'Out for Delivery'],
        'delivered' => ['bg' => '#D1FAE5', 'color' => '#065F46', 'text' => 'Delivered'],
        'canceled' => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'text' => 'Canceled'],
    ];
    $stKey = strtolower($shipment ? ($shipment->status ?: $order->order_status) : $order->order_status);
    $badge = $statusColors[$stKey] ?? ['bg' => '#DCFCE7', 'color' => '#166534', 'text' => 'Order Confirmed'];
@endphp

@section('meta')
    <meta property="og:title" content="Order #ORD-{{ $order->id }} — TOUCHSTAINS Package Tracking">
    <meta property="og:description" content="Verified Authentic Order • Status: {{ $badge['text'] }} • Courier: {{ $courierName }} • AWB: {{ $awbNumber }}">
    <meta property="og:image" content="{{ $imgUrl }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $imgUrl }}">
@endsection

@section('content')
<main class="container" style="max-width:820px;margin:20px auto 40px;padding:0 16px">

    <!-- Top Verified Security Banner -->
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:16px 20px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="background:#047857;color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900">
                ✓
            </div>
            <div>
                <span style="font-size:11px;font-weight:800;color:#047857;text-transform:uppercase;letter-spacing:0.5px">Official Smart QR Verification</span>
                <div style="font-size:18px;font-weight:900;color:#0F172A">Order #ORD-{{ $order->id }}</div>
            </div>
        </div>
        <div>
            <span style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};padding:6px 14px;border-radius:20px;font-size:13px;font-weight:900;display:inline-block">
                ● {{ $badge['text'] }}
            </span>
        </div>
    </div>

    <!-- 1. Ordered Products Section (With Live Database Product Images) -->
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:22px;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #E2E8F0;padding-bottom:12px;margin-bottom:16px">
            <h2 style="font-size:16px;font-weight:900;color:#0F172A;margin:0;display:flex;align-items:center;gap:8px">
                <span>📦</span> Package Contents & Products ({{ $order->items->count() }})
            </h2>
            <span style="font-size:11.5px;color:#64748B">Date: {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : date('d M Y') }}</span>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px">
            @forelse($order->items as $item)
                @php
                    $prodItem = $item->product;
                    $itemImg = $item->product_image;
                    if (!$itemImg && $prodItem && !empty($prodItem->images_list)) {
                        $itemImg = $prodItem->images_list[0];
                    }
                    $itemImgUrl = \App\Models\Product::getProductImageUrl($itemImg);
                    $itemSku = $prodItem && $prodItem->sku ? $prodItem->sku : ('TS-PRD-' . ($item->product_id ?: $item->id));
                @endphp
                <div style="display:flex;gap:16px;align-items:center;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:14px;flex-wrap:wrap">
                    <!-- Product Image -->
                    <div style="width:85px;height:85px;background:#fff;border:1px solid #CBD5E1;border-radius:10px;padding:4px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <img src="{{ $itemImgUrl }}" alt="{{ $item->product_name }}" style="max-width:100%;max-height:100%;object-fit:contain" onerror="this.src='{{ asset('storage/restaurant/touchstains-logo.png') }}'">
                    </div>

                    <!-- Product Details -->
                    <div style="flex:1;min-width:220px">
                        <h3 style="font-size:15px;font-weight:800;color:#0F172A;margin:0 0 4px">
                            {{ $item->product_name }}
                        </h3>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;font-size:11.5px;color:#475569;margin-bottom:6px">
                            <span>Product ID: <strong>PRD-{{ $item->product_id ?: $item->id }}</strong></span>
                            <span>•</span>
                            <span>SKU: <strong style="font-family:monospace">{{ $itemSku }}</strong></span>
                            <span>•</span>
                            <span>Quantity: <strong style="color:#047857">{{ $item->quantity }}</strong></span>
                            <span>•</span>
                            <span>Volume: <strong>{{ $item->unit ?: '250ml Bottle' }}</strong></span>
                        </div>
                        <div style="font-size:16px;font-weight:900;color:#2563EB">
                            ₹{{ number_format(($item->price ?: 0) * ($item->quantity ?: 1), 2) }}
                            <span style="font-size:11.5px;color:#64748B;font-weight:600">
                                (₹{{ number_format($item->price ?: 0, 2) }} × {{ $item->quantity }})
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:20px;color:#64748B">No products in this package</div>
            @endforelse
        </div>

        <!-- Total Breakdown -->
        <div style="border-top:1px solid #E2E8F0;margin-top:16px;padding-top:12px;display:flex;justify-content:flex-end">
            <div style="width:260px;font-size:12.5px">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                    <span style="color:#64748B">Subtotal:</span>
                    <strong>₹{{ number_format($order->subtotal ?: $order->order_amount, 2) }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                    <span style="color:#64748B">Delivery:</span>
                    <strong style="color:#047857">{{ $order->delivery_charge > 0 ? '₹' . number_format($order->delivery_charge, 2) : 'FREE' }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;border-top:1.5px solid #0F172A;padding-top:6px;font-size:15px;font-weight:900;color:#0F172A">
                    <span>Order Total:</span>
                    <span style="color:#2563EB">₹{{ number_format($order->order_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Customer Information Card (Safe & Secure) -->
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:22px;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <h3 style="font-size:15px;font-weight:900;color:#0F172A;border-bottom:1px solid #E2E8F0;padding-bottom:10px;margin:0 0 14px;display:flex;align-items:center;gap:8px">
            <span>👤</span> Customer Information
        </h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;font-size:12.5px">
            <div style="background:#F8FAFC;padding:12px;border-radius:8px">
                <span style="color:#64748B;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">Customer Name</span>
                <strong style="color:#0F172A;font-size:13.5px">{{ $order->getCustomerName() }}</strong>
            </div>

            <div style="background:#F8FAFC;padding:12px;border-radius:8px">
                <span style="color:#64748B;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">Order Number</span>
                <strong style="color:#2563EB;font-size:13.5px">#ORD-{{ $order->id }}</strong>
            </div>

            <div style="background:#F8FAFC;padding:12px;border-radius:8px">
                <span style="color:#64748B;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">Payment Status</span>
                <span style="display:inline-block;padding:2px 8px;border-radius:6px;font-weight:800;font-size:12px;background:{{ $order->payment_status === 'paid' ? '#DCFCE7' : '#FEF3C7' }};color:{{ $order->payment_status === 'paid' ? '#166534' : '#92400E' }}">
                    {{ strtoupper($order->payment_status ?: 'UNPAID') }}
                </span>
                <div style="font-size:11px;color:#64748B;margin-top:2px">{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</div>
            </div>

            <div style="background:#F8FAFC;padding:12px;border-radius:8px">
                <span style="color:#64748B;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">Delivery Status</span>
                <strong style="color:#047857;font-size:13px">{{ strtoupper(str_replace('_', ' ', $order->order_status)) }}</strong>
                <div style="font-size:11px;color:#64748B;margin-top:2px">Destination PIN: {{ $order->getDeliveryPincode() }}</div>
            </div>
        </div>
    </div>

    <!-- 3. Shipping & Courier Information (Shiprocket Integration) -->
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:22px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <h3 style="font-size:15px;font-weight:900;color:#0F172A;border-bottom:1px solid #E2E8F0;padding-bottom:10px;margin:0 0 14px;display:flex;align-items:center;gap:8px">
            <span>🚚</span> Shipping & Logistics Information
        </h3>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;font-size:12.5px;margin-bottom:18px">
            <div style="background:#F0FDF4;border:1px solid #BBF7D0;padding:12px;border-radius:8px">
                <span style="color:#166534;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">Courier Partner</span>
                <strong style="color:#065F46;font-size:14px">🚚 {{ $courierName }}</strong>
            </div>

            <div style="background:#F8FAFC;border:1px solid #E2E8F0;padding:12px;border-radius:8px">
                <span style="color:#64748B;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">AWB Number</span>
                <strong style="color:#0F172A;font-family:monospace;font-size:14px">{{ $awbNumber }}</strong>
            </div>

            <div style="background:#EFF6FF;border:1px solid #BFDBFE;padding:12px;border-radius:8px">
                <span style="color:#1E40AF;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">Tracking Status</span>
                <strong style="color:#1E3A8A;font-size:13.5px">{{ $trackingStatus }}</strong>
            </div>
        </div>

        <!-- 4-Step Live Tracking Progress Timeline -->
        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:16px">
            <div style="display:flex;justify-content:space-between;align-items:center;position:relative;flex-wrap:wrap;gap:10px">
                <div style="text-align:center;flex:1;min-width:70px">
                    <div style="width:28px;height:28px;background:#047857;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:12px;font-weight:900">✓</div>
                    <span style="font-size:10.5px;font-weight:700;color:#0F172A">Placed</span>
                </div>
                <div style="text-align:center;flex:1;min-width:70px">
                    <div style="width:28px;height:28px;background:#047857;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:12px;font-weight:900">✓</div>
                    <span style="font-size:10.5px;font-weight:700;color:#0F172A">Confirmed</span>
                </div>
                <div style="text-align:center;flex:1;min-width:70px">
                    <div style="width:28px;height:28px;background:{{ in_array($stKey, ['in_transit', 'out_for_delivery', 'delivered']) ? '#047857' : '#94A3B8' }};color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:12px;font-weight:900">
                        {{ in_array($stKey, ['in_transit', 'out_for_delivery', 'delivered']) ? '✓' : '3' }}
                    </div>
                    <span style="font-size:10.5px;font-weight:700;color:#0F172A">In Transit</span>
                </div>
                <div style="text-align:center;flex:1;min-width:70px">
                    <div style="width:28px;height:28px;background:{{ $stKey === 'delivered' ? '#047857' : '#94A3B8' }};color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:12px;font-weight:900">
                        {{ $stKey === 'delivered' ? '✓' : '4' }}
                    </div>
                    <span style="font-size:10.5px;font-weight:700;color:#0F172A">Delivered</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Helpline & Tracking Buttons -->
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="https://wa.me/917679416294?text=Hi%20Touchstains%2C%20I%20scanned%20Order%20ORD-{{ $order->id }}" target="_blank" style="background:#25D366;color:#fff;padding:12px 22px;border-radius:8px;font-size:13.5px;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;gap:8px;box-shadow:0 2px 8px rgba(37,211,102,0.3)">
            <span>💬</span> WhatsApp Help & Support
        </a>
        <a href="{{ url('/track-order?id=' . $order->id) }}" class="btn-primary" style="padding:12px 22px;font-size:13.5px">
            🚚 Live Track Package
        </a>
        <a href="{{ url('/shop') }}" class="btn-outline" style="padding:12px 22px;font-size:13.5px">
            🛍️ Visit Store
        </a>
    </div>

</main>
@endsection
