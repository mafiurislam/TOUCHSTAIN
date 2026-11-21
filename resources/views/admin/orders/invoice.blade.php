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
        $qrData = "AWB:{$awbCode}|ORD:{$order->id}|PIN:{$pincode}|COD:" . ($isCod ? number_format($collectAmount, 2) : '0');
    @endphp
    <title>Tax Invoice — Order #{{ $order->id }} — AWB: {{ $awbCode }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { background: #f0f2f5; color: #111; padding: 30px; display: flex; flex-direction: column; align-items: center; }
        
        .no-print-bar {
            background: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 24px;
            width: 100%;
            max-width: 850px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn-print {
            background: #2563EB;
            color: #fff;
            border: none;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-label {
            background: #01684B;
            color: #fff;
            padding: 9px 16px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-carrier-pdf {
            background: #059669;
            color: #fff;
            padding: 9px 16px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back {
            background: #E2E8F0;
            color: #334155;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
        }

        .invoice-sheet {
            background: #fff;
            width: 100%;
            max-width: 850px;
            padding: 36px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border: 1px solid #ddd;
        }

        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #2563EB;
            padding-bottom: 18px;
            margin-bottom: 20px;
            gap: 20px;
        }
        .inv-brand { font-size: 24px; font-weight: 900; color: #2563EB; letter-spacing: -0.5px; }

        /* Courier Dispatch & Scannable Barcode Strip */
        .courier-scan-strip {
            background: #f8fafc;
            border: 2px solid #000;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .scan-barcode-box {
            flex: 1.6;
            text-align: center;
        }
        .scan-barcode-box svg {
            width: 95% !important;
            height: 55px !important;
            display: block;
            margin: 0 auto;
            shape-rendering: crispEdges !important;
        }
        .scan-barcode-caption {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 1.5px;
            margin-top: 3px;
        }
        .scan-routing-box {
            flex: 1.1;
            border-left: 2px solid #000;
            padding-left: 14px;
            font-size: 12px;
            line-height: 1.4;
        }
        .scan-qr-box {
            width: 75px;
            text-align: center;
            border-left: 2px solid #000;
            padding-left: 12px;
        }
        .scan-qr-box img {
            width: 65px;
            height: 65px;
            display: block;
            margin: 0 auto;
        }

        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 22px;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 22px;
        }
        .inv-table th {
            background: #F1F5F9;
            padding: 9px 12px;
            border: 1px solid #CBD5E1;
            text-align: left;
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
        }
        .inv-table td {
            padding: 9px 12px;
            border: 1px solid #E2E8F0;
        }

        .total-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }
        .total-table {
            width: 290px;
            font-size: 13px;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 5px 10px;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print-bar { display: none !important; }
            .invoice-sheet { border: none; box-shadow: none; padding: 0; width: 100%; max-width: 100%; }
            svg { shape-rendering: crispEdges !important; }
            @page { size: A4; margin: 12mm; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <button class="btn-print" onclick="window.print()">🖨️ Print Tax Invoice</button>
            @if($shipment)
                <a href="{{ route('admin.delivery.shipments.print-label', $shipment->id) }}" target="_blank" class="btn-label">
                    🏷️ Print Shipping Label (4x6)
                </a>
                @if($shipment->hasRemoteLabelUrl())
                    <a href="{{ $shipment->label_url }}" target="_blank" class="btn-carrier-pdf">
                        📄 Carrier Label PDF ↗
                    </a>
                @endif
            @endif
        </div>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-back">← Back to Order</a>
    </div>

    <div class="invoice-sheet">
        
        <!-- Header -->
        <div class="inv-header">
            <div style="display:flex;align-items:center;gap:14px">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:60px;width:auto;object-fit:contain" onerror="this.style.display='none'">
                <div>
                    <div class="inv-brand">TOUCHSTAINS™</div>
                    <div style="font-size:11.5px;color:#64748B;margin-top:4px;line-height:1.4">
                        Fabric Stain & Rust Remover Solutions<br>
                        24 Bharat Mata Society, RC Marg, Rahul Road, Chembur East, Mumbai - 400074<br>
                        Helpline: +91 7679416294 / 7039214465 | GSTIN: Unregistered Consumer Sale
                    </div>
                </div>
            </div>
            <div style="text-align:right">
                <h2 style="font-size:18px;font-weight:900;color:#0F172A">TAX INVOICE</h2>
                <div style="font-size:13px;font-weight:700;margin-top:3px">Invoice #: <code>INV-{{ date('Y') }}-{{ $order->id }}</code></div>
                <div style="font-size:12px;color:#64748B">Date: {{ $order->created_at ? $order->created_at->format('d M Y') : date('d M Y') }}</div>
                <div style="font-size:12px;color:#64748B">Payment: <strong>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</strong></div>
            </div>
        </div>

        <!-- Scannable Courier Logistics Strip (For Delivery Boy Handheld / Mobile Scanner) -->
        <div class="courier-scan-strip">
            <!-- 1D Vector Code 128 Barcode -->
            <div class="scan-barcode-box">
                {!! \App\Services\Delivery\BarcodeService::getCode128Svg($awbCode, 55, 2, false, 12) !!}
                <div class="scan-barcode-caption">AWB: {{ $awbCode }}</div>
            </div>

            <!-- Routing Details -->
            <div class="scan-routing-box">
                <div>Courier: <strong>{{ $courierName }}</strong></div>
                <div>Dest Pincode: <strong style="font-size:15px;letter-spacing:1px">{{ $pincode }}</strong></div>
                <div style="margin-top:4px">
                    @if($isCod)
                        <span style="background:#000;color:#fff;padding:2px 6px;font-size:11px;font-weight:900;border-radius:3px">
                            COD: COLLECT ₹{{ number_format($collectAmount, 2) }}
                        </span>
                    @else
                        <span style="background:#059669;color:#fff;padding:2px 6px;font-size:11px;font-weight:900;border-radius:3px">
                            PREPAID — DO NOT COLLECT
                        </span>
                    @endif
                </div>
            </div>

            <!-- 2D QR Code -->
            <div class="scan-qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&margin=1&data={{ urlencode($qrData) }}" alt="QR" onerror="this.style.display='none'">
                <div style="font-size:8px;font-weight:800;text-transform:uppercase;margin-top:2px">Scan Pickup</div>
            </div>
        </div>

        <!-- Address Grid -->
        <div class="address-grid">
            <div>
                <strong style="text-transform:uppercase;font-size:11px;color:#64748B;display:block;margin-bottom:4px">Billed & Shipped To:</strong>
                <div style="font-weight:800;font-size:14px">{{ $order->getCustomerName() }}</div>
                <div>{{ $order->getDeliveryAddressFormatted() }}</div>
                <div>Pincode: <strong>{{ $order->getDeliveryPincode() }}</strong></div>
                <div>Phone: <strong>{{ $order->getCustomerPhone() }}</strong></div>
            </div>
            <div style="text-align:right">
                <strong style="text-transform:uppercase;font-size:11px;color:#64748B;display:block;margin-bottom:4px">Dispatch Details:</strong>
                <div>Dispatch From: <strong>TOUCHSTAINS Mumbai Warehouse (400074)</strong></div>
                <div>Order Status: <span style="font-weight:800;text-transform:uppercase">{{ $order->order_status }}</span></div>
                <div>AWB Number: <strong>{{ $awbCode }}</strong></div>
                <div>Tracking: <strong>shiprocket.co/tracking/{{ $awbCode }}</strong></div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>Item Description</th>
                    <th style="text-align:center;width:60px">Qty</th>
                    <th style="text-align:right;width:100px">Rate (₹)</th>
                    <th style="text-align:right;width:110px">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            <div style="font-size:11px;color:#64748B">Unit: {{ $item->unit ?: 'Bottle' }}</div>
                        </td>
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
                <tr style="border-top:2px solid #0F172A;font-size:15px;font-weight:900;color:#2563EB">
                    <td>Grand Total:</td>
                    <td style="text-align:right">₹{{ number_format($order->order_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div style="font-size:11px;color:#64748B;text-align:center;border-top:1px dashed #CBD5E1;padding-top:14px;line-height:1.5">
            Thank you for shopping with TOUCHSTAINS! For customer assistance or return inquiries, please call +91 7679416294.<br>
            This is a computer generated invoice and requires no physical signature.
        </div>

    </div>

</body>
</html>
