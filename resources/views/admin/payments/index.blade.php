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
                <p style="font-size:12px;color:var(--text-muted);margin:2px 0 0">Turn UPI Payment, Razorpay, or Cash on Delivery ON or OFF. Disabled methods are instantly hidden from checkout.</p>
            </div>
            <a href="{{ route('admin.settings') }}" class="btn-secondary" style="padding:6px 14px;font-size:12px">⚙️ All Store Settings</a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px">
            <!-- 1. Direct UPI Payment Card (New Feature) -->
            <div style="background:{{ $isUpiActive ? '#FAF5FF' : '#FEF2F2' }};border:1.5px solid {{ $isUpiActive ? '#C4B5FD' : '#FCA5A5' }};border-radius:12px;padding:16px;display:flex;justify-content:space-between;align-items:center;gap:14px">
                <div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:20px">📱</span>
                        <strong style="font-size:14.5px;color:#0F172A">UPI Payment</strong>
                    </div>
                    <div style="font-size:12px;color:{{ $isUpiActive ? '#5B21B6' : '#991B1B' }};font-weight:700;margin-top:4px">
                        {{ $isUpiActive ? '● Status: ENABLED (ON)' : '○ Status: DISABLED (OFF)' }}
                    </div>
                    <div style="font-size:11px;color:#64748B;margin-top:2px">PhonePe, GPay, Paytm, QR</div>
                </div>

                <form action="{{ route('admin.payments.toggle-method') }}" method="POST" style="margin:0">
                    @csrf
                    <input type="hidden" name="method" value="upi">
                    <input type="hidden" name="status" value="{{ $isUpiActive ? '0' : '1' }}">
                    <button type="submit" class="btn-theme" style="padding:7px 16px;font-size:12px;background:{{ $isUpiActive ? '#DC2626' : '#5B21B6' }};border-color:{{ $isUpiActive ? '#DC2626' : '#5B21B6' }}">
                        {{ $isUpiActive ? '🔴 Turn OFF' : '🟢 Turn ON' }}
                    </button>
                </form>
            </div>

            <!-- 2. Razorpay Gateway Card -->
            <div style="background:{{ $isRazorpayActive ? '#F0FDF4' : '#FEF2F2' }};border:1.5px solid {{ $isRazorpayActive ? '#86EFAC' : '#FCA5A5' }};border-radius:12px;padding:16px;display:flex;justify-content:space-between;align-items:center;gap:14px">
                <div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:20px">⚡</span>
                        <strong style="font-size:14.5px;color:#0F172A">Razorpay Online Payment</strong>
                    </div>
                    <div style="font-size:12px;color:{{ $isRazorpayActive ? '#166534' : '#991B1B' }};font-weight:700;margin-top:4px">
                        {{ $isRazorpayActive ? '● Status: ENABLED (ON)' : '○ Status: DISABLED (OFF)' }}
                    </div>
                    <div style="font-size:11px;color:#64748B;margin-top:2px">Cards, NetBanking, Wallets</div>
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

            <!-- 3. Cash on Delivery (COD) Card -->
            <div style="background:{{ $isCodActive ? '#F0FDF4' : '#FEF2F2' }};border:1.5px solid {{ $isCodActive ? '#86EFAC' : '#FCA5A5' }};border-radius:12px;padding:16px;display:flex;justify-content:space-between;align-items:center;gap:14px">
                <div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:20px">💵</span>
                        <strong style="font-size:14.5px;color:#0F172A">Cash on Delivery (COD)</strong>
                    </div>
                    <div style="font-size:12px;color:{{ $isCodActive ? '#166534' : '#991B1B' }};font-weight:700;margin-top:4px">
                        {{ $isCodActive ? '● Status: ENABLED (ON)' : '○ Status: DISABLED (OFF)' }}
                    </div>
                    <div style="font-size:11px;color:#64748B;margin-top:2px">Pay cash upon delivery</div>
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

        <!-- Dedicated UPI & QR Code Settings Form (Requested Feature) -->
        <div style="margin-top:20px;padding-top:20px;border-top:1.5px dashed #E2E8F0">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:22px">📷</span>
                    <div>
                        <h3 style="margin:0;font-size:14.5px;font-weight:800;color:#0F172A">UPI QR Code &amp; Payee Management</h3>
                        <p style="margin:2px 0 0;font-size:12px;color:#64748B">Change your UPI QR image anytime, edit UPI ID (VPA), or payee name. Updates instantly reflect on customer checkout.</p>
                    </div>
                </div>
                
                <form action="{{ route('admin.payments.delete-upi-qr') }}" method="POST" onsubmit="return confirm('Reset custom QR image back to default PhonePe QR?');" style="margin:0">
                    @csrf
                    <button type="submit" class="btn-secondary" style="padding:6px 12px;font-size:11.5px;color:#DC2626;border-color:#FCA5A5">
                        🗑️ Reset QR to Default PhonePe
                    </button>
                </form>
            </div>

            <form action="{{ route('admin.payments.update-upi') }}" method="POST" enctype="multipart/form-data" style="margin:0">
                @csrf
                <div style="display:grid;grid-template-columns:auto 1fr 1fr 1fr;gap:16px;align-items:center;background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:12px;padding:16px">
                    <!-- Current QR Preview -->
                    <div style="text-align:center">
                        <div style="background:#fff;border:1px solid #CBD5E1;border-radius:8px;padding:4px;display:inline-block">
                            <img src="{{ asset('storage/' . ($upiQrImage ?? 'payment/upi_phonepe_qr.jpg')) }}?v={{ time() }}" alt="Current QR" style="width:72px;height:72px;object-fit:contain;display:block">
                        </div>
                        <span style="display:block;font-size:10px;font-weight:700;color:#059669;margin-top:3px">Active QR</span>
                    </div>

                    <!-- UPI ID Input -->
                    <div>
                        <label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px;color:#334155">UPI ID (VPA) *</label>
                        <input type="text" name="upi_id" value="{{ $upiId ?? '7039214465-3@ybl' }}" required placeholder="e.g. 7039214465-3@ybl" style="width:100%;padding:8px 10px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:13px;font-weight:700;box-sizing:border-box">
                    </div>

                    <!-- Payee Name Input -->
                    <div>
                        <label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px;color:#334155">Payee Name *</label>
                        <input type="text" name="upi_name" value="{{ $upiName ?? 'Naushad Ali Meher Ali' }}" required placeholder="Account Holder Name" style="width:100%;padding:8px 10px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:13px;font-weight:700;box-sizing:border-box">
                    </div>

                    <!-- Upload New QR Image File -->
                    <div>
                        <label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px;color:#334155">Upload New QR Image</label>
                        <input type="file" name="qr_image" accept="image/*" style="width:100%;padding:6px;font-size:11.5px;border:1.5px solid #CBD5E1;border-radius:8px;background:#fff;box-sizing:border-box">
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;margin-top:12px">
                    <button type="submit" class="btn-theme" style="padding:9px 20px;font-size:13px;font-weight:700;background:#5B21B6;border-color:#5B21B6">
                        💾 Save UPI &amp; QR Settings
                    </button>
                </div>
            </form>
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
