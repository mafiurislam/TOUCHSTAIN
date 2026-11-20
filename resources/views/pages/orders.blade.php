@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="my_order">My Orders</span>
    </div>

    <div class="account-layout">
        <!-- Left Account Menu -->
        @include('partials.account-sidebar', ['activeTab' => 'orders'])

        <!-- Right Orders List from SQLite -->
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px">
                <h1 style="font-size:20px;font-weight:700" data-i18n="my_order">My Orders ({{ $orders->count() }})</h1>
                <a href="{{ url('/shop') }}" class="btn-primary" style="padding:8px 16px;font-size:13px">+ Place New Order</a>
            </div>

            <div id="sqlite-orders-list" style="display:flex;flex-direction:column;gap:14px">
                @forelse($orders as $ord)
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:18px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
                        <div>
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap">
                                <strong style="font-size:16px;color:var(--primary)">Order #{{ $ord->id }}</strong>
                                <span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700;text-transform:uppercase;background:{{ $ord->order_status === 'delivered' ? '#D1FAE5' : ($ord->order_status === 'canceled' ? '#FEE2E2' : '#FEF3C7') }};color:{{ $ord->order_status === 'delivered' ? '#065F46' : ($ord->order_status === 'canceled' ? '#991B1B' : '#92400E') }}">
                                    {{ str_replace('_', ' ', $ord->order_status) }}
                                </span>
                                @if(!$ord->isCod())
                                    <span style="background:#DCFCE7;color:#166534;font-size:11px;padding:2px 8px;border-radius:99px;font-weight:700">
                                        ✓ Paid via Razorpay
                                    </span>
                                @else
                                    <span style="background:#FEF3C7;color:#92400E;font-size:11px;padding:2px 8px;border-radius:99px;font-weight:700">
                                        🚚 Cash on Delivery
                                    </span>
                                @endif
                                <span style="font-size:12px;color:var(--text-muted)">{{ $ord->created_at ? $ord->created_at->format('d M Y, h:i A') : $ord->delivery_date }}</span>
                            </div>
                            <p style="font-size:13px;color:var(--text-secondary);margin:0 0 4px">
                                {{ $ord->items->count() }} Items • Total: <strong>₹{{ number_format($ord->order_amount, 2) }}</strong> • Payment: 
                                @if(!$ord->isCod())
                                    <strong style="color:#1D4ED8">Razorpay Online</strong>
                                    @if($ord->utr_number)
                                        <span style="font-family:monospace;font-size:11.5px;color:#4B5563">({{ $ord->utr_number }})</span>
                                    @endif
                                @else
                                    <strong>CASH ON DELIVERY (COD)</strong>
                                @endif
                            </p>

                            @if($ord->shipment)
                                <div style="display:flex;align-items:center;gap:8px;margin:6px 0;flex-wrap:wrap">
                                    <span style="font-size:11.5px;background:#F0FDF4;color:#166534;border:1px solid #BBF7D0;padding:3px 8px;border-radius:6px;font-weight:700">
                                        🚚 {{ $ord->shipment->courier_name ?: 'Shiprocket Courier' }}
                                    </span>
                                    <span style="font-size:11.5px;background:#F3F4F6;color:#374151;border:1px solid #E5E7EB;padding:3px 8px;border-radius:6px;font-family:monospace;font-weight:700">
                                        AWB: {{ $ord->shipment->awb_number ?: 'Pending' }}
                                    </span>
                                    <span style="font-size:11.5px;background:#EFF6FF;color:#1E40AF;padding:3px 8px;border-radius:6px;font-weight:700">
                                        {{ $ord->shipment->formatted_status }}
                                    </span>
                                    @if($ord->shipment->etd)
                                        <span style="font-size:11px;color:#6B7280">
                                            • Est. Delivery: {{ \Carbon\Carbon::parse($ord->shipment->etd)->format('d M') }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
                                @foreach($ord->items->take(4) as $item)
                                    <span style="font-size:11px;background:var(--bg-body);padding:4px 10px;border-radius:6px;border:1px solid var(--border)">
                                        {{ $item->product_name }} × {{ $item->quantity }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div style="display:flex;gap:10px;flex-wrap:wrap">
                            <a href="{{ url('/order-details?id=' . $ord->id) }}" class="btn-outline" style="padding:8px 14px;font-size:12px">View Details</a>
                            <a href="{{ url('/track-order?id=' . $ord->id) }}" class="btn-primary" style="padding:8px 14px;font-size:12px">Track Order</a>
                            @if(in_array($ord->order_status, ['pending', 'confirmed']))
                                <form action="{{ url('/orders/' . $ord->id . '/cancel') }}" method="POST" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn-outline" style="padding:8px 14px;font-size:12px;color:var(--danger);border-color:var(--danger)">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <img src="{{ asset('assets/image/empty_box.png') }}" alt="No orders">
                        <h3>No orders found yet</h3>
                        <p>Start shopping fresh groceries from Getgozo Jadupur!</p>
                        <a href="{{ url('/shop') }}" class="btn-primary">Shop Groceries Now</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</main>
@endsection

