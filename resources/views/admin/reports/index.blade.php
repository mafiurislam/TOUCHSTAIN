@extends('admin.layouts.master')

@section('title', 'Reports & Financial Analytics')

@section('content')

    <!-- Financial Summary Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px">
        <div class="kpi-white-card">
            <div class="kpi-title">Gross Revenue (Selected Period)</div>
            <div class="kpi-number" style="color:var(--primary);margin-top:4px">₹{{ number_format($totalSales, 2) }}</div>
            <div class="kpi-trend">From {{ $ordersCount }} Orders</div>
        </div>

        <div class="kpi-white-card">
            <div class="kpi-title">Estimated Net Profit</div>
            <div class="kpi-number" style="color:#059669;margin-top:4px">₹{{ number_format($netProfit, 2) }}</div>
            <div class="kpi-trend" style="background:#ECFDF5;color:#065F46">After Cost & Discounts</div>
        </div>

        <div class="kpi-white-card">
            <div class="kpi-title">Discounts Allowed</div>
            <div class="kpi-number" style="color:#D97706;margin-top:4px">₹{{ number_format($discountsGiven, 2) }}</div>
            <div class="kpi-trend" style="background:#FEF3C7;color:#92400E">Promotional Vouchers</div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">📈 Financial Sales Analytics</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Filter revenue and order volume by customized date ranges</p>
            </div>
            <div style="display:flex;gap:10px">
                <button type="button" onclick="window.print()" class="btn-secondary">🖨️ Print Report</button>
            </div>
        </div>

        <!-- Date Range Filter -->
        <form action="{{ route('admin.reports') }}" method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;background:#F8FAFC;padding:14px;border-radius:12px;border:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:8px">
                <label style="font-size:12.5px;font-weight:700">From:</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px">
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <label style="font-size:12.5px;font-weight:700">To:</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px">
            </div>
            <button type="submit" class="btn-theme" style="padding:8px 18px">Generate Report</button>
        </form>

        <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:24px;align-items:start">
            
            <!-- Daily Sales Table -->
            <div>
                <h3 style="font-size:15px;font-weight:800;color:var(--text-dark);margin-bottom:12px">Daily Sales Breakdown</h3>
                <div style="overflow-x:auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders Count</th>
                                <th style="text-align:right">Revenue (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyReports as $rep)
                                <tr>
                                    <td style="font-weight:700">{{ \Carbon\Carbon::parse($rep->report_date)->format('d M Y (D)') }}</td>
                                    <td>{{ $rep->total_orders }} orders</td>
                                    <td style="text-align:right;font-weight:800;color:var(--primary)">₹{{ number_format($rep->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:24px;color:#94A3B8">No orders in selected date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Product Performance Table -->
            <div>
                <h3 style="font-size:15px;font-weight:800;color:var(--text-dark);margin-bottom:12px">Top Revenue Products</h3>
                <div style="overflow-x:auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units</th>
                                <th style="text-align:right">Revenue (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productStats as $ps)
                                <tr>
                                    <td><strong>{{ \Illuminate\Support\Str::limit($ps->product_name, 22) }}</strong></td>
                                    <td>{{ $ps->units_sold }}</td>
                                    <td style="text-align:right;font-weight:800">₹{{ number_format($ps->revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:24px;color:#94A3B8">No product data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection
