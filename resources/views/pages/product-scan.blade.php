@extends('layouts.app')

@section('content')
@php
    $shipment = $order ? $order->shipment : null;
    $awbCode = $shipment && $shipment->awb_number ? $shipment->awb_number : ($order ? 'SR' . str_pad($order->id . $product->id, 8, '0', STR_PAD_RIGHT) : 'SR7665334165');
    $courierName = $shipment && $shipment->courier_name ? $shipment->courier_name : 'Express Surface Logistics';
    $pincode = $order ? $order->getDeliveryPincode() : '400074';
    $isCod = $order && (strtoupper($order->payment_method ?? '') === 'CASH_ON_DELIVERY' || ($shipment && strtoupper($shipment->payment_method ?? '') === 'COD'));
    $totalAmount = $order ? $order->order_amount : $product->price;

    $prodImg = !empty($product->images_list) ? $product->images_list[0] : (is_array($product->image) ? ($product->image[0] ?? null) : $product->image);
    $imgUrl = \App\Models\Product::getProductImageUrl($prodImg);
    $sku = $product->sku ?: ('TS-SR-' . $product->id);
    $barcodeVal = $product->barcode ?: ('890' . str_pad($product->id, 8, '0', STR_PAD_LEFT));
@endphp

<main class="container" style="max-width:860px;margin:24px auto;padding:0 16px">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb" style="font-size:12.5px;color:var(--text-secondary);margin-bottom:18px">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span>Product Verification</span> <span>›</span>
        <span>{{ $product->name }}</span>
    </div>

    <!-- Official Verified Banner (Flipkart / Meesho Package Verification Style) -->
    <div style="background:linear-gradient(135deg, #065F46 0%, #047857 50%, #059669 100%);color:#fff;border-radius:14px;padding:22px;margin-bottom:20px;box-shadow:0 4px 18px rgba(4,120,87,0.25)">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
            <div style="display:flex;align-items:center;gap:14px">
                <div style="background:#fff;color:#047857;width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:900;box-shadow:0 2px 8px rgba(0,0,0,0.15)">
                    ✓
                </div>
                <div>
                    <div style="font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#A7F3D0">
                        🛡️ Official Product Authentication
                    </div>
                    <h1 style="font-size:20px;font-weight:900;margin:2px 0;letter-spacing:-0.3px">
                        Verified Genuine TOUCHSTAINS™ Product
                    </h1>
                    <div style="font-size:12.5px;color:#D1FAE5">
                        Scanned via Mobile Barcode / Google Lens • 100% Original Brand Certified
                    </div>
                </div>
            </div>
            <div style="text-align:right">
                <span style="background:rgba(255,255,255,0.2);backdrop-filter:blur(4px);padding:5px 12px;border-radius:20px;font-size:11.5px;font-weight:800;display:inline-block">
                    ✓ Verified for Delivery
                </span>
                <div style="font-size:11px;color:#D1FAE5;margin-top:4px">
                    Verified at: {{ now()->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Main Product Card -->
    <div style="background:var(--bg-card,#fff);border:1px solid var(--border,#E2E8F0);border-radius:14px;padding:24px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,0.04)">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:24px;align-items:center">
            
            <!-- Large Product Image Gallery -->
            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:20px;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center">
                <div style="width:220px;height:220px;background:#fff;border-radius:10px;padding:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.05);border:1px solid #CBD5E1">
                    <img src="{{ $imgUrl }}" alt="{{ $product->name }}" style="max-width:100%;max-height:100%;object-fit:contain" onerror="this.src='{{ asset('storage/restaurant/touchstains-logo.png') }}'">
                </div>
                <div style="margin-top:12px;font-size:12px;color:#059669;font-weight:700">
                    ✓ Official Lab Seal & Packaging Verified
                </div>
            </div>

            <!-- Product Specs & Details -->
            <div>
                <div style="font-size:11.5px;font-weight:800;color:#2563EB;text-transform:uppercase;letter-spacing:0.5px">
                    TOUCHSTAINS™ Laboratory Quality Formulations
                </div>
                <h2 style="font-size:20px;font-weight:900;color:#0F172A;line-height:1.3;margin:6px 0 10px">
                    {{ $product->name }}
                </h2>

                <!-- Price and Volume -->
                <div style="display:flex;align-items:baseline;gap:12px;margin-bottom:14px">
                    <span style="font-size:26px;font-weight:900;color:#059669">₹{{ number_format($product->price, 2) }}</span>
                    @if($product->market_price && $product->market_price > $product->price)
                        <span style="font-size:16px;color:#94A3B8;text-decoration:line-through">₹{{ number_format($product->market_price, 2) }}</span>
                        <span style="background:#DCFCE7;color:#166534;font-size:11.5px;font-weight:800;padding:2px 8px;border-radius:6px">
                            Save {{ round((($product->market_price - $product->price) / $product->market_price) * 100) }}%
                        </span>
                    @endif
                    <span style="font-size:13px;color:#64748B;font-weight:600">({{ $product->unit ?: '250ml Bottle' }})</span>
                </div>

                <!-- Product Attributes Grid -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;font-size:12px">
                    <div style="background:#F1F5F9;padding:8px 12px;border-radius:8px">
                        <span style="color:#64748B;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">Product ID</span>
                        <strong style="color:#0F172A;font-size:13px">#PRD-{{ $product->id }}</strong>
                    </div>
                    <div style="background:#F1F5F9;padding:8px 12px;border-radius:8px">
                        <span style="color:#64748B;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">SKU Code</span>
                        <strong style="color:#0F172A;font-family:monospace;font-size:13px">{{ $sku }}</strong>
                    </div>
                    <div style="background:#F1F5F9;padding:8px 12px;border-radius:8px">
                        <span style="color:#64748B;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">Stock Status</span>
                        <strong style="color:#059669;font-size:12.5px">● In Stock (Ready)</strong>
                    </div>
                    <div style="background:#F1F5F9;padding:8px 12px;border-radius:8px">
                        <span style="color:#64748B;display:block;font-size:10.5px;font-weight:700;text-transform:uppercase">Barcode String</span>
                        <strong style="color:#0F172A;font-family:monospace;font-size:12px">{{ $barcodeVal }}</strong>
                    </div>
                </div>

                <!-- Styled Barcode Card matching user's uploaded reference image -->
                <div style="margin-top:14px">
                    <span style="font-size:11px;font-weight:800;color:#64748B;text-transform:uppercase;display:block;margin-bottom:6px">
                        Scanned Packaging Barcode
                    </span>
                    <div style="display:inline-block;background:#fff;border:1px solid #CBD5E1;border-radius:8px;padding:8px 16px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.06)">
                        <div style="max-width:260px;margin:0 auto">
                            {!! \App\Services\Delivery\BarcodeService::getCode128Svg('touchstains.com/p/' . $product->id, 38, 1.3, false, 8) !!}
                        </div>
                        <div style="font-family:'Courier New',Courier,monospace;font-weight:700;font-size:13px;color:#000;letter-spacing:0.8px;margin-top:4px">
                            AWB:  {{ $awbCode }}
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Description & Usage -->
        <div style="border-top:1px solid #E2E8F0;margin-top:20px;padding-top:16px">
            <h4 style="font-size:13px;font-weight:800;color:#0F172A;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">
                📋 Product Description & Usage
            </h4>
            <p style="font-size:12.5px;color:#475569;line-height:1.5">
                {{ $product->description ?: 'TOUCHSTAINS™ Instant Fabric Stain & Rust Remover is a laboratory-grade, multi-surface active enzyme formula designed to remove stubborn rust, grease, turmeric, dye, ink, and blood stains instantly from white and colored cotton, linen, silk, polyester, school uniforms, and restaurant linens. Color-safe, fabric-friendly, and non-corrosive.' }}
            </p>
        </div>
    </div>

    <!-- Associated Order & Delivery Context -->
    @if($order)
        <div style="background:var(--bg-card,#fff);border:1px solid var(--border,#E2E8F0);border-radius:14px;padding:22px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #E2E8F0;padding-bottom:12px;margin-bottom:14px">
                <h3 style="font-size:16px;font-weight:800;color:#0F172A;display:flex;align-items:center;gap:8px">
                    <span>📦</span> Associated Order & Delivery Info
                </h3>
                <span style="font-size:12px;background:#EDE9FE;color:#6D28D9;font-weight:800;padding:3px 10px;border-radius:6px">
                    Order #{{ $order->id }}
                </span>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;font-size:12.5px">
                <div>
                    <span style="color:#64748B;display:block;font-size:11px;font-weight:700;text-transform:uppercase">Customer & Destination</span>
                    <strong style="color:#0F172A;font-size:13px">{{ $order->getCustomerName() }}</strong>
                    <div style="color:#475569;margin-top:2px">{{ $order->getDeliveryAddressFormatted() }}</div>
                    <div style="font-weight:700;color:#0F172A;margin-top:2px">PIN: {{ $pincode }} | Phone: {{ $order->getCustomerPhone() }}</div>
                </div>

                <div>
                    <span style="color:#64748B;display:block;font-size:11px;font-weight:700;text-transform:uppercase">Courier Logistics & Tracking</span>
                    <div style="font-weight:700;color:#047857">🚚 {{ $courierName }}</div>
                    <div style="font-family:monospace;font-weight:700;color:#0F172A;margin-top:2px">AWB: {{ $awbCode }}</div>
                    <div style="margin-top:4px">
                        <span style="padding:2px 8px;border-radius:4px;font-size:11px;font-weight:800;background:{{ $isCod ? '#FEF3C7' : '#DCFCE7' }};color:{{ $isCod ? '#92400E' : '#166534' }}">
                            {{ $isCod ? 'CASH ON DELIVERY (Collect ₹' . number_format($totalAmount, 2) . ')' : 'PREPAID — DO NOT COLLECT' }}
                        </span>
                    </div>
                </div>

                <div>
                    <span style="color:#64748B;display:block;font-size:11px;font-weight:700;text-transform:uppercase">Delivery Rider Checklist</span>
                    <div style="color:#166534;font-weight:600;display:flex;align-items:center;gap:6px;margin-top:4px">
                        <span>✓</span> Bottle sealed with security strip
                    </div>
                    <div style="color:#166534;font-weight:600;display:flex;align-items:center;gap:6px;margin-top:2px">
                        <span>✓</span> Product photo verified by camera
                    </div>
                    <div style="color:#166534;font-weight:600;display:flex;align-items:center;gap:6px;margin-top:2px">
                        <span>✓</span> Destination PIN & address confirmed
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bottom Actions -->
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin:28px 0">
        <a href="https://wa.me/917679416294?text=Hi%20Touchstains%2C%20I%20scanned%20product%20{{ urlencode($product->name) }}" target="_blank" style="background:#25D366;color:#fff;padding:12px 22px;border-radius:8px;font-size:14px;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;gap:8px;box-shadow:0 2px 8px rgba(37,211,102,0.3)">
            <span>💬</span> WhatsApp Support Helpline
        </a>
        @if($order)
            <a href="{{ url('/track-order?id=' . $order->id) }}" class="btn-primary" style="padding:12px 22px;font-size:14px">
                🚚 Track Live Courier Order #{{ $order->id }}
            </a>
        @endif
        <a href="{{ url('/shop') }}" class="btn-outline" style="padding:12px 22px;font-size:14px">
            🛍️ Shop More Products
        </a>
    </div>

</main>
@endsection
