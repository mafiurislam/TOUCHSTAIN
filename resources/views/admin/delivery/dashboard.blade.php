<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delivery Management — Shiprocket Logistics Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laravel-app.css') }}">
    <style>
        .delivery-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px; }
        .delivery-kpi-card { background: #fff; padding: 18px; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .delivery-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        .delivery-table th { background: #F9FAFB; padding: 12px 14px; font-weight: 700; color: #4B5563; border-bottom: 1px solid #E5E7EB; }
        .delivery-table td { padding: 12px 14px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
        .delivery-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; }
        .delivery-btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; border: 1px solid transparent; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
        .modal-content { background: #fff; border-radius: 14px; max-width: 600px; width: 95%; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
    </style>
</head>
<body style="background:#F3F4F6">

    <!-- Admin Top Header -->
    <header style="background:#01684B;color:#fff;padding:14px 0;box-shadow:0 4px 15px rgba(0,0,0,0.12);position:sticky;top:0;z-index:100">
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
            <div style="display:flex;align-items:center;gap:14px">
                <a href="{{ url('/admin') }}" style="background:#fff;padding:5px 10px;border-radius:8px;display:flex;align-items:center">
                    <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}" alt="TOUCHSTAIN" style="height:34px;object-fit:contain">
                </a>
                <div>
                    <h1 style="font-size:18px;font-weight:800;margin:0">🚚 Delivery Management (Shiprocket)</h1>
                    <p style="font-size:11.5px;opacity:0.85;margin:0">Automated Courier Dispatch, AWB Generation & Live Tracking</p>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <a href="{{ url('/admin') }}" class="btn-outline" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.3);padding:7px 12px;font-size:12px">← Main Admin</a>
                <a href="{{ url('/admin/delivery/settings') }}" class="btn-outline" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.3);padding:7px 12px;font-size:12px">⚙️ Shiprocket Settings</a>
                <form action="{{ url('/admin/delivery/sync-tracking') }}" method="POST" style="margin:0">
                    @csrf
                    <button type="submit" class="btn-primary" style="background:#10B981;color:#fff;padding:7px 14px;font-size:12px;border:none">🔄 Sync Tracking</button>
                </form>
                <a href="{{ url('/admin/delivery/export') }}" class="btn-outline" style="background:#fff;color:#01684B;padding:7px 14px;font-size:12px;font-weight:700">📥 Export CSV</a>
            </div>
        </div>
    </header>

    <main class="container" style="padding:24px 16px 60px">

        @if(session('success'))
            <div style="background:#D1FAE5;border:1px solid #059669;color:#065F46;padding:12px 18px;border-radius:10px;font-weight:700;margin-bottom:20px">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#FEE2E2;border:1px solid #DC2626;color:#991B1B;padding:12px 18px;border-radius:10px;font-weight:700;margin-bottom:20px">
                ❌ {{ session('error') }}
            </div>
        @endif

        <!-- Connection Status Banner -->
        <div style="background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $settings->connection_status === 'connected' ? '#10B981' : '#F59E0B' }}"></span>
                <span style="font-size:13.5px;font-weight:700">
                    Shiprocket API: 
                    <span style="color:{{ $settings->connection_status === 'connected' ? '#059669' : '#D97706' }}">
                        {{ strtoupper($settings->connection_status) }} ({{ strtoupper($settings->environment) }} MODE)
                    </span>
                </span>
                <span style="font-size:12px;color:#6B7280">• Pickup: <strong>{{ $settings->pickup_location_name }}</strong> (Pincode: {{ $settings->warehouse_pincode }})</span>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <button type="button" onclick="testConnection()" class="delivery-btn-sm" style="background:#F3F4F6;color:#374151;border-color:#D1D5DB" id="btnTestConn">
                    ⚡ Test Connection
                </button>
                <a href="{{ url('/admin/delivery/settings') }}" style="font-size:12px;color:#01684B;font-weight:600">Configure Credentials →</a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="delivery-kpi-grid">
            <div class="delivery-kpi-card">
                <span style="font-size:11.5px;color:#6B7280;font-weight:700;text-transform:uppercase">Total Shipments</span>
                <div style="font-size:24px;font-weight:800;color:#01684B;margin-top:4px">{{ $counts['total'] }}</div>
                <span style="font-size:11px;color:#10B981">Across all couriers</span>
            </div>
            <div class="delivery-kpi-card">
                <span style="font-size:11.5px;color:#6B7280;font-weight:700;text-transform:uppercase">Pending Shipment</span>
                <div style="font-size:24px;font-weight:800;color:#D97706;margin-top:4px">{{ $counts['pending'] }}</div>
                <span style="font-size:11px;color:#92400E">Awaiting AWB</span>
            </div>
            <div class="delivery-kpi-card">
                <span style="font-size:11.5px;color:#6B7280;font-weight:700;text-transform:uppercase">AWB Generated</span>
                <div style="font-size:24px;font-weight:800;color:#2563EB;margin-top:4px">{{ $counts['awb_assigned'] }}</div>
                <span style="font-size:11px;color:#3B82F6">Ready for label/pickup</span>
            </div>
            <div class="delivery-kpi-card">
                <span style="font-size:11.5px;color:#6B7280;font-weight:700;text-transform:uppercase">Pickup Scheduled</span>
                <div style="font-size:24px;font-weight:800;color:#7C3AED;margin-top:4px">{{ $counts['pickup_scheduled'] }}</div>
                <span style="font-size:11px;color:#8B5CF6">Rider assigned</span>
            </div>
            <div class="delivery-kpi-card">
                <span style="font-size:11.5px;color:#6B7280;font-weight:700;text-transform:uppercase">In Transit</span>
                <div style="font-size:24px;font-weight:800;color:#0284C7;margin-top:4px">{{ $counts['in_transit'] }}</div>
                <span style="font-size:11px;color:#0EA5E9">Moving between hubs</span>
            </div>
            <div class="delivery-kpi-card">
                <span style="font-size:11.5px;color:#6B7280;font-weight:700;text-transform:uppercase">Out For Delivery</span>
                <div style="font-size:24px;font-weight:800;color:#F59E0B;margin-top:4px">{{ $counts['out_for_delivery'] }}</div>
                <span style="font-size:11px;color:#D97706">Expected today</span>
            </div>
            <div class="delivery-kpi-card">
                <span style="font-size:11.5px;color:#6B7280;font-weight:700;text-transform:uppercase">Delivered</span>
                <div style="font-size:24px;font-weight:800;color:#059669;margin-top:4px">{{ $counts['delivered'] }}</div>
                <span style="font-size:11px;color:#10B981">Successfully completed</span>
            </div>
            <div class="delivery-kpi-card">
                <span style="font-size:11.5px;color:#6B7280;font-weight:700;text-transform:uppercase">RTO / Return</span>
                <div style="font-size:24px;font-weight:800;color:#DC2626;margin-top:4px">{{ $counts['rto'] }}</div>
                <span style="font-size:11px;color:#EF4444">Reverse logistics</span>
            </div>
            <div class="delivery-kpi-card">
                <span style="font-size:11.5px;color:#6B7280;font-weight:700;text-transform:uppercase">NDR / Issues</span>
                <div style="font-size:24px;font-weight:800;color:#B91C1C;margin-top:4px">{{ $counts['ndr'] }}</div>
                <span style="font-size:11px;color:#DC2626">Delivery attempts failed</span>
            </div>
        </div>

        <!-- Orders Awaiting Shipment Queue -->
        @if($ordersPendingShipment->count() > 0)
            <div style="background:#fff;border:1px solid #E5E7EB;border-radius:14px;padding:20px;margin-bottom:24px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
                    <h3 style="font-size:16px;font-weight:800;margin:0;color:#1E232C">📦 Orders Ready for Shipment ({{ $ordersPendingShipment->count() }})</h3>
                    <span style="font-size:12px;color:#6B7280">Dispatch through Shiprocket</span>
                </div>
                <div style="overflow-x:auto">
                    <table class="delivery-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer & Destination</th>
                                <th>Items / Weight</th>
                                <th>Payment Type</th>
                                <th>Order Total</th>
                                <th>Quick Dispatch</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordersPendingShipment as $pOrd)
                                <tr>
                                    <td>
                                        <a href="{{ url('/order-details?id=' . $pOrd->id) }}" style="font-weight:700;color:#01684B">#{{ $pOrd->id }}</a>
                                        <div style="font-size:11px;color:#6B7280">{{ $pOrd->created_at->format('d M, h:i A') }}</div>
                                    </td>
                                    <td>
                                        <strong>{{ $pOrd->getCustomerName() }}</strong>
                                        <div style="font-size:11.5px;color:#6B7280">{{ $pOrd->getDeliveryPincode() }} • {{ Str::limit($pOrd->getDeliveryAddressFormatted(), 45) }}</div>
                                    </td>
                                    <td>
                                        <span>{{ $pOrd->items->count() }} items</span>
                                        <div style="font-size:11.5px;color:#01684B;font-weight:600">~{{ $pOrd->calculateTotalWeight() }} kg</div>
                                    </td>
                                    <td>
                                        <span style="padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;background:{{ $pOrd->isCod() ? '#FEF3C7' : '#ECFDF5' }};color:{{ $pOrd->isCod() ? '#92400E' : '#065F46' }}">
                                            {{ $pOrd->isCod() ? 'COD' : 'PREPAID' }}
                                        </span>
                                    </td>
                                    <td style="font-weight:700">₹{{ number_format($pOrd->order_amount, 2) }}</td>
                                    <td>
                                        <div style="display:flex;gap:6px">
                                            <button type="button" onclick="openCourierModal({{ $pOrd->id }}, '{{ $pOrd->getDeliveryPincode() }}', {{ $pOrd->calculateTotalWeight() }})" class="delivery-btn-sm" style="background:#01684B;color:#fff">
                                                Select Courier & Ship →
                                            </button>
                                            <form action="{{ route('admin.delivery.orders.create-shipment', $pOrd->id) }}" method="POST" style="margin:0">
                                                @csrf
                                                <button type="submit" class="delivery-btn-sm" style="background:#E6F4EA;color:#01684B;border-color:#01684B" title="Fast Auto-Select cheapest courier">
                                                    ⚡ Quick Auto
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Filter & Search Panel -->
        <div style="background:#fff;border:1px solid #E5E7EB;border-radius:14px;padding:20px;margin-bottom:24px">
            <form action="{{ url('/admin/delivery') }}" method="GET" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)) 120px;gap:12px;align-items:end">
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4B5563;display:block;margin-bottom:4px">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Order ID, AWB, Customer..." style="padding:8px 12px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4B5563;display:block;margin-bottom:4px">Shipment Status</label>
                    <select name="status" class="form-control" style="padding:8px 12px;font-size:13px">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="awb_assigned" {{ request('status') === 'awb_assigned' ? 'selected' : '' }}>AWB Assigned</option>
                        <option value="pickup_scheduled" {{ request('status') === 'pickup_scheduled' ? 'selected' : '' }}>Pickup Scheduled</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>Out For Delivery</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="rto_initiated" {{ request('status') === 'rto_initiated' ? 'selected' : '' }}>RTO Initiated</option>
                        <option value="ndr" {{ request('status') === 'ndr' ? 'selected' : '' }}>NDR / Failed</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4B5563;display:block;margin-bottom:4px">Payment Type</label>
                    <select name="payment_method" class="form-control" style="padding:8px 12px;font-size:13px">
                        <option value="">All Types</option>
                        <option value="Prepaid" {{ request('payment_method') === 'Prepaid' ? 'selected' : '' }}>Prepaid</option>
                        <option value="COD" {{ request('payment_method') === 'COD' ? 'selected' : '' }}>Cash On Delivery (COD)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4B5563;display:block;margin-bottom:4px">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="padding:8px 12px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:700;color:#4B5563;display:block;margin-bottom:4px">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" style="padding:8px 12px;font-size:13px">
                </div>
                <div style="display:flex;gap:6px">
                    <button type="submit" class="btn-primary" style="padding:8px 16px;font-size:13px;width:100%;justify-content:center">Filter</button>
                    @if(request()->hasAny(['search', 'status', 'payment_method', 'date_from', 'date_to']))
                        <a href="{{ url('/admin/delivery') }}" class="btn-outline" style="padding:8px 10px;font-size:12px">✕</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Master Shipments Table -->
        <div style="background:#fff;border:1px solid #E5E7EB;border-radius:14px;padding:22px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px">
                <h2 style="font-size:18px;font-weight:800;color:#1E232C;margin:0">🚚 All Shipments ({{ $shipments->total() }})</h2>
                <span style="font-size:12px;color:#6B7280">Page {{ $shipments->currentPage() }} of {{ $shipments->lastPage() }}</span>
            </div>

            <div style="overflow-x:auto">
                <table class="delivery-table">
                    <thead>
                        <tr>
                            <th>Shipment / Order</th>
                            <th>Customer & Address</th>
                            <th>Courier & AWB</th>
                            <th>Payment & Value</th>
                            <th>Status & Milestone</th>
                            <th>Actions & Documentation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $s)
                            <tr>
                                <td>
                                    <div style="font-weight:800;color:#1E232C">Shipment #{{ $s->id }}</div>
                                    <div style="font-size:12px">
                                        Order: <a href="{{ url('/order-details?id=' . $s->order_id) }}" style="color:#01684B;font-weight:700">#{{ $s->order_id }}</a>
                                    </div>
                                    <div style="font-size:11px;color:#6B7280">SR Order: {{ $s->shiprocket_order_id ?: 'Pending' }}</div>
                                </td>
                                <td>
                                    <strong>{{ $s->order ? $s->order->getCustomerName() : 'Customer' }}</strong>
                                    <div style="font-size:11.5px;color:#6B7280">
                                        📍 {{ $s->destination_pincode }} • {{ $s->package_weight }} kg
                                    </div>
                                    <div style="font-size:11px;color:#9CA3AF">
                                        {{ Str::limit($s->order ? $s->order->getDeliveryAddressFormatted() : '', 40) }}
                                    </div>
                                </td>
                                <td>
                                    @if($s->awb_number)
                                        <div style="font-weight:800;color:#01684B;font-family:monospace;font-size:13.5px">{{ $s->awb_number }}</div>
                                        <div style="font-size:12px;color:#4B5563">{{ $s->courier_name ?: 'Courier Partner' }}</div>
                                        @if($s->tracking_url)
                                            <a href="{{ $s->tracking_url }}" target="_blank" style="font-size:11px;color:#2563EB;text-decoration:underline">Live Track ↗</a>
                                        @endif
                                    @else
                                        <span style="color:#9CA3AF;font-style:italic">AWB Pending</span>
                                        <form action="{{ route('admin.delivery.shipments.generate-awb', $s->id) }}" method="POST" style="margin-top:4px">
                                            @csrf
                                            <button type="submit" class="delivery-btn-sm" style="background:#E6F4EA;color:#01684B;font-size:11px;padding:3px 8px">
                                                + Assign Courier & AWB
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    <span style="padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;background:{{ $s->payment_method === 'COD' ? '#FEF3C7' : '#ECFDF5' }};color:{{ $s->payment_method === 'COD' ? '#92400E' : '#065F46' }}">
                                        {{ $s->payment_method }}
                                    </span>
                                    <div style="font-weight:700;margin-top:2px">₹{{ number_format($s->order ? $s->order->order_amount : 0, 2) }}</div>
                                    @if($s->payment_method === 'COD')
                                        <div style="font-size:11px;color:#D97706">COD Collect: ₹{{ number_format($s->cod_amount, 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="delivery-status-badge" style="background:{{ $s->status_bg }};color:{{ $s->status_color }}">
                                        ● {{ $s->formatted_status }}
                                    </span>
                                    @if($s->latestTrackingEvent)
                                        <div style="font-size:11px;color:#4B5563;margin-top:4px">
                                            {{ Str::limit($s->latestTrackingEvent->activity, 35) }}
                                        </div>
                                    @endif
                                    @if($s->pickup_status === 'scheduled')
                                        <div style="font-size:11px;color:#7C3AED">Pickup: {{ $s->pickup_scheduled_date ? $s->pickup_scheduled_date->format('d M') : 'Scheduled' }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
                                        @if(!$s->awb_number)
                                            <!-- 🚀 Ship Now Action Button -->
                                            <form action="{{ route('admin.delivery.shipments.generate-awb', $s->id) }}" method="POST" style="margin:0">
                                                @csrf
                                                <button type="submit" class="delivery-btn-sm" style="background:#2563EB;color:#fff;font-weight:700" title="Assign Courier & Generate AWB on Shiprocket">
                                                    🚀 Ship Now
                                                </button>
                                            </form>
                                            <a href="https://app.shiprocket.in/seller/orders" target="_blank" class="delivery-btn-sm" style="background:#EDE9FE;color:#6D28D9" title="Manage directly in Shiprocket">
                                                Shiprocket ↗
                                            </a>
                                        @endif

                                        @if($s->awb_number)
                                            <!-- Print Label -->
                                            <a href="{{ route('admin.delivery.shipments.print-label', $s->id) }}" target="_blank" class="delivery-btn-sm" style="background:#F3F4F6;color:#1E232C;border-color:#D1D5DB" title="Print Shipping Label (Vector Barcode)">
                                                🏷️ Label
                                            </a>
                                            @if($s->hasRemoteLabelUrl())
                                                <a href="{{ $s->label_url }}" target="_blank" class="delivery-btn-sm" style="background:#ECFDF5;color:#047857;border-color:#A7F3D0;font-weight:700" title="Official Carrier PDF Label (Shiprocket/Delhivery)">
                                                    📄 Carrier PDF
                                                </a>
                                            @endif
                                            <!-- Print Manifest -->
                                            <a href="{{ route('admin.delivery.shipments.print-manifest', $s->id) }}" target="_blank" class="delivery-btn-sm" style="background:#F3F4F6;color:#1E232C;border-color:#D1D5DB" title="Print Manifest">
                                                📋 Manifest
                                            </a>
                                        @endif

                                        @if($s->canSchedulePickup())
                                            <form action="{{ route('admin.delivery.shipments.schedule-pickup', $s->id) }}" method="POST" style="margin:0">
                                                @csrf
                                                <button type="submit" class="delivery-btn-sm" style="background:#EDE9FE;color:#6D28D9" title="Schedule Rider Pickup">
                                                    📅 Pickup
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Live Track Refresh -->
                                        <form action="{{ route('admin.delivery.shipments.track', $s->id) }}" method="POST" style="margin:0">
                                            @csrf
                                            <button type="submit" class="delivery-btn-sm" style="background:#E0F2FE;color:#0369A1" title="Refresh Live Status">
                                                🔄 Track
                                            </button>
                                        </form>

                                        @if($s->canCancel())
                                            <form action="{{ route('admin.delivery.shipments.cancel', $s->id) }}" method="POST" style="margin:0" onsubmit="return confirm('Cancel this shipment on Shiprocket?')">
                                                @csrf
                                                <button type="submit" class="delivery-btn-sm" style="background:#FEE2E2;color:#DC2626" title="Cancel Shipment">
                                                    ✕
                                                </button>
                                            </form>
                                        @endif

                                        @if($s->canCreateReturn())
                                            <button type="button" onclick="openReturnModal({{ $s->id }})" class="delivery-btn-sm" style="background:#FEF3C7;color:#92400E" title="Reverse Logistics Return">
                                                ↩ Return
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:48px 16px;color:#6B7280">
                                    <div style="font-size:32px;margin-bottom:8px">📦</div>
                                    <h4 style="font-size:16px;font-weight:700;color:#374151">No shipments found</h4>
                                    <p style="font-size:13px;margin:4px 0 0">Select an order above to dispatch via Shiprocket.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px">
                {{ $shipments->links() }}
            </div>
        </div>

    </main>

    <!-- Modal: Select Courier & Compare Rates -->
    <div class="modal-overlay" id="courierModal">
        <div class="modal-content">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #E5E7EB;padding-bottom:12px">
                <h3 style="font-size:17px;font-weight:800;color:#1E232C;margin:0">🚀 Courier Serviceability & Rate Selection</h3>
                <button type="button" onclick="closeCourierModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#6B7280">✕</button>
            </div>
            
            <form id="createShipmentForm" method="POST">
                @csrf
                <div style="background:#F9FAFB;padding:12px;border-radius:8px;margin-bottom:16px;display:flex;justify-content:space-between;font-size:12.5px">
                    <div>Destination Pincode: <strong id="modalPincode">400001</strong></div>
                    <div>Est. Weight: <strong id="modalWeight">0.5 kg</strong></div>
                </div>

                <div style="margin-bottom:16px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Select Preferred Courier Partner:</label>
                    <div id="courierListContainer" style="display:flex;flex-direction:column;gap:8px;max-height:260px;overflow-y:auto">
                        <div style="text-align:center;padding:20px;color:#6B7280">Checking live serviceability rates...</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#4B5563">Package Weight (KG)</label>
                        <input type="number" step="0.1" name="weight" id="customWeightInput" class="form-control" value="0.5" style="padding:8px 10px;font-size:13px">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#4B5563">Pickup Date</label>
                        <input type="date" name="pickup_date" class="form-control" value="{{ date('Y-m-d', strtotime('+1 day')) }}" style="padding:8px 10px;font-size:13px">
                    </div>
                </div>

                <div style="margin-bottom:18px">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer">
                        <input type="checkbox" name="schedule_pickup" value="1" checked> Automatically schedule pickup rider
                    </label>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px">
                    <button type="button" onclick="closeCourierModal()" class="btn-outline" style="padding:8px 16px;font-size:13px">Cancel</button>
                    <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:13px;background:#01684B;color:#fff">
                        Confirm & Create Shipment →
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Return Shipment -->
    <div class="modal-overlay" id="returnModal">
        <div class="modal-content">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #E5E7EB;padding-bottom:12px">
                <h3 style="font-size:17px;font-weight:800;color:#DC2626;margin:0">↩ Create Reverse Pickup / Return</h3>
                <button type="button" onclick="closeReturnModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#6B7280">✕</button>
            </div>
            
            <form id="returnForm" method="POST">
                @csrf
                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Reason for Return</label>
                    <select name="reason" class="form-control" required style="padding:8px 10px;font-size:13px">
                        <option value="Defective or Damaged Product">Defective or Damaged Product</option>
                        <option value="Wrong Item Received">Wrong Item Received</option>
                        <option value="Customer Not Satisfied">Customer Not Satisfied</option>
                        <option value="Package Damaged in Transit">Package Damaged in Transit</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px">
                    <button type="button" onclick="closeReturnModal()" class="btn-outline" style="padding:8px 16px;font-size:13px">Cancel</button>
                    <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:13px;background:#DC2626;color:#fff">
                        Schedule Reverse Pickup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function testConnection() {
            const btn = document.getElementById('btnTestConn');
            btn.innerHTML = '⚡ Testing...';
            btn.disabled = true;

            fetch('{{ route("admin.delivery.test-connection") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                btn.innerHTML = '⚡ Test Connection';
                btn.disabled = false;
                if (data.success) {
                    alert('✅ Shiprocket Connection Successful!\nMode: ' + data.environment.toUpperCase() + '\nAvailable Couriers: ' + data.available_couriers_count);
                    window.location.reload();
                } else {
                    alert('❌ Connection Failed: ' + data.message);
                }
            })
            .catch(err => {
                btn.innerHTML = '⚡ Test Connection';
                btn.disabled = false;
                alert('Connection error: ' + err);
            });
        }

        function openCourierModal(orderId, pincode, weight) {
            document.getElementById('modalPincode').innerText = pincode;
            document.getElementById('modalWeight').innerText = weight + ' kg';
            document.getElementById('customWeightInput').value = weight;
            document.getElementById('createShipmentForm').action = `{{ url('/admin/delivery/orders') }}/${orderId}/create-shipment`;
            document.getElementById('courierModal').style.display = 'flex';

            const container = document.getElementById('courierListContainer');
            container.innerHTML = '<div style="text-align:center;padding:16px;color:#6B7280">Calculating real-time courier rates...</div>';

            fetch(`{{ route('admin.delivery.check-serviceability') }}?order_id=${orderId}&weight=${weight}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.couriers && data.couriers.length > 0) {
                        let html = '';
                        data.couriers.forEach((c, idx) => {
                            html += `
                                <label style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border:1px solid #E5E7EB;border-radius:8px;cursor:pointer;background:${idx === 0 ? '#ECFDF5' : '#fff'};border-color:${idx === 0 ? '#10B981' : '#E5E7EB'}">
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <input type="radio" name="courier_company_id" value="${c.courier_company_id}" ${idx === 0 ? 'checked' : ''}>
                                        <div>
                                            <strong style="font-size:13px;color:#1E232C">${c.courier_name}</strong>
                                            <div style="font-size:11px;color:#6B7280">ETD: ${c.etd} • Rating: ⭐ ${c.rating}</div>
                                        </div>
                                    </div>
                                    <div style="text-align:right">
                                        <strong style="font-size:14px;color:#01684B">₹${c.rate.toFixed(2)}</strong>
                                        ${idx === 0 ? '<div style="font-size:10px;color:#059669;font-weight:700">RECOMMENDED</div>' : ''}
                                    </div>
                                </label>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<div style="color:#DC2626;padding:10px">Pincode not directly serviceable or rates unavailable. You can proceed with auto-allocation.</div>';
                    }
                })
                .catch(() => {
                    container.innerHTML = '<div style="padding:10px">Using auto-allocated standard surface courier.</div>';
                });
        }

        function closeCourierModal() {
            document.getElementById('courierModal').style.display = 'none';
        }

        function openReturnModal(shipmentId) {
            document.getElementById('returnForm').action = `{{ url('/admin/delivery/shipments') }}/${shipmentId}/return`;
            document.getElementById('returnModal').style.display = 'flex';
        }

        function closeReturnModal() {
            document.getElementById('returnModal').style.display = 'none';
        }
    </script>
</body>
</html>
