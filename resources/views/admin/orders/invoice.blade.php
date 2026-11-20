<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice — Order #{{ $order->id }}</title>
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
            max-width: 800px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            max-width: 800px;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border: 1px solid #ddd;
        }

        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #2563EB;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .inv-brand { font-size: 24px; font-weight: 900; color: #2563EB; letter-spacing: -0.5px; }

        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 24px;
        }
        .inv-table th {
            background: #F1F5F9;
            padding: 10px 12px;
            border: 1px solid #CBD5E1;
            text-align: left;
            font-weight: 800;
        }
        .inv-table td {
            padding: 10px 12px;
            border: 1px solid #E2E8F0;
        }

        .total-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .total-table {
            width: 280px;
            font-size: 13px;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 6px 10px;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print-bar { display: none !important; }
            .invoice-sheet { border: none; box-shadow: none; padding: 0; width: 100%; }
            @page { size: A4; margin: 15mm; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Print Tax Invoice</button>
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-back">← Back to Order</a>
    </div>

    <div class="invoice-sheet">
        
        <div class="inv-header">
            <div style="display:flex;align-items:center;gap:14px">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:64px;width:auto;object-fit:contain">
                <div>
                    <div class="inv-brand">TOUCHSTAINS™</div>
                    <div style="font-size:12px;color:#64748B;margin-top:4px">
                        Fabric Stain & Rust Remover Solutions<br>
                        Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada,<br>
                        Sion, Mumbai, Maharashtra - 400022<br>
                        Helpline: +91 7679416294 / 7039214465
                    </div>
                </div>
            </div>
            <div style="text-align:right">
                <h2 style="font-size:18px;font-weight:900;color:#0F172A">TAX INVOICE</h2>
                <div style="font-size:13px;font-weight:700;margin-top:4px">Invoice #: <code>INV-{{ date('Y') }}-{{ $order->id }}</code></div>
                <div style="font-size:12px;color:#64748B">Date: {{ $order->created_at ? $order->created_at->format('d M Y') : date('d M Y') }}</div>
                <div style="font-size:12px;color:#64748B">Payment: <strong>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</strong></div>
            </div>
        </div>

        <div class="address-grid">
            <div>
                <strong style="text-transform:uppercase;font-size:11px;color:#64748B;display:block;margin-bottom:4px">Billed & Shipped To:</strong>
                <div style="font-weight:800;font-size:14px">{{ $order->getCustomerName() }}</div>
                <div>{{ $order->getDeliveryAddressFormatted() }}</div>
                <div>Pincode: <strong>{{ $order->getDeliveryPincode() }}</strong></div>
                <div>Phone: {{ $order->getCustomerPhone() }}</div>
            </div>
            <div style="text-align:right">
                <strong style="text-transform:uppercase;font-size:11px;color:#64748B;display:block;margin-bottom:4px">Shipment Details:</strong>
                <div>Courier: <strong>{{ $order->shipment ? $order->shipment->courier_name : 'Express Standard Logistics' }}</strong></div>
                <div>AWB: <strong>{{ $order->shipment ? $order->shipment->awb_number : 'Assigned on Dispatch' }}</strong></div>
                <div>Status: <span style="font-weight:800;text-transform:uppercase">{{ $order->order_status }}</span></div>
            </div>
        </div>

        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Item Description</th>
                    <th style="text-align:center">Qty</th>
                    <th style="text-align:right">Rate (₹)</th>
                    <th style="text-align:right">Total (₹)</th>
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
                        <td style="text-align:center">{{ $item->quantity }}</td>
                        <td style="text-align:right">₹{{ number_format($item->price, 2) }}</td>
                        <td style="text-align:right;font-weight:700">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

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

        <div style="font-size:11px;color:#64748B;text-align:center;border-top:1px dashed #CBD5E1;padding-top:14px;line-height:1.5">
            Thank you for choosing TOUCHSTAINS! For customer assistance, please write to touchstains@gmail.com or call +91 7679416294.<br>
            This is a computer generated invoice and requires no physical signature.
        </div>

    </div>

</body>
</html>
