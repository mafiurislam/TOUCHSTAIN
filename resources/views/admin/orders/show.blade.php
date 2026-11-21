<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order #{{ $order->id }} — TOUCHSTAINS Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laravel-app.css') }}">
    <style>
        .order-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; font-family: 'Poppins', sans-serif; }
        .order-card { background: #fff; border: 1px solid #E5E7EB; border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .order-badge { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-block; text-transform: uppercase; }
        .badge-pending { background: #FEF3C7; color: #92400E; }
        .badge-confirmed { background: #DBEAFE; color: #1E40AF; }
        .badge-processing { background: #EDE9FE; color: #6D28D9; }
        .badge-out_for_delivery { background: #CFFAFE; color: #0E7490; }
        .badge-delivered { background: #D1FAE5; color: #065F46; }
        .badge-canceled { background: #FEE2E2; color: #991B1B; }
        .btn-action { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; border: none; transition: 0.2s; }
        .btn-shiprocket { background: #01684B; color: #fff; }
        .btn-shiprocket:hover { background: #01523B; color: #fff; }
        .btn-shipnow { background: #2563EB; color: #fff; }
        .btn-shipnow:hover { background: #1D4ED8; color: #fff; }
        .btn-sr-panel { background: #7C3AED; color: #fff; }
        .btn-sr-panel:hover { background: #6D28D9; color: #fff; }
        .table-items th { background: #F9FAFB; padding: 12px; font-size: 12px; text-transform: uppercase; color: #4B5563; text-align: left; }
        .table-items td { padding: 14px 12px; border-top: 1px solid #F3F4F6; font-size: 13.5px; vertical-align: middle; }
    </style>
</head>
<body style="background:#F8FAFC;color:#1E293B">

    <div class="order-container">
        
        <!-- Top Nav & Breadcrumb -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
            <div style="display:flex;align-items:center;gap:12px">
                <a href="{{ route('admin.orders') }}" class="btn-action" style="background:#fff;border:1px solid #D1D5DB;color:#374151">
                    ← Back to Orders
                </a>
                <h1 style="font-size:22px;font-weight:800;margin:0">Order #{{ $order->id }}</h1>
                <span class="order-badge badge-{{ $order->order_status }}">
                    {{ str_replace('_', ' ', $order->order_status) }}
                </span>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn-action" style="background:#fff;border:1px solid #D1D5DB;color:#374151">
                    🖨️ Printable Invoice
                </a>
                <a href="{{ url('/admin/delivery') }}" class="btn-action" style="background:#E6F4EA;color:#01684B;border:1px solid #01684B">
                    🚚 Shiprocket Hub
                </a>
                <form action="{{ route('admin.orders.delete', $order->id) }}" method="POST" style="margin:0" onsubmit="return confirm('Are you sure you want to permanently delete Order #{{ $order->id }}?')">
                    @csrf
                    <button type="submit" class="btn-action" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#DC2626">
                        🗑️ Delete Order
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div style="background:#DEF7EC;border:1px solid #31C48D;color:#03543F;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13.5px;font-weight:600">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#FDE8E8;border:1px solid #F98080;color:#9B1C1C;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13.5px;font-weight:600">
                {{ session('error') }}
            </div>
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
            
            <!-- Left Column: Order Items & Delivery Logistics Box -->
            <div>
                
                <!-- 🚀 SHIPROCKET LOGISTICS & COURIER ACTIONS -->
                <div class="order-card" style="border-left:5px solid #01684B">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                        <div>
                            <h2 style="font-size:16px;font-weight:800;color:#01684B;margin:0">
                                🚚 Shiprocket Delivery & Logistics
                            </h2>
                            <p style="font-size:12px;color:#64748B;margin:3px 0 0">
                                Pickup from: <strong>{{ $deliverySettings->pickup_location_name ?: 'home' }}</strong> (Chembur, Mumbai 400074)
                            </p>
                        </div>

                        @if($order->shipment)
                            <a href="https://app.shiprocket.in/seller/orders" target="_blank" class="btn-action btn-sr-panel" style="font-size:12px;padding:6px 14px">
                                ↗ Open in Shiprocket Panel
                            </a>
                        @endif
                    </div>

                    @if(!$order->shipment)
                        <!-- Case 1: Order not yet sent to Shiprocket -->
                        <div style="background:#F0FDF4;border:1.5px dashed #059669;border-radius:10px;padding:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
                            <div>
                                <strong style="color:#065F46;font-size:14px">Ready to Dispatch via Shiprocket</strong>
                                <p style="font-size:12.5px;color:#047857;margin:4px 0 0">
                                    Click below to send this order to Shiprocket. It will appear in your Delivery section where you can click <strong>"Ship Now"</strong> to assign courier or manage directly from Shiprocket!
                                </p>
                            </div>
                            <form action="{{ route('admin.delivery.orders.create-shipment', $order->id) }}" method="POST" style="margin:0">
                                @csrf
                                <button type="submit" class="btn-action btn-shiprocket" style="font-size:14px;padding:10px 20px">
                                    🚚 Send to Shiprocket →
                                </button>
                            </form>
                        </div>
                    @elseif(empty($order->shipment->awb_number))
                        <!-- Case 2: Order sent to Shiprocket, awaiting Ship Now / Courier Assignment -->
                        <div style="background:#EFF6FF;border:1.5px solid #3B82F6;border-radius:10px;padding:18px">
                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:14px">
                                <div>
                                    <span style="background:#DBEAFE;color:#1E40AF;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700">
                                        READY TO SHIP
                                    </span>
                                    <div style="font-size:14px;font-weight:700;color:#1E3A8A;margin-top:6px">
                                        Shiprocket Order ID: #{{ $order->shipment->shiprocket_order_id }}
                                    </div>
                                    <div style="font-size:12px;color:#3B82F6">
                                        Shipment ID: #{{ $order->shipment->shiprocket_shipment_id }}
                                    </div>
                                </div>
                                <div style="display:flex;gap:10px;align-items:center">
                                    <!-- 🚀 SHIP NOW BUTTON -->
                                    <form action="{{ route('admin.delivery.shipments.generate-awb', $order->shipment->id) }}" method="POST" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-action btn-shipnow" style="font-size:14px;padding:10px 22px">
                                            🚀 Ship Now (Book Courier & Pickup)
                                        </button>
                                    </form>
                                    <a href="https://app.shiprocket.in/seller/orders" target="_blank" class="btn-action btn-sr-panel" style="font-size:13px;padding:10px 16px">
                                        Manual Courier in Shiprocket ↗
                                    </a>
                                </div>
                            </div>
                            <p style="font-size:12px;color:#64748B;margin:0">
                                💡 <em>"Ship Now" এ ক্লিক করলে স্বয়ংক্রিয়ভাবে সেরা কুরিয়ার পার্টনার (Delhivery, Blue Dart ইত্যাদি) বুক হয়ে AWB নম্বর ও পিকআপ শিডিউল তৈরি হয়ে যাবে।</em>
                            </p>
                        </div>
                    @else
                        <!-- Case 3: Courier Partner Booked with AWB -->
                        <div style="background:#F0FDF4;border:1.5px solid #10B981;border-radius:10px;padding:18px">
                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:14px">
                                <div>
                                    <span style="background:#D1FAE5;color:#065F46;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700">
                                        COURIER BOOKED & PICKUP SCHEDULED
                                    </span>
                                    <div style="font-size:15px;font-weight:800;color:#065F46;margin-top:6px">
                                        🚚 Courier: {{ $order->shipment->courier_name }}
                                    </div>
                                    <div style="font-size:13px;font-family:monospace;font-weight:700;color:#1E293B;margin-top:2px">
                                        AWB Code: {{ $order->shipment->awb_number }}
                                    </div>
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap">
                                    <a href="{{ route('admin.delivery.shipments.print-label', $order->shipment->id) }}" target="_blank" class="btn-action" style="background:#fff;border:1px solid #10B981;color:#065F46">
                                        🏷️ Print Label
                                    </a>
                                    @if($order->shipment->hasRemoteLabelUrl())
                                        <a href="{{ $order->shipment->label_url }}" target="_blank" class="btn-action" style="background:#047857;color:#fff;font-weight:700">
                                            📄 Carrier PDF ↗
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.delivery.shipments.print-manifest', $order->shipment->id) }}" target="_blank" class="btn-action" style="background:#fff;border:1px solid #10B981;color:#065F46">
                                        📋 Manifest
                                    </a>
                                    <form action="{{ route('admin.delivery.shipments.track', $order->shipment->id) }}" method="POST" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-action" style="background:#E0F2FE;color:#0369A1">
                                            🔄 Refresh Tracking
                                        </button>
                                    </form>
                                    @if($order->shipment->tracking_url)
                                        <a href="{{ $order->shipment->tracking_url }}" target="_blank" class="btn-action" style="background:#7C3AED;color:#fff">
                                            Live Track ↗
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Products Table -->
                <div class="order-card">
                    <h2 style="font-size:16px;font-weight:800;margin-bottom:16px">Ordered Products</h2>
                    <table class="table-items" style="width:100%;border-collapse:collapse">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th style="text-align:right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:12px">
                                            @if($item->product && !empty($item->product->image))
                                                <img src="{{ asset('storage/product/' . $item->product->image[0]) }}" style="width:45px;height:45px;object-fit:cover;border-radius:6px;border:1px solid #E5E7EB" onerror="this.src='{{ asset('storage/restaurant/touchstains-logo.png') }}'">
                                            @endif
                                            <div>
                                                <strong>{{ $item->product_name }}</strong>
                                                <div style="font-size:11.5px;color:#64748B">SKU: SKU-{{ $item->product_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>₹{{ number_format($item->price, 2) }}</td>
                                    <td><strong>{{ $item->quantity }}</strong></td>
                                    <td style="text-align:right;font-weight:700">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Order Financial Breakdown -->
                    <div style="margin-top:20px;border-top:1px solid #E5E7EB;padding-top:16px;display:flex;justify-content:flex-end">
                        <div style="width:280px;font-size:13px">
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                                <span>Subtotal:</span>
                                <strong>₹{{ number_format($order->subtotal ?: $order->order_amount, 2) }}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                                <span>Delivery Charge:</span>
                                <span>₹{{ number_format($order->delivery_charge, 2) }}</span>
                            </div>
                            @if($order->coupon_discount_amount > 0)
                                <div style="display:flex;justify-content:space-between;margin-bottom:6px;color:#059669">
                                    <span>Discount ({{ $order->coupon_code }}):</span>
                                    <span>-₹{{ number_format($order->coupon_discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div style="display:flex;justify-content:space-between;border-top:1.5px solid #E5E7EB;padding-top:8px;font-size:16px;font-weight:800;color:#01684B">
                                <span>Grand Total:</span>
                                <span>₹{{ number_format($order->order_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Status Update & Customer Details -->
            <div>
                
                <!-- Order Status Update Form -->
                <div class="order-card">
                    <h2 style="font-size:15px;font-weight:800;margin-bottom:14px">Manage Order Status</h2>
                    <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                        @csrf
                        <div style="margin-bottom:14px">
                            <label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px;color:#475569">Order Status:</label>
                            <select name="order_status" class="form-control" style="width:100%;padding:8px 12px;border:1px solid #D1D5DB;border-radius:8px;font-size:13px">
                                @foreach(['pending' => 'Pending (নতুন)', 'confirmed' => 'Confirmed (কনফার্মড)', 'processing' => 'Processing (প্রসেসিং)', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered (ডেলিভার্ড)', 'canceled' => 'Canceled (বাতিল)', 'returned' => 'Returned'] as $stKey => $stLbl)
                                    <option value="{{ $stKey }}" {{ $order->order_status === $stKey ? 'selected' : '' }}>
                                        {{ $stLbl }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div style="margin-bottom:14px">
                            <label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px;color:#475569">Payment Status:</label>
                            <select name="payment_status" class="form-control" style="width:100%;padding:8px 12px;border:1px solid #D1D5DB;border-radius:8px;font-size:13px">
                                <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid (বাকি)</option>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid (পরিশোধিত)</option>
                            </select>
                        </div>
                        <div style="margin-bottom:14px">
                            <label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px;color:#475569">Internal Admin Note:</label>
                            <textarea name="admin_note" rows="2" class="form-control" style="width:100%;padding:8px 12px;border:1px solid #D1D5DB;border-radius:8px;font-size:12.5px" placeholder="Write any notes here...">{{ $order->order_note }}</textarea>
                        </div>
                        <button type="submit" class="btn-action" style="width:100%;justify-content:center;background:#0F172A;color:#fff">
                            Update Order Details
                        </button>
                    </form>
                </div>

                <!-- Customer & Destination Details -->
                <div class="order-card">
                    <h2 style="font-size:15px;font-weight:800;margin-bottom:14px">Customer & Delivery Details</h2>
                    
                    <div style="margin-bottom:12px">
                        <span style="font-size:11px;text-transform:uppercase;color:#64748B;font-weight:700">Customer Name:</span>
                        <div style="font-size:14px;font-weight:700;margin-top:2px">{{ $order->getCustomerName() }}</div>
                    </div>

                    <div style="margin-bottom:12px">
                        <span style="font-size:11px;text-transform:uppercase;color:#64748B;font-weight:700">Phone Number:</span>
                        <div style="font-size:13.5px;font-weight:600;margin-top:2px">
                            <a href="tel:{{ $order->getCustomerPhone() }}" style="color:#01684B">{{ $order->getCustomerPhone() }}</a>
                        </div>
                    </div>

                    <div style="margin-bottom:12px">
                        <span style="font-size:11px;text-transform:uppercase;color:#64748B;font-weight:700">Delivery Address:</span>
                        <div style="font-size:13px;line-height:1.45;margin-top:3px;color:#334155">
                            {{ $order->getDeliveryAddressFormatted() }}
                        </div>
                    </div>

                    <div style="margin-bottom:12px;display:flex;gap:16px">
                        <div>
                            <span style="font-size:11px;text-transform:uppercase;color:#64748B;font-weight:700">City / State:</span>
                            <div style="font-size:13px;font-weight:600">{{ $order->getDeliveryCity() }}, {{ $order->getDeliveryState() }}</div>
                        </div>
                        <div>
                            <span style="font-size:11px;text-transform:uppercase;color:#64748B;font-weight:700">Pincode:</span>
                            <div style="font-size:13px;font-weight:700;color:#01684B">{{ $order->getDeliveryPincode() }}</div>
                        </div>
                    </div>

                    <div style="border-top:1px solid #E5E7EB;padding-top:12px;margin-top:12px">
                        <span style="font-size:11px;text-transform:uppercase;color:#64748B;font-weight:700">Payment Method:</span>
                        <div style="font-size:13px;font-weight:700;margin-top:2px">
                            {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}
                            <span style="font-size:11px;padding:2px 6px;border-radius:4px;margin-left:6px;background:{{ $order->payment_status === 'paid' ? '#DEF7EC' : '#FEF3C7' }};color:{{ $order->payment_status === 'paid' ? '#03543F' : '#92400E' }}">
                                {{ strtoupper($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</body>
</html>
