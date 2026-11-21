<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $awbCode = $shipment->awb_number ?: ('SR' . ($shipment->order_id ?? rand(10000000, 99999999)));
        $order = $shipment->order;
        $orderId = $shipment->order_id ?: ($order ? $order->id : '1');
        $pincode = $order ? $order->getDeliveryPincode() : ($settings->warehouse_pincode ?: '400074');
        $customerName = $order ? $order->getCustomerName() : 'Customer';
        $customerPhone = $order ? $order->getCustomerPhone() : 'N/A';
        $customerAddress = $order ? $order->getDeliveryAddressFormatted() : 'Delivery Address';
        $isCod = strtoupper($shipment->payment_method ?? '') === 'COD' || ($shipment->cod_amount > 0) || ($order && strtoupper($order->payment_method ?? '') === 'CASH_ON_DELIVERY');
        $collectAmount = $shipment->cod_amount ?: ($order ? $order->order_amount : 0);
        $courierName = $shipment->courier_name ?: 'DELHIVERY SURFACE';

        // Base domain resolution: If on live domain, use current host; if on localhost, use live domain so phone scans always work!
        $liveDomain = (!empty(request()->getHost()) && !in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1']))
            ? request()->getSchemeAndHttpHost()
            : 'https://touchstains.com';
        $verifyUrl = "{$liveDomain}/verify-order/{$orderId}";

        // Short URL for 1D Barcode (e.g. touchstains.com/v/1) so Google Lens immediately opens product details
        $shortHost = (!empty(request()->getHost()) && !in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1']))
            ? (request()->getHost() . '/v/' . $orderId)
            : ("touchstains.com/v/{$orderId}");

        $barcodeMode = request('barcode_type', 'smart');
        $barcodeContent = ($barcodeMode === 'awb') ? $awbCode : $shortHost;
    @endphp
    <title>Shipping Label — AWB: {{ $awbCode }} — Order #{{ $orderId }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { background: #f0f2f5; color: #000; padding: 20px; display: flex; flex-direction: column; align-items: center; }

        /* Top Toolbar */
        .no-print-toolbar {
            background: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 16px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            max-width: 100mm;
            width: 100%;
        }
        .btn-print {
            background: #01684B;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-carrier-pdf {
            background: #2563EB;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-toggle {
            background: #F1F5F9;
            color: #334155;
            border: 1px solid #CBD5E1;
            padding: 7px 11px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            text-decoration: none;
        }
        .btn-close {
            background: #e2e8f0;
            color: #475569;
            border: none;
            padding: 7px 11px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        /* 4x6 inch (approx 100mm x 150mm) Original Clean Thermal Label Container */
        .shipping-label {
            width: 100mm;
            min-height: 146mm;
            max-height: 148mm;
            background: #fff;
            border: 2px solid #000;
            padding: 5mm;
            box-shadow: 0 4px 14px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        /* 1. Header (Original Version) */
        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .label-header .brand {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.5px;
        }
        .label-header .courier {
            font-size: 14px;
            font-weight: 800;
            text-align: right;
            text-transform: uppercase;
        }

        /* 2. Barcode & AWB Row (Separated: Left Barcode, Right Small QR Code) */
        .barcode-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 0 5px;
            border-bottom: 2px solid #000;
            gap: 10px;
        }
        .barcode-left-box {
            flex: 1;
            text-align: left;
        }
        .barcode-svg-wrapper {
            max-width: 210px;
            margin: 0;
            overflow: hidden;
        }
        .barcode-svg-wrapper svg {
            width: 100% !important;
            height: 35px !important;
            display: block;
            shape-rendering: crispEdges !important;
        }
        .awb-code {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 1px;
            font-family: 'Courier New', Courier, monospace;
            margin-top: 2px;
        }

        /* Right Side: Small, Separated Smart QR Code */
        .barcode-right-box {
            width: 58px;
            text-align: center;
            border-left: 1.5px dashed #444;
            padding-left: 8px;
            flex-shrink: 0;
        }
        .barcode-right-box img {
            width: 44px;
            height: 44px;
            display: block;
            margin: 0 auto;
        }
        .qr-subtext {
            font-size: 6.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #047857;
            margin-top: 1px;
            display: block;
            line-height: 1.1;
        }

        /* 3. Routing & Payment Block (Original Version) */
        .routing-block {
            display: flex;
            border-bottom: 2px solid #000;
            margin-bottom: 5px;
        }
        .routing-pincode {
            flex: 1.2;
            padding: 5px 6px;
            border-right: 2px solid #000;
        }
        .routing-pincode .title {
            font-size: 8.5px;
            text-transform: uppercase;
            font-weight: 800;
        }
        .routing-pincode .pincode-val {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1px;
            line-height: 1.1;
        }
        .routing-payment {
            flex: 1.8;
            padding: 5px 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            background: #f8f8f8;
        }
        .payment-badge {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .cod-amount {
            font-size: 16px;
            font-weight: 900;
            color: #000;
            margin-top: 2px;
        }

        /* 4. Ship To / Customer Details (Original Version) */
        .address-box {
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
            font-size: 10px;
            line-height: 1.35;
        }
        .address-box .box-title {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
            display: block;
        }
        .address-box .person-name {
            font-size: 12.5px;
            font-weight: 800;
        }

        /* 5. Package Details (Original Version) */
        .package-meta {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 5px;
        }

        /* 6. Items Summary Table (Original Version) */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 5px;
            border-bottom: 1px solid #000;
        }
        .items-table th, .items-table td {
            padding: 2.5px 3.5px;
            text-align: left;
        }
        .items-table th {
            border-bottom: 1px solid #000;
            font-weight: 800;
        }

        /* 7. Return / Ship From Address (Original Version) */
        .return-box {
            font-size: 8.5px;
            line-height: 1.3;
            border-bottom: 1px dashed #000;
            padding-bottom: 3px;
            margin-bottom: 3px;
        }
        .return-box .box-title {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 1px;
            display: block;
        }

        /* 8. Footer (Original Version) */
        .label-footer {
            font-size: 7.5px;
            text-align: center;
            color: #333;
            line-height: 1.25;
            margin-top: auto;
            padding-top: 2px;
        }

        /* Print Settings */
        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .shipping-label {
                border: 2px solid #000;
                box-shadow: none;
                width: 100mm;
                height: 148mm;
                max-width: 100mm;
                max-height: 148mm;
                page-break-after: always;
                page-break-inside: avoid;
                padding: 4mm;
                margin: 0 auto;
                overflow: hidden;
            }
            svg {
                shape-rendering: crispEdges !important;
            }
            @page {
                size: 100mm 150mm;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Toolbar -->
    <div class="no-print-toolbar">
        <button class="btn-print" onclick="window.print()">🖨️ Print Label (4x6 / A6)</button>
        @if($shipment->hasRemoteLabelUrl())
            <a href="{{ $shipment->label_url }}" target="_blank" class="btn-carrier-pdf">
                📄 Carrier PDF ↗
            </a>
        @endif
        <a href="{{ $verifyUrl }}" target="_blank" class="btn-toggle" title="View product image, SKU, and specifications">
            🔍 Product Preview ↗
        </a>
        <a href="{{ url('/admin/delivery') }}" class="btn-close">
            ← Dashboard
        </a>
    </div>

    <!-- Carrier-Grade Shipping Label Container (Original Layout) -->
    <div class="shipping-label">
        
        <!-- 1. Header -->
        <div class="label-header">
            <div style="display:flex;align-items:center;gap:7px">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:32px;width:auto;object-fit:contain" onerror="this.style.display='none'">
                <div>
                    <div class="brand">TOUCHSTAINS™</div>
                    <div style="font-size:8px;color:#333">www.touchstains.com</div>
                </div>
            </div>
            <div class="courier">
                <div>{{ $courierName }}</div>
                <div style="font-size:8px;font-weight:600">Standard Surface / Air</div>
            </div>
        </div>

        <!-- 2. Barcode & AWB Row (Separated: Left Barcode, Right Small QR Code) -->
        <div class="barcode-section">
            <!-- Left Side: Compact Barcode (Width ~200px, Not stretched across page) -->
            <div class="barcode-left-box">
                <div class="barcode-svg-wrapper">
                    {!! \App\Services\Delivery\BarcodeService::getCode128Svg($barcodeContent, 34, 1.3, false, 8) !!}
                </div>
                <div class="awb-code">AWB: {{ $awbCode }}</div>
                <div style="font-size:8.5px;color:#333;margin-top:1px">
                    Order ID: <strong>#{{ $orderId }}</strong> | Date: {{ $shipment->created_at ? $shipment->created_at->format('d/m/Y') : date('d/m/Y') }}
                </div>
            </div>

            <!-- Right Side: Small QR Code (Separated far away so camera never confuses them) -->
            <div class="barcode-right-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&margin=1&data={{ urlencode($verifyUrl) }}" alt="QR" onerror="this.style.display='none'">
                <span class="qr-subtext">Google Scan</span>
            </div>
        </div>

        <!-- 3. Routing & Payment Block (Original Version) -->
        <div class="routing-block">
            <div class="routing-pincode">
                <div class="title">Destination Pincode</div>
                <div class="pincode-val">{{ $pincode }}</div>
            </div>
            <div class="routing-payment">
                @if($isCod)
                    <div class="payment-badge" style="color:#B91C1C">CASH ON DELIVERY (COD)</div>
                    <div class="cod-amount">COLLECT: ₹{{ number_format($collectAmount, 2) }}</div>
                @else
                    <div class="payment-badge" style="color:#047857">PREPAID ORDER</div>
                    <div style="font-size:10px;font-weight:700;margin-top:2px">DO NOT COLLECT CASH</div>
                @endif
            </div>
        </div>

        <!-- 4. Ship To / Customer Details (Original Version) -->
        <div class="address-box">
            <span class="box-title">SHIP TO (DELIVERY ADDRESS):</span>
            <div class="person-name">{{ $customerName }}</div>
            <div>{{ $customerAddress }}</div>
            <div style="margin-top:2px;font-weight:700">
                Phone: {{ $customerPhone }} | PIN: {{ $pincode }}
            </div>
        </div>

        <!-- 5. Package Details (Original Version) -->
        <div class="package-meta">
            <div>Weight: <strong>{{ number_format($shipment->weight ?: 0.5, 2) }} KG</strong></div>
            <div>Dimensions: <strong>{{ $shipment->length ?: 10 }}x{{ $shipment->breadth ?: 10 }}x{{ $shipment->height ?: 5 }} CM</strong></div>
            <div>Pieces: <strong>1</strong></div>
        </div>

        <!-- 6. Items Summary Table (Original Version + SKU / ID) -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="width:90px">SKU / ID</th>
                    <th style="text-align:center;width:30px">Qty</th>
                    <th style="text-align:right;width:60px">Price</th>
                </tr>
            </thead>
            <tbody>
                @if($order && $order->items && count($order->items) > 0)
                    @foreach($order->items->take(3) as $item)
                        @php
                            $prod = $item->product;
                            $sku = $prod && $prod->sku ? $prod->sku : ('TS-SR-' . ($item->product_id ?: $item->id));
                        @endphp
                        <tr>
                            <td>{{ \Illuminate\Support\Str::limit($item->product_name ?? 'Touchstains Stain Remover', 24) }}</td>
                            <td style="font-family:monospace;font-size:8px">{{ $sku }}</td>
                            <td style="text-align:center;font-weight:700">{{ $item->quantity ?? 1 }}</td>
                            <td style="text-align:right">₹{{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 2) }}</td>
                        </tr>
                    @endforeach
                    @if(count($order->items) > 3)
                        <tr>
                            <td colspan="4" style="font-style:italic;color:#555">+ {{ count($order->items) - 3 }} more item(s)</td>
                        </tr>
                    @endif
                @else
                    <tr>
                        <td>Touchstains Fabric Stain & Rust Remover (250ml)</td>
                        <td style="font-family:monospace;font-size:8px">TS-SR-250ML</td>
                        <td style="text-align:center;font-weight:700">1</td>
                        <td style="text-align:right">₹{{ number_format($collectAmount ?: 299, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- 7. Return / Ship From Address (Original Version) -->
        <div class="return-box" style="border-bottom:none;padding-bottom:0;margin-bottom:2px">
            <span class="box-title">IF UNDELIVERED, RETURN TO:</span>
            <div style="font-weight:700">{{ $settings->warehouse_name ?? 'TOUCHSTAINS Dispatch Centre' }}</div>
            <div>{{ $settings->warehouse_address ?: '24 Bharat Mata Society, RC Marg, Rahul Road, Chembur East' }}, Mumbai - {{ $settings->warehouse_pincode ?: '400074' }}</div>
            <div>Contact: {{ $settings->contact_person ?: 'Support Team' }} (Ph: {{ $settings->contact_number ?: '+91 7679416294' }})</div>
        </div>

        <!-- 8. Footer (Original Version) -->
        <div class="label-footer">
            This is a computer generated shipping label and does not require a physical signature.<br>
            Scan barcode or QR code with Google to view authentic product photos, SKU, and live tracking.
        </div>

    </div>

</body>
</html>
