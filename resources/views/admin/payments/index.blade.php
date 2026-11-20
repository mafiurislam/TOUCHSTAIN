@extends('admin.layouts.master')

@section('title', 'Razorpay & COD Payments')

@section('content')

    <!-- KPIs -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
        <div class="kpi-white-card">
            <div class="kpi-title">Total Transactions</div>
            <div class="kpi-number" style="color:var(--primary);margin-top:4px">{{ $stats['total'] }}</div>
        </div>
        <div class="kpi-white-card">
            <div class="kpi-title">Confirmed / Paid</div>
            <div class="kpi-number" style="color:#10B981;margin-top:4px">{{ $stats['confirmed'] }}</div>
        </div>
        <div class="kpi-white-card">
            <div class="kpi-title">Pending / COD</div>
            <div class="kpi-number" style="color:#D97706;margin-top:4px">{{ $stats['pending'] }}</div>
        </div>
        <div class="kpi-white-card">
            <div class="kpi-title">Failed / Canceled</div>
            <div class="kpi-number" style="color:#DC2626;margin-top:4px">{{ $stats['rejected'] }}</div>
        </div>
    </div>

    <!-- Active Razorpay Gateway Ribbon -->
    <div style="background:linear-gradient(135deg, #02042B, #1D4ED8);color:#fff;border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
        <div>
            <div style="font-size:11.5px;text-transform:uppercase;letter-spacing:1px;opacity:0.85">Active Payment Methods</div>
            <div style="font-size:16px;font-weight:800;margin-top:2px">◆ Razorpay Payment Gateway &amp; Cash on Delivery (COD)</div>
            <div style="font-size:12.5px;opacity:0.9;margin-top:2px">
                Razorpay API Key ID: <code style="background:rgba(255,255,255,0.15);padding:2px 8px;border-radius:4px;font-weight:700">{{ config('services.razorpay.key_id', 'rzp_test_U1cQQReArMfXP9') }}</code>
            </div>
        </div>
        <span class="status-badge status-success" style="background:#ECFDF5;color:#065F46">● Razorpay &amp; COD Active</span>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">💳 Razorpay &amp; Cash on Delivery Payment Log</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">View all Razorpay online payment transactions and Cash on Delivery orders</p>
            </div>
        </div>

        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Razorpay Payment ID</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $tr)
                        <tr>
                            <td style="font-weight:700">#{{ $tr->id }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $tr->order_id) }}" style="font-weight:800;color:var(--primary);text-decoration:none">
                                    #{{ $tr->order_id }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight:700;font-size:13px">{{ $tr->user->name ?? ($tr->order->contact_person_name ?? 'Customer') }}</div>
                                <div style="font-size:11.5px;color:var(--text-muted)">{{ $tr->user->phone ?? ($tr->order->contact_person_number ?? '') }}</div>
                            </td>
                            <td style="font-weight:800;font-size:14px">₹{{ number_format($tr->amount, 2) }}</td>
                            <td>
                                @if($tr->utr_number)
                                    <code style="background:#EFF6FF;color:#1E40AF;padding:3px 8px;border-radius:6px;font-weight:800;font-size:12px">{{ $tr->utr_number }}</code>
                                @else
                                    <span style="color:#94A3B8;font-size:12px">COD</span>
                                @endif
                            </td>
                            <td>
                                @if($tr->status === 'payment_confirmed')
                                    <span class="status-badge status-success">Paid / Confirmed</span>
                                @elseif($tr->status === 'payment_rejected')
                                    <span class="status-badge status-danger">Failed</span>
                                @else
                                    <span class="status-badge status-warning">Pending</span>
                                @endif
                            </td>
                            <td style="text-align:right">
                                <a href="{{ route('admin.orders.show', $tr->order_id) }}" class="btn-secondary" style="padding:5px 10px;font-size:11.5px">View Order</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:36px;color:#94A3B8">No payment transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px">
            {{ $transfers->links() }}
        </div>
    </div>

@endsection
