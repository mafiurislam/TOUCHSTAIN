@extends('admin.layouts.master')

@section('title', 'Order Management')

@section('content')

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">🧾 Order Management</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Track, process, dispatch, and manage invoices for customer orders</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <a href="{{ url('/admin/delivery') }}" class="btn-theme" style="background:#059669">🚚 Shiprocket Logistics →</a>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:14px">
            @php
                $currStatus = request('status', 'all');
            @endphp
            <a href="{{ route('admin.orders') }}" class="btn-secondary {{ $currStatus === 'all' ? 'active-pill' : '' }}" style="{{ $currStatus === 'all' ? 'background:var(--primary);color:#fff;font-weight:700' : '' }}">
                All ({{ $statusCounts['all'] }})
            </a>
            <a href="{{ route('admin.orders', ['status' => 'pending']) }}" class="btn-secondary" style="{{ $currStatus === 'pending' ? 'background:#F59E0B;color:#fff;font-weight:700' : '' }}">
                ⏳ Pending ({{ $statusCounts['pending'] }})
            </a>
            <a href="{{ route('admin.orders', ['status' => 'confirmed']) }}" class="btn-secondary" style="{{ $currStatus === 'confirmed' ? 'background:#3B82F6;color:#fff;font-weight:700' : '' }}">
                ✓ Confirmed ({{ $statusCounts['confirmed'] }})
            </a>
            <a href="{{ route('admin.orders', ['status' => 'processing']) }}" class="btn-secondary" style="{{ $currStatus === 'processing' ? 'background:#8B5CF6;color:#fff;font-weight:700' : '' }}">
                📦 Processing ({{ $statusCounts['processing'] }})
            </a>
            <a href="{{ route('admin.orders', ['status' => 'out_for_delivery']) }}" class="btn-secondary" style="{{ $currStatus === 'out_for_delivery' ? 'background:#06B6D4;color:#fff;font-weight:700' : '' }}">
                🚚 Out for Delivery ({{ $statusCounts['out_for_delivery'] }})
            </a>
            <a href="{{ route('admin.orders', ['status' => 'delivered']) }}" class="btn-secondary" style="{{ $currStatus === 'delivered' ? 'background:#10B981;color:#fff;font-weight:700' : '' }}">
                🏠 Delivered ({{ $statusCounts['delivered'] }})
            </a>
            <a href="{{ route('admin.orders', ['status' => 'canceled']) }}" class="btn-secondary" style="{{ $currStatus === 'canceled' ? 'background:#EF4444;color:#fff;font-weight:700' : '' }}">
                ✕ Canceled ({{ $statusCounts['canceled'] }})
            </a>
        </div>

        <!-- Filter Ribbon -->
        <form action="{{ route('admin.orders') }}" method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;background:#F8FAFC;padding:14px;border-radius:12px;border:1px solid var(--border)">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Order ID, Customer Name, or Phone..." class="search-input" style="flex:1.5;min-width:220px">
            
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff">

            <button type="submit" class="btn-theme" style="padding:9px 18px">Filter</button>
            <a href="{{ route('admin.orders') }}" class="btn-secondary">Reset</a>
        </form>

        <!-- Orders Table with Bulk Form -->
        <form action="{{ route('admin.orders.bulk-status') }}" method="POST">
            @csrf

            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap">
                <span style="font-size:12.5px;font-weight:700;color:#475569">Bulk Actions:</span>
                <select name="bulk_status" required style="padding:7px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:12.5px;background:#fff">
                    <option value="">Select Target Status</option>
                    <option value="confirmed">Mark as Confirmed</option>
                    <option value="processing">Mark as Processing</option>
                    <option value="out_for_delivery">Mark as Out for Delivery</option>
                    <option value="delivered">Mark as Delivered</option>
                    <option value="canceled">Mark as Canceled</option>
                </select>
                <button type="submit" class="btn-secondary" style="padding:7px 14px">Apply to Selected</button>
            </div>

            <div style="overflow-x:auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:30px"><input type="checkbox" id="checkAll" onclick="toggleSelectAll(this)" style="accent-color:var(--primary)"></th>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Customer Info</th>
                            <th>Payment</th>
                            <th>Total Amount</th>
                            <th>Order Status</th>
                            <th>Shipment / Logistics</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $ord)
                            <tr>
                                <td>
                                    <input type="checkbox" name="order_ids[]" value="{{ $ord->id }}" class="order-checkbox" style="accent-color:var(--primary)">
                                </td>
                                <td style="font-weight:800;font-family:monospace;color:var(--primary)">
                                    <a href="{{ route('admin.orders.show', $ord->id) }}" style="color:var(--primary);text-decoration:none">
                                        #{{ $ord->id }}
                                    </a>
                                </td>
                                <td style="font-size:12px;color:var(--text-muted);white-space:nowrap">
                                    {{ $ord->created_at ? $ord->created_at->format('d M Y, h:i A') : 'N/A' }}
                                </td>
                                <td>
                                    <div style="font-weight:700;font-size:13px">{{ $ord->getCustomerName() }}</div>
                                    <div style="font-size:11.5px;color:var(--text-muted)">{{ $ord->getCustomerPhone() }}</div>
                                </td>
                                <td>
                                    <div style="font-size:12px;font-weight:700">{{ strtoupper(str_replace('_', ' ', $ord->payment_method)) }}</div>
                                    <span style="font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:700;background:{{ $ord->payment_status === 'paid' ? '#DCFCE7' : '#FEF3C7' }};color:{{ $ord->payment_status === 'paid' ? '#166534' : '#92400E' }}">
                                        {{ strtoupper($ord->payment_status) }}
                                    </span>
                                </td>
                                <td style="font-weight:800;font-size:14px">₹{{ number_format($ord->order_amount, 2) }}</td>
                                <td>
                                    @php
                                        $st = $ord->order_status;
                                    @endphp
                                    <span class="status-badge {{ $st === 'delivered' ? 'status-success' : ($st === 'canceled' ? 'status-danger' : ($st === 'confirmed' ? 'status-info' : 'status-warning')) }}">
                                        {{ str_replace('_', ' ', $st) }}
                                    </span>
                                </td>
                                <td>
                                    @if($ord->shipment)
                                        <div style="font-size:11.5px">
                                            @if($ord->shipment->awb_number)
                                                <span style="font-weight:700;color:#047857">🚚 {{ $ord->shipment->courier_name ?: 'Shiprocket' }}</span>
                                                <div style="font-family:monospace;font-weight:600;color:#334155">{{ $ord->shipment->awb_number }}</div>
                                            @else
                                                <span style="font-weight:700;color:#2563EB">📦 Shiprocket #{{ $ord->shipment->shiprocket_order_id }}</span>
                                                <form action="{{ route('admin.delivery.shipments.generate-awb', $ord->shipment->id) }}" method="POST" style="margin-top:3px">
                                                    @csrf
                                                    <button type="submit" class="btn-theme" style="padding:2px 8px;font-size:10.5px;background:#2563EB" title="Book Courier & Schedule Pickup">
                                                        🚀 Ship Now
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <form action="{{ route('admin.delivery.orders.create-shipment', $ord->id) }}" method="POST" style="margin:0">
                                            @csrf
                                            <button type="submit" class="btn-secondary" style="padding:3px 8px;font-size:11px;border-color:#01684B;color:#01684B;font-weight:600" title="Send to Shiprocket">
                                                🚚 To Shiprocket
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td style="text-align:right">
                                    <div style="display:inline-flex;gap:6px;align-items:center">
                                        <a href="{{ route('admin.orders.show', $ord->id) }}" class="btn-secondary" style="padding:5px 10px;font-size:11.5px">Manage</a>
                                        <a href="{{ route('admin.orders.invoice', $ord->id) }}" target="_blank" class="btn-secondary" style="padding:5px 10px;font-size:11.5px" title="Print Invoice">🖨️</a>
                                        <form action="{{ route('admin.orders.delete', $ord->id) }}" method="POST" style="margin:0" onsubmit="return confirm('Are you sure you want to delete Order #{{ $ord->id }}? This action cannot be undone.')">
                                            @csrf
                                            <button type="submit" class="btn-secondary" style="padding:5px 9px;font-size:11.5px;color:#DC2626;border-color:#FCA5A5;background:#FEF2F2" title="Delete Order">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center;padding:36px;color:#94A3B8">No orders found matching criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px">
                {{ $orders->links() }}
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
    function toggleSelectAll(master) {
        document.querySelectorAll('.order-checkbox').forEach(cb => {
            cb.checked = master.checked;
        });
    }
</script>
@endpush
