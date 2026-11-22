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

    <!-- Payment Methods On / Off Switch Panel -->
    <div style="background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:20px;margin-bottom:24px;box-shadow:0 2px 10px rgba(0,0,0,0.04)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
            <div>
                <h2 style="font-size:16px;font-weight:800;color:#0F172A;margin:0">🎛️ Payment Methods Control Panel</h2>
                <p style="font-size:12px;color:var(--text-muted);margin:2px 0 0">Turn online payments (Razorpay) or Cash on Delivery ON or OFF. Disabled methods are instantly hidden from checkout.</p>
            </div>
            <a href="{{ route('admin.settings') }}" class="btn-secondary" style="padding:6px 14px;font-size:12px">⚙️ All Store Settings</a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:16px">
            <!-- 1. Razorpay Gateway Card -->
            <div style="background:{{ $isRazorpayActive ? '#F0FDF4' : '#FEF2F2' }};border:1.5px solid {{ $isRazorpayActive ? '#86EFAC' : '#FCA5A5' }};border-radius:12px;padding:16px;display:flex;justify-content:space-between;align-items:center;gap:14px">
                <div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:20px">⚡</span>
                        <strong style="font-size:14.5px;color:#0F172A">Razorpay Online Payment</strong>
                    </div>
                    <div style="font-size:12px;color:{{ $isRazorpayActive ? '#166534' : '#991B1B' }};font-weight:700;margin-top:4px">
                        {{ $isRazorpayActive ? '● Status: ENABLED (ON)' : '○ Status: DISABLED (OFF)' }}
                    </div>
                    <div style="font-size:11px;color:#64748B;margin-top:2px">UPI, Cards, NetBanking, Wallets</div>
                </div>

                <form action="{{ route('admin.payments.toggle-method') }}" method="POST" style="margin:0">
                    @csrf
                    <input type="hidden" name="method" value="razorpay">
                    <input type="hidden" name="status" value="{{ $isRazorpayActive ? '0' : '1' }}">
                    <button type="submit" class="btn-theme" style="padding:7px 16px;font-size:12px;background:{{ $isRazorpayActive ? '#DC2626' : '#059669' }};border-color:{{ $isRazorpayActive ? '#DC2626' : '#059669' }}">
                        {{ $isRazorpayActive ? '🔴 Turn OFF' : '🟢 Turn ON' }}
                    </button>
                </form>
            </div>

            <!-- 2. Cash on Delivery (COD) Card -->
            <div style="background:{{ $isCodActive ? '#F0FDF4' : '#FEF2F2' }};border:1.5px solid {{ $isCodActive ? '#86EFAC' : '#FCA5A5' }};border-radius:12px;padding:16px;display:flex;justify-content:space-between;align-items:center;gap:14px">
                <div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:20px">💵</span>
                        <strong style="font-size:14.5px;color:#0F172A">Cash on Delivery (COD)</strong>
                    </div>
                    <div style="font-size:12px;color:{{ $isCodActive ? '#166534' : '#991B1B' }};font-weight:700;margin-top:4px">
                        {{ $isCodActive ? '● Status: ENABLED (ON)' : '○ Status: DISABLED (OFF)' }}
                    </div>
                    <div style="font-size:11px;color:#64748B;margin-top:2px">Pay cash or rider UPI upon delivery</div>
                </div>

                <form action="{{ route('admin.payments.toggle-method') }}" method="POST" style="margin:0">
                    @csrf
                    <input type="hidden" name="method" value="cod">
                    <input type="hidden" name="status" value="{{ $isCodActive ? '0' : '1' }}">
                    <button type="submit" class="btn-theme" style="padding:7px 16px;font-size:12px;background:{{ $isCodActive ? '#DC2626' : '#059669' }};border-color:{{ $isCodActive ? '#DC2626' : '#059669' }}">
                        {{ $isCodActive ? '🔴 Turn OFF' : '🟢 Turn ON' }}
                    </button>
                </form>
            </div>
        </div>
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
