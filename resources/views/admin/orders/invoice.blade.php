<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $shipment = $order->shipment;
        $awbCode = ($shipment && $shipment->awb_number) ? $shipment->awb_number : ('ORD-' . $order->id);
        $courierName = ($shipment && $shipment->courier_name) ? $shipment->courier_name : 'Express Standard Logistics';
        $pincode = $order->getDeliveryPincode();
        $isCod = strtoupper($order->payment_method ?? '') === 'CASH_ON_DELIVERY' || ($shipment && strtoupper($shipment->payment_method ?? '') === 'COD');
        $collectAmount = $order->order_amount;
        // Base domain resolution: If on live domain, use current host; if on localhost, use live domain so phone scans always work!
        $liveDomain = (!empty(request()->getHost()) && !in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1']))
            ? request()->getSchemeAndHttpHost()
            : 'https://touchstains.com';
        $verifyUrl = "{$liveDomain}/verify-order/{$order->id}";

        // Short URL for 1D Barcode so Google Lens immediately displays product details
        $shortHost = (!empty(request()->getHost()) && !in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1']))
            ? (request()->getHost() . '/v/' . $order->id)
            : ("touchstains.com/v/{$order->id}");
    @endphp
    <title>Tax Invoice — Order #{{ $order->id }} — AWB: {{ $awbCode }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { background: #f0f2f5; color: #111; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        
        /* Non-print toolbar */
        .no-print-bar {
            background: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 16px;
            width: 100%;
            max-width: 820px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-print {
            background: #2563EB;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-label {
            background: #01684B;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-carrier-pdf {
            background: #059669;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-verify {
            background: #EDE9FE;
            color: #6D28D9;
            border: 1px solid #DDD6FE;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
        }
        .btn-back {
            background: #E2E8F0;
            color: #334155;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        /* 1-Page Guaranteed Sheet Container */
        .invoice-sheet {
            background: #fff;
            width: 100%;
            max-width: 820px;
            padding: 22px 28px;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid #ddd;
        }

        /* Header */
        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #2563EB;
            padding-bottom: 10px;
            margin-bottom: 10px;
            gap: 16px;
        }
        .inv-brand { font-size: 20px; font-weight: 900; color: #2563EB; letter-spacing: -0.5px; }

        /* Courier & Verification Barcode Strip (Compact & 100% Scannable) */
        .courier-scan-strip {
            background: #f8fafc;
            border: 1.5px solid #000;
            border-radius: 6px;
            padding: 6px 12px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .scan-barcode-box {
            flex: 1.5;
            text-align: center;
        }
        .scan-barcode-box svg {
            width: 96% !important;
            height: 34px !important;
            display: block;
            margin: 0 auto;
            shape-rendering: crispEdges !important;
        }
        .scan-barcode-caption {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11.5px;
            font-weight: 900;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .scan-routing-box {
            flex: 1.2;
            border-left: 1.5px solid #000;
            padding-left: 10px;
            font-size: 11px;
            line-height: 1.35;
        }
        .scan-qr-box {
            width: 65px;
            text-align: center;
            border-left: 1.5px solid #000;
            padding-left: 10px;
        }
        .scan-qr-box img {
            width: 50px;
            height: 50px;
            display: block;
            margin: 0 auto;
        }
        .scan-qr-label {
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #047857;
            margin-top: 1px;
            display: block;
        }

        /* Addresses */
        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 10px;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Items Table */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 10px;
        }
        .inv-table th {
            background: #F1F5F9;
            padding: 5px 8px;
            border: 1px solid #CBD5E1;
            text-align: left;
            font-weight: 800;
            font-size: 10.5px;
            text-transform: uppercase;
        }
        .inv-table td {
            padding: 5px 8px;
            border: 1px solid #E2E8F0;
        }

        /* Total Box */
        .total-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
        }
        .total-table {
            width: 260px;
            font-size: 11.5px;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 3px 6px;
        }

        /* Footer */
        .inv-footer {
            font-size: 9.5px;
            color: #64748B;
            text-align: center;
            border-top: 1px dashed #CBD5E1;
            padding-top: 6px;
            line-height: 1.35;
        }

        /* Strict Single Page Print Settings */
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }
        @media print {
            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                height: 100% !important;
                overflow: hidden !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .invoice-sheet {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }
            .courier-scan-strip {
                border: 1.5px solid #000 !important;
                background: #fbfbfb !important;
            }
            table, tr, td, th {
                page-break-inside: avoid !important;
            }
            svg {
                shape-rendering: crispEdges !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <button class="btn-print" onclick="window.print()">🖨️ Print Tax Invoice (Single Page)</button>
            @if($shipment)
                <a href="{{ route('admin.delivery.shipments.print-label', $shipment->id) }}" target="_blank" class="btn-label">
                    🏷️ 4x6 Shipping Label
                </a>
                @if($shipment->hasRemoteLabelUrl())
                    <a href="{{ $shipment->label_url }}" target="_blank" class="btn-carrier-pdf">
                        📄 Carrier Label PDF ↗
                    </a>
                @endif
            @endif
            <a href="{{ $verifyUrl }}" target="_blank" class="btn-verify" title="Preview Product Details page opened upon scanning barcode">
                🔍 Preview Scanned Page ↗
            </a>
        </div>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-back">← Back to Order</a>
    </div>

    <div class="invoice-sheet">
        
        <!-- Header -->
        <div class="inv-header">
            <div style="display:flex;align-items:center;gap:12px">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:44px;width:auto;object-fit:contain" onerror="this.style.display='none'">
                <div>
                    <div class="inv-brand">TOUCHSTAINS™</div>
                    <div style="font-size:10px;color:#64748B;margin-top:2px;line-height:1.3">
                        Fabric Stain & Rust Remover Solutions | Mumbai, Maharashtra - 400074<br>
                        Helpline: +91 7679416294 / 7039214465 | Website: touchstains.com
                    </div>
                </div>
            </div>
            <div style="text-align:right">
                <h2 style="font-size:16px;font-weight:900;color:#0F172A">TAX INVOICE</h2>
                <div style="font-size:11.5px;font-weight:700;margin-top:2px">Invoice #: <code>INV-{{ date('Y') }}-{{ $order->id }}</code></div>
                <div style="font-size:10.5px;color:#64748B">Date: {{ $order->created_at ? $order->created_at->format('d M Y') : date('d M Y') }}</div>
                <div style="font-size:10.5px;color:#64748B">Payment: <strong>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</strong></div>
            </div>
        </div>

        <!-- Scannable Courier Logistics Strip (Compact Barcode + Phone Scan QR Code) -->
        <div class="courier-scan-strip">
            <!-- 1D Compact Code 128 Barcode for Google Scan & Courier -->
            <div class="scan-barcode-box" style="text-align:left">
                <div style="max-width:210px">
                    {!! \App\Services\Delivery\BarcodeService::getCode128Svg($shortHost, 34, 1.3, false, 8) !!}
                </div>
                <div class="scan-barcode-caption">AWB: {{ $awbCode }}</div>
            </div>

            <!-- Routing & Payment Info -->
            <div class="scan-routing-box">
                <div>Courier: <strong>{{ $courierName }}</strong></div>
                <div>Dest PIN: <strong style="font-size:13px;letter-spacing:1px">{{ $pincode }}</strong></div>
                <div style="margin-top:2px">
                    @if($isCod)
                        <span style="background:#000;color:#fff;padding:1px 5px;font-size:9.5px;font-weight:900;border-radius:3px">
                            COD: COLLECT ₹{{ number_format($collectAmount, 2) }}
                        </span>
                    @else
                        <span style="background:#059669;color:#fff;padding:1px 5px;font-size:9.5px;font-weight:900;border-radius:3px">
                            PREPAID — DO NOT COLLECT
                        </span>
                    @endif
                </div>
            </div>

            <!-- 2D QR Code (Scans directly to Flipkart-style Product Details Page) -->
            <div class="scan-qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&margin=1&data={{ urlencode($verifyUrl) }}" alt="QR" onerror="this.style.display='none'">
                <span class="scan-qr-label">Scan Details</span>
            </div>
        </div>

        <!-- Address Grid -->
        <div class="address-grid">
            <div>
                <strong style="text-transform:uppercase;font-size:9.5px;color:#64748B;display:block;margin-bottom:2px">Billed & Shipped To:</strong>
                <div style="font-weight:800;font-size:12px">{{ $order->getCustomerName() }}</div>
                <div>{{ $order->getDeliveryAddressFormatted() }}</div>
                <div>PINCODE: <strong>{{ $order->getDeliveryPincode() }}</strong> | Phone: <strong>{{ $order->getCustomerPhone() }}</strong></div>
            </div>
            <div style="text-align:right">
                <strong style="text-transform:uppercase;font-size:9.5px;color:#64748B;display:block;margin-bottom:2px">Dispatch Details:</strong>
                <div>Dispatch From: <strong>TOUCHSTAINS Mumbai Warehouse (400074)</strong></div>
                <div>Status: <span style="font-weight:800;text-transform:uppercase">{{ $order->order_status }}</span></div>
                <div>AWB #: <strong>{{ $awbCode }}</strong></div>
                <div>Scan Verify: <strong>touchstains.com/verify-order/{{ $order->id }}</strong></div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Product Description</th>
                    <th style="width:110px">SKU / ID</th>
                    <th style="text-align:center;width:45px">Qty</th>
                    <th style="text-align:right;width:80px">Rate (₹)</th>
                    <th style="text-align:right;width:85px">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $idx => $item)
                    @php
                        $prod = $item->product;
                        $sku = $prod && $prod->sku ? $prod->sku : ('TS-SR-' . ($item->product_id ?: $item->id));
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            <span style="font-size:9.5px;color:#64748B">({{ $item->unit ?: '250ml Bottle' }})</span>
                        </td>
                        <td style="font-family:monospace;font-size:10px">{{ $sku }}</td>
                        <td style="text-align:center;font-weight:700">{{ $item->quantity }}</td>
                        <td style="text-align:right">₹{{ number_format($item->price, 2) }}</td>
                        <td style="text-align:right;font-weight:700">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Box -->
        <div class="total-box">
            <table class="total-table">
                <tr>
                    <td>Subtotal:</td>
                    <td style="text-align:right;font-weight:700">₹{{ number_format($order->subtotal ?: $order->order_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Delivery Fee:</td>
                    <td style="text-align:right;font-weight:700">{{ $order->delivery_charge > 0 ? '₹' . number_format($order->delivery_charge, 2) : 'FREE' }}</td>
                </tr>
                @if($order->coupon_discount_amount > 0)
                    <tr style="color:#DC2626">
                        <td>Discount ({{ $order->coupon_code }}):</td>
                        <td style="text-align:right;font-weight:700">- ₹{{ number_format($order->coupon_discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr style="border-top:1.5px solid #0F172A;font-size:13px;font-weight:900;color:#2563EB">
                    <td>Grand Total:</td>
                    <td style="text-align:right">₹{{ number_format($order->order_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="inv-footer">
            Thank you for shopping with TOUCHSTAINS! Scan the QR code to verify product authenticity, SKU, and live courier tracking.<br>
            Customer Care Helpline: +91 7679416294. This is an authenticated computer generated invoice.
        </div>

    </div>

</body>
</html>
