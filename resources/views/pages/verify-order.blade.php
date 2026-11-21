@extends('layouts.app')

@section('content')
@php
    $shipment = $order ? $order->shipment : null;
    $awbCode = $shipment && $shipment->awb_number ? $shipment->awb_number : ($order ? 'TS-ORD-' . $order->id : 'PENDING');
    $courierName = $shipment && $shipment->courier_name ? $shipment->courier_name : 'Express Standard Logistics';
    $pincode = $order ? $order->getDeliveryPincode() : '400074';
    $isCod = $order && (strtoupper($order->payment_method ?? '') === 'CASH_ON_DELIVERY' || ($shipment && strtoupper($shipment->payment_method ?? '') === 'COD'));
    $totalAmount = $order ? $order->order_amount : 0;

    $shipStatusSteps = [
        'pending' => 1,
        'confirmed' => 1,
        'processing' => 2,
        'awb_assigned' => 2,
        'pickup_scheduled' => 2,
        'picked_up' => 3,
        'in_transit' => 3,
        'reached_hub' => 3,
        'out_for_delivery' => 4,
        'delivered' => 5,
    ];
    $currentStep = $shipment ? ($shipStatusSteps[$shipment->status] ?? 2) : ($order ? ($shipStatusSteps[$order->order_status] ?? 1) : 1);
@endphp

<main class="container" style="max-width:920px;margin:24px auto;padding:0 16px">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb" style="font-size:12.5px;color:var(--text-secondary);margin-bottom:18px">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span>Package Verification</span> <span>›</span>
        <span>#ORD-{{ $order ? $order->id : 'NA' }}</span>
    </div>

    @if(!$order)
        <div style="background:#FEF2F2;border:1px solid #FCA5A5;border-radius:12px;padding:32px;text-align:center;margin-bottom:30px">
            <div style="font-size:42px;margin-bottom:10px">⚠️</div>
            <h2 style="font-size:20px;font-weight:800;color:#991B1B">Shipment Record Not Found</h2>
            <p style="font-size:14px;color:#7F1D1D;margin-top:6px">The scanned barcode or order reference does not match any active shipment record.</p>
            <a href="{{ url('/') }}" class="btn-primary" style="display:inline-block;margin-top:16px">Return to Home</a>
        </div>
    @else
        <!-- Verified Genuine Header Banner (Flipkart / Amazon Style) -->
        <div style="background:linear-gradient(135deg, #065F46 0%, #047857 50%, #059669 100%);color:#fff;border-radius:14px;padding:24px;margin-bottom:22px;box-shadow:0 4px 18px rgba(4,120,87,0.25)">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
                <div style="display:flex;align-items:center;gap:14px">
                    <div style="background:#fff;color:#047857;width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;box-shadow:0 2px 8px rgba(0,0,0,0.15)">
                        ✓
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#A7F3D0">
                            🛡️ Official Shipment Authentication
                        </div>
                        <h1 style="font-size:22px;font-weight:900;margin:2px 0;letter-spacing:-0.3px">
                            Verified Genuine Package
                        </h1>
                        <div style="font-size:12.5px;color:#D1FAE5">
                            Order <strong>#ORD-{{ $order->id }}</strong> • Scanned via Official Barcode / QR
                        </div>
                    </div>
                </div>
                <div style="text-align:right">
                    <span style="background:rgba(255,255,255,0.2);backdrop-filter:blur(4px);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:800;display:inline-block">
                        100% Original Brand Certified
                    </span>
                    <div style="font-size:11px;color:#D1FAE5;margin-top:6px">
                        Verified at: {{ now()->format('d M Y, h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Ordered Products Section (Flipkart Style Cards) -->
        <div style="background:var(--bg-card,#fff);border:1px solid var(--border,#E2E8F0);border-radius:14px;padding:22px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border,#E2E8F0);padding-bottom:12px;margin-bottom:16px">
                <h2 style="font-size:17px;font-weight:800;color:var(--text-primary,#0F172A);display:flex;align-items:center;gap:8px">
                    <span>📦</span> Package Contents & Product Details
                </h2>
                <span style="font-size:12px;color:var(--text-muted,#64748B);font-weight:700">
                    {{ count($order->items) }} Item(s) Verified
                </span>
            </div>

            <div style="display:flex;flex-direction:column;gap:16px">
                @forelse($order->items as $item)
                    @php
                        $prod = $item->product;
                        $prodImg = $item->product_image;
                        if (!$prodImg && $prod && !empty($prod->images_list)) {
                            $prodImg = $prod->images_list[0];
                        }
                        $imgUrl = \App\Models\Product::getProductImageUrl($prodImg);
                        $sku = $prod && $prod->sku ? $prod->sku : ('TS-SR-' . ($item->product_id ?: $item->id));
                        $barcode = $prod && $prod->barcode ? $prod->barcode : ('890' . str_pad($item->product_id ?: $item->id, 8, '0', STR_PAD_LEFT));
                    @endphp
                    <div style="display:flex;gap:18px;align-items:flex-start;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:16px;flex-wrap:wrap">
                        <!-- Product Thumbnail -->
                        <div style="width:100px;height:100px;background:#fff;border:1px solid #CBD5E1;border-radius:10px;padding:4px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:6px" onerror="this.src='{{ asset('storage/restaurant/touchstains-logo.png') }}'">
                        </div>

                        <!-- Product Info (Flipkart Style) -->
                        <div style="flex:1;min-width:240px">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
                                <div>
                                    <h3 style="font-size:16px;font-weight:800;color:#0F172A;line-height:1.3;margin-bottom:4px">
                                        {{ $item->product_name }}
                                    </h3>
                                    <div style="font-size:12px;color:#059669;font-weight:700;margin-bottom:6px">
                                        TOUCHSTAINS™ Official Lab Formulation
                                    </div>
                                </div>
                                <div style="text-align:right">
                                    <div style="font-size:18px;font-weight:900;color:#2563EB">
                                        ₹{{ number_format(($item->price ?: 0) * ($item->quantity ?: 1), 2) }}
                                    </div>
                                    <div style="font-size:11.5px;color:#64748B">
                                        ₹{{ number_format($item->price ?: 0, 2) }} x {{ $item->quantity ?: 1 }} unit(s)
                                    </div>
                                </div>
                            </div>

                            <!-- Product Specs & Identifiers -->
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;font-size:11.5px">
                                <span style="background:#fff;border:1px solid #CBD5E1;padding:3px 8px;border-radius:6px;font-weight:700;color:#334155">
                                    🆔 Product ID: <strong>#{{ $item->product_id ?: '1' }}</strong>
                                </span>
                                <span style="background:#fff;border:1px solid #CBD5E1;padding:3px 8px;border-radius:6px;font-weight:700;color:#334155">
                                    🏷️ SKU: <strong>{{ $sku }}</strong>
                                </span>
                                <span style="background:#fff;border:1px solid #CBD5E1;padding:3px 8px;border-radius:6px;font-weight:700;color:#334155">
                                    📊 Barcode: <strong>{{ $barcode }}</strong>
                                </span>
                                <span style="background:#EDE9FE;border:1px solid #DDD6FE;padding:3px 8px;border-radius:6px;font-weight:700;color:#6D28D9">
                                    💧 Volume: {{ $item->unit ?: '250ml Bottle' }}
                                </span>
                            </div>

                            <p style="font-size:12px;color:#475569;margin-top:8px;line-height:1.4">
                                Premium instant stain & rust remover for cotton, polyester, uniform fabrics, and delicate garments. Fast acting, color safe, enzyme powered.
                            </p>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:20px;color:#64748B">
                        Touchstains Fabric Stain & Rust Remover (250ml)
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Grid: Order Summary & Logistics Details -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;margin-bottom:22px">
            
            <!-- Logistics & Courier Card -->
            <div style="background:var(--bg-card,#fff);border:1px solid var(--border,#E2E8F0);border-radius:14px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
                <h3 style="font-size:15px;font-weight:800;color:#0F172A;margin-bottom:14px;display:flex;align-items:center;gap:8px">
                    <span>🚚</span> Delivery & Courier Partner
                </h3>
                
                <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#64748B">Courier Partner:</span>
                        <strong style="color:#0F172A">{{ $courierName }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span style="color:#64748B">AWB Tracking #:</span>
                        <strong style="font-family:monospace;font-size:14px;color:#2563EB">{{ $awbCode }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#64748B">Destination PIN:</span>
                        <strong style="font-size:15px;color:#059669;letter-spacing:1px">{{ $pincode }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#64748B">Dispatch Center:</span>
                        <span>Mumbai Hub (400074)</span>
                    </div>
                </div>

                <!-- Live Track Button -->
                <div style="margin-top:16px;padding-top:14px;border-top:1px dashed #E2E8F0">
                    <a href="https://shiprocket.co/tracking/{{ $awbCode }}" target="_blank" class="btn-primary" style="display:block;text-align:center;padding:10px;border-radius:8px;font-size:13px;font-weight:700">
                        Live Tracking on Courier ↗
                    </a>
                </div>
            </div>

            <!-- Order & Payment Summary Card -->
            <div style="background:var(--bg-card,#fff);border:1px solid var(--border,#E2E8F0);border-radius:14px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
                <h3 style="font-size:15px;font-weight:800;color:#0F172A;margin-bottom:14px;display:flex;align-items:center;gap:8px">
                    <span>🧾</span> Order & Payment Info
                </h3>

                <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#64748B">Order ID:</span>
                        <strong style="color:#0F172A">#ORD-{{ $order->id }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#64748B">Order Date:</span>
                        <span>{{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : date('d M Y') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span style="color:#64748B">Payment Method:</span>
                        @if($isCod)
                            <span style="background:#FEF2F2;color:#DC2626;border:1px solid #FCA5A5;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:800">
                                CASH ON DELIVERY (COD)
                            </span>
                        @else
                            <span style="background:#ECFDF5;color:#059669;border:1px solid #A7F3D0;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:800">
                                PREPAID (ONLINE)
                            </span>
                        @endif
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-top:8px;border-top:1px solid #E2E8F0">
                        <span style="font-weight:800;color:#0F172A">Total Order Amount:</span>
                        <span style="font-size:18px;font-weight:900;color:#047857">₹{{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>

                <!-- Recipient Delivery Address -->
                <div style="margin-top:14px;padding:12px;background:#F8FAFC;border-radius:8px;font-size:12px;line-height:1.4">
                    <span style="color:#64748B;font-weight:700;display:block;margin-bottom:2px">DELIVERED TO:</span>
                    <strong style="color:#0F172A;font-size:13px">{{ $order->getCustomerName() }}</strong>
                    <div style="color:#475569">{{ $order->getDeliveryAddressFormatted() }}</div>
                    <div style="color:#0F172A;font-weight:700;margin-top:3px">Phone: {{ $order->getCustomerPhone() }}</div>
                </div>
            </div>

        </div>

        <!-- Helpdesk & Customer Assurance Bar -->
        <div style="background:#F1F5F9;border:1px solid #CBD5E1;border-radius:12px;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
            <div>
                <div style="font-size:14px;font-weight:800;color:#0F172A">Need assistance with your package or return?</div>
                <div style="font-size:12.5px;color:#64748B">Direct customer support helpline: <strong>+91 7679416294</strong> / <strong>7039214465</strong></div>
            </div>
            <div style="display:flex;gap:10px">
                <a href="https://wa.me/917679416294?text={{ urlencode('Hello TOUCHSTAINS, I am inquiring about Order #' . $order->id) }}" target="_blank" style="background:#25D366;color:#fff;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                    WhatsApp Support
                </a>
                <a href="{{ url('/shop') }}" style="background:#0F172A;color:#fff;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:700;text-decoration:none">
                    Shop More ↗
                </a>
            </div>
        </div>

    @endif

</main>
@endsection
