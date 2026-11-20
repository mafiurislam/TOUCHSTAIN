@extends('admin.layouts.master')

@section('title', 'Customer Management')

@section('content')

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">👥 Customer Management</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Manage customer accounts, purchase history, and VIP designations</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <a href="{{ route('admin.customers.export') }}" class="btn-secondary">📥 Export CSV</a>
                <button type="button" onclick="openAddCustomerModal()" class="btn-theme">+ Add New Customer</button>
            </div>
        </div>

        <!-- Filter Ribbon -->
        <form action="{{ route('admin.customers') }}" method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;background:#F8FAFC;padding:14px;border-radius:12px;border:1px solid var(--border)">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer name, phone, or email..." class="search-input" style="flex:1.5;min-width:200px">
            
            <select name="status" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
            </select>

            <button type="submit" class="btn-theme" style="padding:9px 18px">Filter</button>
            <a href="{{ route('admin.customers') }}" class="btn-secondary">Reset</a>
        </form>

        <!-- Customers Table -->
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Customer Details</th>
                        <th>Contact Info</th>
                        <th>Total Orders</th>
                        <th>Total Spent (₹)</th>
                        <th>Customer Tier</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td style="color:var(--text-muted);font-weight:700">{{ $c->id }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div style="width:36px;height:36px;border-radius:50%;background:#EFF6FF;color:var(--primary);font-weight:800;display:flex;align-items:center;justify-content:center;font-size:13px">
                                        {{ strtoupper(substr($c->name ?: 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:700;font-size:13.5px;color:var(--text-dark)">{{ $c->name }}</div>
                                        <div style="font-size:11px;color:var(--text-muted)">Joined: {{ $c->created_at ? $c->created_at->format('d M Y') : 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:700;font-size:13px">{{ $c->phone }}</div>
                                <div style="font-size:11.5px;color:var(--text-muted)">{{ $c->email }}</div>
                            </td>
                            <td style="font-weight:800;font-size:13.5px">{{ $c->orders_count }}</td>
                            <td style="font-weight:800;font-size:14px;color:var(--primary)">₹{{ number_format($c->total_spent, 2) }}</td>
                            <td>
                                @if($c->is_vip)
                                    <span class="status-badge" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D">⭐ VIP Buyer</span>
                                @else
                                    <span class="status-badge status-info">Regular</span>
                                @endif
                            </td>
                            <td>
                                @if($c->status === 'active')
                                    <span class="status-badge status-success">Active</span>
                                @else
                                    <span class="status-badge status-danger">Blocked</span>
                                @endif
                            </td>
                            <td style="text-align:right">
                                <div style="display:inline-flex;gap:6px">
                                    <form action="{{ route('admin.customers.toggle-status', $c->id) }}" method="POST" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:4px 8px;font-size:11px">
                                            {{ $c->status === 'active' ? 'Block' : 'Unblock' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.customers.delete', $c->id) }}" method="POST" onsubmit="return confirm('Delete customer {{ addslashes($c->name) }}?')" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:4px 8px;font-size:11px;color:#DC2626">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:36px;color:#94A3B8">No customer accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px">
            {{ $customers->links() }}
        </div>
    </div>

    <!-- Modal to Add Customer -->
    <div id="addCustomerModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:9999;padding:20px">
        <div style="background:#fff;border-radius:14px;max-width:480px;width:100%;padding:26px;box-shadow:var(--shadow-lg)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #F1F5F9;padding-bottom:12px">
                <h3 style="font-size:16px;font-weight:800;color:var(--text-dark);margin:0">👤 Add New Customer</h3>
                <button type="button" onclick="closeAddCustomerModal()" style="border:none;background:none;font-size:20px;cursor:pointer;color:#64748B">&times;</button>
            </div>

            <form action="{{ route('admin.customers.store') }}" method="POST">
                @csrf
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Full Name *</label>
                        <input type="text" name="name" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Phone Number *</label>
                        <input type="text" name="phone" placeholder="+91..." required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Email (Optional)</label>
                        <input type="email" name="email" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Initial Password (Default: 12345678)</label>
                        <input type="password" name="password" placeholder="12345678" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
                    <button type="button" onclick="closeAddCustomerModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-theme">Create Account</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openAddCustomerModal() {
        document.getElementById('addCustomerModal').style.display = 'flex';
    }
    function closeAddCustomerModal() {
        document.getElementById('addCustomerModal').style.display = 'none';
    }
</script>
@endpush
