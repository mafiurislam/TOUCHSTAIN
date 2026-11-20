@extends('admin.layouts.master')

@section('title', 'Staff & Role Management')

@section('content')

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">🛡️ Staff & Role Management</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Manage admin staff, assign operational roles, and enforce security</p>
            </div>
            <div style="display:flex;gap:10px">
                <a href="{{ route('admin.audit-logs') }}" class="btn-secondary">🔒 Security Audit Trail</a>
                <button type="button" onclick="openAddStaffModal()" class="btn-theme">+ Add Staff Member</button>
            </div>
        </div>

        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Staff Name</th>
                        <th>Username</th>
                        <th>Email & Phone</th>
                        <th>Assigned Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffMembers as $stf)
                        <tr>
                            <td style="color:var(--text-muted);font-weight:700">{{ $stf->id }}</td>
                            <td>
                                <strong style="font-size:13.5px">{{ $stf->name }}</strong>
                            </td>
                            <td><code style="font-weight:700;font-size:12.5px">{{ $stf->username ?: 'admin12345' }}</code></td>
                            <td>
                                <div>{{ $stf->email }}</div>
                                <div style="font-size:11.5px;color:var(--text-muted)">{{ $stf->phone }}</div>
                            </td>
                            <td>
                                <span class="status-badge status-info">
                                    {{ strtoupper(str_replace('_', ' ', $stf->role)) }}
                                </span>
                            </td>
                            <td>
                                @if($stf->status === 'active')
                                    <span class="status-badge status-success">Active</span>
                                @else
                                    <span class="status-badge status-danger">Blocked</span>
                                @endif
                            </td>
                            <td style="font-size:12px;color:var(--text-muted)">
                                {{ $stf->last_login_at ? \Carbon\Carbon::parse($stf->last_login_at)->diffForHumans() : 'Never' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Adding Staff -->
    <div id="addStaffModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:9999;padding:20px">
        <div style="background:#fff;border-radius:14px;max-width:480px;width:100%;padding:26px;box-shadow:var(--shadow-lg)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #F1F5F9;padding-bottom:12px">
                <h3 style="font-size:16px;font-weight:800;color:var(--text-dark);margin:0">🛡️ Add Administrative Staff</h3>
                <button type="button" onclick="closeAddStaffModal()" style="border:none;background:none;font-size:20px;cursor:pointer;color:#64748B">&times;</button>
            </div>

            <form action="{{ route('admin.staff.store') }}" method="POST">
                @csrf
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Full Name *</label>
                        <input type="text" name="name" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Login Username *</label>
                        <input type="text" name="username" required placeholder="e.g. manager_rahul" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Email *</label>
                        <input type="email" name="email" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Phone Number *</label>
                        <input type="text" name="phone" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Assigned Role *</label>
                        <select name="role" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                            <option value="manager">Manager (Orders & Products)</option>
                            <option value="staff">Customer Support Staff</option>
                            <option value="super_admin">Super Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:4px">Initial Password *</label>
                        <input type="password" name="password" required placeholder="••••••••••••" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
                    <button type="button" onclick="closeAddStaffModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-theme">Create Staff Member</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openAddStaffModal() {
        document.getElementById('addStaffModal').style.display = 'flex';
    }
    function closeAddStaffModal() {
        document.getElementById('addStaffModal').style.display = 'none';
    }
</script>
@endpush
