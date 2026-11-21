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
        // Flipkart/Amazon style public product & order verification URL
        $verifyUrl = url('/verify-order/' . $orderId);
    @endphp
    <title>Shipping Label — AWB: {{ $awbCode }} — Order #{{ $orderId }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { background: #f0f2f5; color: #000; padding: 20px; display: flex; flex-direction: column; align-items: center; }

        /* Top Toolbar */
        .no-print-toolbar {
            background: #fff;
            padding: 10px 20px;
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
            padding: 9px 16px;
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
            padding: 9px 14px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-invoice {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-verify {
            background: #EDE9FE;
            color: #6D28D9;
            border: 1px solid #DDD6FE;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }
        .btn-close {
            background: #e2e8f0;
            color: #475569;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        /* 4x6 inch (approx 100mm x 150mm) Standard Thermal Label Container */
        .shipping-label {
            width: 100mm;
            min-height: 146mm;
            max-height: 148mm;
            background: #fff;
            border: 2px solid #000;
            padding: 4mm;
            box-shadow: 0 4px 14px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        /* Header */
        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }
        .label-header .brand {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .label-header .courier {
            font-size: 13px;
            font-weight: 900;
            text-align: right;
            text-transform: uppercase;
            line-height: 1.2;
        }

        /* Primary AWB Barcode Box (Compact & Sharp) */
        .barcode-section {
            text-align: center;
            padding: 3px 0 4px;
            border-bottom: 2px solid #000;
            background: #fff;
        }
        .barcode-container {
            width: 100%;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 40px;
            overflow: hidden;
        }
        .barcode-container svg {
            width: 95% !important;
            height: 40px !important;
            display: block;
            margin: 0 auto;
            shape-rendering: crispEdges !important;
        }
        .awb-text-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 6px 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        /* Routing Block (Pincode + Payment + Phone Scan QR Code) */
        .routing-block {
            display: flex;
            border-bottom: 2px solid #000;
            margin-bottom: 4px;
            min-height: 64px;
        }
        .routing-pincode {
            flex: 1.1;
            padding: 3px 5px;
            border-right: 2px solid #000;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .routing-pincode .title {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 800;
            color: #222;
        }
        .routing-pincode .pincode-val {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1.5px;
            line-height: 1;
            margin: 2px 0;
        }
        .routing-pincode .hub-text {
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #555;
        }

        .routing-payment {
            flex: 1.4;
            padding: 3px 5px;
            border-right: 2px solid #000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            background: #fff;
        }
        .payment-badge {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cod-badge {
            color: #000;
            border: 1.5px solid #000;
            padding: 2px 4px;
            display: inline-block;
            background: #f8f8f8;
        }
        .prepaid-badge {
            color: #000;
            border: 1.5px solid #000;
            padding: 2px 4px;
            display: inline-block;
            background: #f8f8f8;
        }
        .cod-amount {
            font-size: 14px;
            font-weight: 900;
            color: #000;
            margin-top: 2px;
        }

        .routing-qr {
            width: 70px;
            padding: 2px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .routing-qr img, .routing-qr canvas {
            width: 54px;
            height: 54px;
            display: block;
        }
        .routing-qr .qr-label {
            font-size: 7px;
            font-weight: 800;
            margin-top: 1px;
            text-transform: uppercase;
            color: #047857;
        }

        /* Customer Address Box */
        .address-box {
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
            font-size: 9.5px;
            line-height: 1.3;
        }
        .address-box .box-title {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 1px;
            display: block;
            border-bottom: 1px dashed #666;
            padding-bottom: 1px;
        }
        .address-box .person-name {
            font-size: 12px;
            font-weight: 900;
            color: #000;
            margin-bottom: 1px;
        }

        /* Package Details Bar */
        .package-meta {
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 3px;
            font-weight: 600;
        }

        /* Items summary table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-bottom: 3px;
            border-bottom: 1px solid #000;
        }
        .items-table th, .items-table td {
            padding: 2px 3px;
            text-align: left;
        }
        .items-table th {
            border-bottom: 1px solid #000;
            font-weight: 800;
            font-size: 8px;
            text-transform: uppercase;
        }

        /* Return / Warehouse Address */
        .return-box {
            font-size: 8px;
            line-height: 1.25;
            border-bottom: 1px dashed #000;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }
        .return-box .return-title {
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #222;
        }

        /* Footer */
        .label-footer {
            font-size: 7px;
            text-align: center;
            color: #222;
            line-height: 1.2;
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
                padding: 3.5mm;
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
                📄 Carrier PDF Label ↗
            </a>
        @endif
        <a href="{{ route('admin.orders.invoice', $orderId) }}" target="_blank" class="btn-invoice">
            🧾 Tax Invoice
        </a>
        <a href="{{ $verifyUrl }}" target="_blank" class="btn-verify" title="Preview Product Details page opened upon scanning barcode">
            🔍 Preview Scanned Page ↗
        </a>
        <a href="{{ url('/admin/delivery') }}" class="btn-close">
            ← Dashboard
        </a>
    </div>

    <!-- Carrier-Grade Shipping Label Container -->
    <div class="shipping-label">
        
        <!-- Header -->
        <div class="label-header">
            <div style="display:flex;align-items:center;gap:6px">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:28px;width:auto;object-fit:contain" onerror="this.style.display='none'">
                <div>
                    <div class="brand">TOUCHSTAINS™</div>
                    <div style="font-size:7.5px;color:#333;font-weight:600">touchstains.com</div>
                </div>
            </div>
            <div class="courier">
                <div>{{ $courierName }}</div>
                <div style="font-size:8px;font-weight:700;color:#333">Standard Surface / Air</div>
            </div>
        </div>

        <!-- Primary Compact High-Resolution Barcode (Code 128) -->
        <div class="barcode-section">
            <div class="barcode-container" id="primary-barcode">
                {!! \App\Services\Delivery\BarcodeService::getCode128Svg($awbCode, 40, 1.7, false, 10) !!}
            </div>
            <div class="awb-text-row">
                <span>AWB: {{ $awbCode }}</span>
                <span>ORD: #{{ $orderId }}</span>
            </div>
        </div>

        <!-- Routing, Pincode & Payment Block -->
        <div class="routing-block">
            <!-- Destination Pincode -->
            <div class="routing-pincode">
                <span class="title">Destination Pincode</span>
                <div class="pincode-val">{{ $pincode }}</div>
                <span class="hub-text">Routing Hub Sort</span>
            </div>

            <!-- Payment Type -->
            <div class="routing-payment">
                @if($isCod)
                    <div class="payment-badge cod-badge">CASH ON DELIVERY (COD)</div>
                    <div class="cod-amount">COLLECT: ₹{{ number_format($collectAmount, 2) }}</div>
                @else
                    <div class="payment-badge prepaid-badge">PREPAID ORDER</div>
                    <div style="font-size:9.5px;font-weight:900;margin-top:2px;color:#000">DO NOT COLLECT CASH</div>
                @endif
            </div>

            <!-- Routing QR Code: Scans directly to Flipkart-style Product Details Page -->
            <div class="routing-qr">
                <div id="qrcode-box">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&margin=1&data={{ urlencode($verifyUrl) }}" alt="QR" style="width:54px;height:54px;object-fit:contain" onerror="renderJsQrFallback()">
                </div>
                <span class="qr-label">Scan Details</span>
            </div>
        </div>

        <!-- Customer Address (SHIP TO) -->
        <div class="address-box">
            <span class="box-title">SHIP TO (DELIVERY ADDRESS):</span>
            <div class="person-name">{{ $customerName }}</div>
            <div>{{ $customerAddress }}</div>
            <div style="margin-top:2px;font-weight:900;font-size:10px">
                PINCODE: {{ $pincode }} | PHONE: {{ $customerPhone }}
            </div>
        </div>

        <!-- Package Meta -->
        <div class="package-meta">
            <div>Weight: <strong>{{ number_format($shipment->weight ?: 0.5, 2) }} KG</strong></div>
            <div>Dims: <strong>{{ $shipment->length ?: 10 }}x{{ $shipment->breadth ?: 10 }}x{{ $shipment->height ?: 5 }} CM</strong></div>
            <div>Date: <strong>{{ $shipment->created_at ? $shipment->created_at->format('d/m/Y') : date('d/m/Y') }}</strong></div>
        </div>

        <!-- Items Summary Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="width:100px">SKU / ID</th>
                    <th style="text-align:center;width:28px">Qty</th>
                    <th style="text-align:right;width:50px">Total</th>
                </tr>
            </thead>
            <tbody>
                @if($order && $order->items && count($order->items) > 0)
                    @foreach($order->items->take(2) as $item)
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
                    @if(count($order->items) > 2)
                        <tr>
                            <td colspan="4" style="font-style:italic;color:#444">+ {{ count($order->items) - 2 }} more product(s)</td>
                        </tr>
                    @endif
                @else
                    <tr>
                        <td>Touchstains Fabric Stain Remover (250ml)</td>
                        <td style="font-family:monospace;font-size:8px">TS-SR-250ML</td>
                        <td style="text-align:center;font-weight:700">1</td>
                        <td style="text-align:right">₹{{ number_format($collectAmount ?: 299, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Return / Dispatch Address -->
        <div class="return-box">
            <span class="return-title">IF UNDELIVERED, RETURN TO:</span>
            <div style="font-weight:800">{{ $settings->warehouse_name ?: 'TOUCHSTAINS DISPATCH CENTRE' }}</div>
            <div>{{ $settings->warehouse_address ?: '24 Bharat Mata Society, RC Marg, Rahul Road, Near RNA Park, Chembur East' }}, Mumbai - {{ $settings->warehouse_pincode ?: '400074' }}</div>
            <div>Contact: {{ $settings->contact_number ?: '+91 7679416294' }}</div>
        </div>

        <!-- Footer -->
        <div class="label-footer">
            Scan QR code to view authenticated product photos, SKU, and live tracking.<br>
            Verified at: <strong>touchstains.com/verify-order/{{ $orderId }}</strong>
        </div>

    </div>

    <!-- Client-side Fallback & QR Code Scripts -->
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <script>
        function renderJsQrFallback() {
            var qrBox = document.getElementById('qrcode-box');
            if (qrBox && typeof QRCode !== 'undefined') {
                qrBox.innerHTML = '';
                new QRCode(qrBox, {
                    text: "{{ $verifyUrl }}",
                    width: 54,
                    height: 54,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        }
    </script>
</body>
</html>
