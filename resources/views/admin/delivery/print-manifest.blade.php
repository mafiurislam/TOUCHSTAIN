<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Pickup Manifest — Shipment #{{ $shipment->id }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body { background: #f0f2f5; color: #111; padding: 24px; display: flex; flex-direction: column; align-items: center; }

        .no-print-toolbar {
            background: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
            align-items: center;
            width: 100%;
            max-width: 900px;
            justify-content: space-between;
        }
        .btn-print {
            background: #01684B;
            color: #fff;
            border: none;
            padding: 9px 20px;
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

        .manifest-sheet {
            background: #fff;
            width: 100%;
            max-width: 900px;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border: 1px solid #ddd;
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .manifest-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .meta-box h4 {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .meta-box p {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }

        .manifest-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 30px;
        }
        .manifest-table th {
            background: #f3f4f6;
            color: #374151;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            text-align: left;
            font-weight: 800;
        }
        .manifest-table td {
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px dashed #9ca3af;
        }
        .sig-box {
            border-top: 1px solid #000;
            padding-top: 8px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print-toolbar { display: none !important; }
            .manifest-sheet { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            @page { size: A4; margin: 15mm; }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <div>
            <button class="btn-print" onclick="window.print()">🖨️ Print Manifest Sheet (A4)</button>
            <a href="{{ url('/admin/delivery') }}" class="btn-close">← Back to Delivery Dashboard</a>
        </div>
        <div style="font-size:12px;color:#666">Handover proof between Merchant and Courier Pickup Executive</div>
    </div>

    <div class="manifest-sheet">
        
        <!-- Header -->
        <div class="header-title">
            <div style="display:flex;align-items:center;gap:14px">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:54px;width:auto;object-fit:contain">
                <div>
                    <h1 style="font-size:22px;font-weight:900;letter-spacing:-0.5px">COURIER PICKUP MANIFEST</h1>
                    <p style="font-size:13px;color:#4b5563;margin-top:2px">TOUCHSTAINS™ — Shiprocket Dispatch Manifest</p>
                </div>
            </div>
            <div style="text-align:right">
                <div style="font-size:14px;font-weight:800">Manifest #: <code>MNF-{{ date('Ymd') }}-{{ $shipment->id }}</code></div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px">Date: {{ date('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="manifest-meta">
            <div class="meta-box">
                <h4>Pickup From (Merchant / Warehouse):</h4>
                <p>{{ $settings->warehouse_name ?? 'TOUCHSTAINS Dispatch' }}</p>
                <div style="font-size:12px;color:#4b5563;margin-top:2px">
                    {{ $settings->warehouse_address }}, {{ $settings->warehouse_city }}, {{ $settings->warehouse_state }} - {{ $settings->warehouse_pincode }}<br>
                    Contact: {{ $settings->contact_person }} ({{ $settings->contact_number }})
                </div>
            </div>
            <div class="meta-box">
                <h4>Courier & Service Details:</h4>
                <p>Partner: {{ $shipment->courier_name ?: 'Shiprocket Express Courier' }}</p>
                <div style="font-size:12px;color:#4b5563;margin-top:2px">
                    Pickup Location Nickname: <strong>{{ $settings->pickup_location_name }}</strong><br>
                    Shiprocket Shipment ID: <strong>{{ $shipment->shiprocket_shipment_id ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>

        <!-- Shipments Table -->
        <table class="manifest-table">
            <thead>
                <tr>
                    <th style="width:40px;text-align:center">#</th>
                    <th>Order #</th>
                    <th>AWB Number</th>
                    <th>Customer Name & City</th>
                    <th>Pincode</th>
                    <th>Weight</th>
                    <th>Mode</th>
                    <th style="text-align:right">Collect Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align:center">1</td>
                    <td><strong>#{{ $shipment->order_id }}</strong></td>
                    <td><strong style="font-family:monospace">{{ $shipment->awb_number ?: 'N/A' }}</strong></td>
                    <td>
                        {{ $shipment->order ? $shipment->order->getCustomerName() : 'Customer' }}<br>
                        <span style="font-size:11px;color:#6b7280">{{ $shipment->order ? $shipment->order->getCustomerPhone() : '' }}</span>
                    </td>
                    <td><strong>{{ $shipment->order ? $shipment->order->getDeliveryPincode() : '400022' }}</strong></td>
                    <td>{{ number_format($shipment->weight ?: 0.5, 2) }} KG</td>
                    <td>
                        @if(strtoupper($shipment->payment_method) === 'COD' || $shipment->cod_amount > 0)
                            <span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-weight:700;font-size:11px">COD</span>
                        @else
                            <span style="background:#d1fae5;color:#065f46;padding:2px 6px;border-radius:4px;font-weight:700;font-size:11px">PREPAID</span>
                        @endif
                    </td>
                    <td style="text-align:right;font-weight:800">
                        @if(strtoupper($shipment->payment_method) === 'COD' || $shipment->cod_amount > 0)
                            ₹{{ number_format($shipment->cod_amount ?: $shipment->order->order_amount, 2) }}
                        @else
                            ₹0.00
                        @endif
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;font-weight:800">
                    <td colspan="5">TOTAL PACKAGES: 1</td>
                    <td colspan="2">TOTAL WEIGHT: {{ number_format($shipment->weight ?: 0.5, 2) }} KG</td>
                    <td style="text-align:right">
                        @if(strtoupper($shipment->payment_method) === 'COD' || $shipment->cod_amount > 0)
                            ₹{{ number_format($shipment->cod_amount ?: $shipment->order->order_amount, 2) }}
                        @else
                            ₹0.00
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Terms -->
        <p style="font-size:11px;color:#6b7280;line-height:1.5">
            <strong>Declaration:</strong> We hereby declare that the goods mentioned above have been packed properly and handed over to the courier pickup representative in sound condition. Any discrepancy or damage during transit must be reported immediately.
        </p>

        <!-- Signature Boxes -->
        <div class="signatures-grid">
            <div>
                <div style="height:60px"></div>
                <div class="sig-box">
                    Dispatcher Signature & Seal<br>
                    <span style="font-weight:normal;color:#6b7280;font-size:11px">({{ $settings->contact_person ?? 'Warehouse Manager' }})</span>
                </div>
            </div>
            <div>
                <div style="height:60px"></div>
                <div class="sig-box">
                    Courier Representative Signature<br>
                    <span style="font-weight:normal;color:#6b7280;font-size:11px">(Name, Mobile No. & Employee ID)</span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
