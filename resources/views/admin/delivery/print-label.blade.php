<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label — AWB: {{ $shipment->awb_number ?? ('ORD-' . $shipment->order_id) }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { background: #f0f2f5; color: #000; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        
        .no-print-toolbar {
            background: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .btn-print {
            background: #01684B;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-close {
            background: #e4e7eb;
            color: #333;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        /* 4x6 inch (approx 100mm x 150mm / 384pt x 576pt) Thermal Label Container */
        .shipping-label {
            width: 100mm;
            min-height: 145mm;
            background: #fff;
            border: 2px solid #000;
            padding: 8mm;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .label-header .brand {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.5px;
        }
        .label-header .courier {
            font-size: 15px;
            font-weight: 800;
            text-align: right;
            text-transform: uppercase;
        }

        /* Barcode Area */
        .barcode-section {
            text-align: center;
            padding: 6px 0;
            border-bottom: 2px solid #000;
        }
        .barcode-stripes {
            height: 38px;
            background: repeating-linear-gradient(
                90deg,
                #000,
                #000 2px,
                #fff 2px,
                #fff 4px,
                #000 4px,
                #000 7px,
                #fff 7px,
                #fff 9px,
                #000 9px,
                #000 12px,
                #fff 12px,
                #fff 15px
            );
            margin: 0 auto 4px;
            width: 85%;
        }
        .awb-code {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 2px;
            font-family: monospace;
        }

        /* Routing block */
        .routing-block {
            display: flex;
            border-bottom: 2px solid #000;
            margin-bottom: 6px;
        }
        .routing-pincode {
            flex: 1.2;
            padding: 6px 4px;
            border-right: 2px solid #000;
        }
        .routing-pincode .title {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 700;
        }
        .routing-pincode .pincode-val {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1px;
            line-height: 1.1;
        }
        .routing-payment {
            flex: 1.8;
            padding: 6px 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            background: #f8f8f8;
        }
        .payment-badge {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .cod-amount {
            font-size: 18px;
            font-weight: 900;
            color: #000;
            margin-top: 2px;
        }

        /* Addresses */
        .address-box {
            border-bottom: 1px solid #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
            font-size: 10.5px;
            line-height: 1.35;
        }
        .address-box .box-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
            display: block;
        }
        .address-box .person-name {
            font-size: 13px;
            font-weight: 800;
        }

        /* Items summary table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 6px;
            border-bottom: 1px solid #000;
        }
        .items-table th, .items-table td {
            padding: 3px 4px;
            text-align: left;
        }
        .items-table th {
            border-bottom: 1px solid #000;
            font-weight: 800;
        }

        .label-footer {
            font-size: 8.5px;
            text-align: center;
            color: #333;
            line-height: 1.3;
            margin-top: auto;
            border-top: 1px dashed #000;
            padding-top: 4px;
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
                width: 100%;
                height: 100%;
                page-break-after: always;
                padding: 6mm;
            }
            @page {
                size: 100mm 150mm;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <button class="btn-print" onclick="window.print()">🖨️ Print Label (4x6 / A6)</button>
        <a href="{{ url('/admin/delivery') }}" class="btn-close">← Back to Delivery Dashboard</a>
        <span style="font-size:12px;color:#666">Press Ctrl + P to print directly to thermal or laser printer.</span>
    </div>

    <div class="shipping-label">
        
        <!-- Header -->
        <div class="label-header">
            <div style="display:flex;align-items:center;gap:8px">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:34px;width:auto;object-fit:contain">
                <div>
                    <div class="brand">TOUCHSTAINS™</div>
                    <div style="font-size:8.5px;color:#333">www.touchstains.com</div>
                </div>
            </div>
            <div class="courier">
                <div>{{ $shipment->courier_name ?: 'EXPRESS LOGISTICS' }}</div>
                <div style="font-size:8.5px;font-weight:600">Standard Surface / Air</div>
            </div>
        </div>

        <!-- Barcode & AWB -->
        <div class="barcode-section">
            <div class="barcode-stripes"></div>
            <div class="awb-code">AWB: {{ $shipment->awb_number ?: ('SR' . rand(100000000, 999999999)) }}</div>
            <div style="font-size:9.5px;margin-top:2px">Order ID: <strong>#{{ $shipment->order_id }}</strong> | Date: {{ $shipment->created_at->format('d/m/Y') }}</div>
        </div>

        <!-- Routing & Payment Block -->
        <div class="routing-block">
            <div class="routing-pincode">
                <div class="title">Destination Pincode</div>
                <div class="pincode-val">{{ $shipment->order ? $shipment->order->getDeliveryPincode() : '400022' }}</div>
            </div>
            <div class="routing-payment">
                @if(strtoupper($shipment->payment_method) === 'COD' || ($shipment->cod_amount > 0))
                    <div class="payment-badge" style="color:#B91C1C">CASH ON DELIVERY (COD)</div>
                    <div class="cod-amount">COLLECT: ₹{{ number_format($shipment->cod_amount ?: $shipment->order->order_amount, 2) }}</div>
                @else
                    <div class="payment-badge" style="color:#047857">PREPAID ORDER</div>
                    <div style="font-size:11px;font-weight:700;margin-top:2px">DO NOT COLLECT CASH</div>
                @endif
            </div>
        </div>

        <!-- Ship To / Customer Details -->
        <div class="address-box">
            <span class="box-title">SHIP TO (DELIVERY ADDRESS):</span>
            <div class="person-name">{{ $shipment->order ? $shipment->order->getCustomerName() : 'Customer' }}</div>
            <div>
                {{ $shipment->order ? $shipment->order->getDeliveryAddressFormatted() : 'Delivery Address' }}
            </div>
            <div style="margin-top:2px;font-weight:700">
                Phone: {{ $shipment->order ? $shipment->order->getCustomerPhone() : 'N/A' }}
            </div>
        </div>

        <!-- Package Details -->
        <div style="display:flex;justify-content:space-between;font-size:9.5px;border-bottom:1px solid #000;padding-bottom:4px;margin-bottom:6px">
            <div>Weight: <strong>{{ number_format($shipment->weight ?: 0.5, 2) }} KG</strong></div>
            <div>Dimensions: <strong>{{ $shipment->length }}x{{ $shipment->breadth }}x{{ $shipment->height }} CM</strong></div>
            <div>Pieces: <strong>1</strong></div>
        </div>

        <!-- Items Summary -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align:center">Qty</th>
                    <th style="text-align:right">Price</th>
                </tr>
            </thead>
            <tbody>
                @if($shipment->order && $shipment->order->items && count($shipment->order->items) > 0)
                    @foreach($shipment->order->items->take(3) as $item)
                        <tr>
                            <td>{{ \Illuminate\Support\Str::limit($item->product_name ?? 'Touchstains Cleaner', 26) }}</td>
                            <td style="text-align:center">{{ $item->quantity ?? 1 }}</td>
                            <td style="text-align:right">₹{{ number_format($item->price ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    @if(count($shipment->order->items) > 3)
                        <tr>
                            <td colspan="3" style="font-style:italic;color:#555">+ {{ count($shipment->order->items) - 3 }} more item(s)</td>
                        </tr>
                    @endif
                @else
                    <tr>
                        <td>Touchstains Fabric Stain & Rust Remover</td>
                        <td style="text-align:center">1</td>
                        <td style="text-align:right">₹{{ number_format($shipment->order->order_amount ?? 299, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Return / Ship From Address -->
        <div class="address-box" style="border-bottom:none;padding-bottom:0;margin-bottom:4px">
            <span class="box-title">IF UNDELIVERED, RETURN TO:</span>
            <div style="font-weight:700">{{ $settings->warehouse_name ?? 'TOUCHSTAINS Dispatch Centre' }}</div>
            <div>{{ $settings->warehouse_address }}, {{ $settings->warehouse_city }}, {{ $settings->warehouse_state }} - {{ $settings->warehouse_pincode }}</div>
            <div>Contact: {{ $settings->contact_person }} (Ph: {{ $settings->contact_number }})</div>
        </div>

        <!-- Footer -->
        <div class="label-footer">
            This is a computer generated shipping label and does not require a physical signature.<br>
            Handled safely under Shiprocket Logistics Network.
        </div>

    </div>

</body>
</html>
