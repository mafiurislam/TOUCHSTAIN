@extends('admin.layouts.master')

@section('title', 'Admin Profile & Security')

@section('content')

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start">
        
        <!-- Left: Account Details & Profile Info -->
        <div class="admin-card">
            <h2 class="admin-card-title" style="margin-bottom:16px">👤 Admin Profile Information</h2>
            
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Username (Fixed)</label>
                    <input type="text" value="{{ $user->username ?: 'admin12345' }}" readonly style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;background:#F8FAFC;font-family:monospace;font-weight:700">
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:20px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <button type="submit" class="btn-theme" style="width:100%;justify-content:center">
                    💾 Save Profile Changes
                </button>
            </form>

            <div style="margin-top:24px;border-top:1px solid #F1F5F9;padding-top:14px;font-size:12px;color:var(--text-muted)">
                <div>Role: <strong style="color:var(--primary);text-transform:uppercase">{{ $user->role }}</strong></div>
                <div style="margin-top:4px">Last Login: {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d M Y, h:i A') : 'N/A' }}</div>
                <div style="margin-top:2px">IP Address: <code>{{ $user->last_login_ip ?: '127.0.0.1' }}</code></div>
            </div>
        </div>

        <!-- Right: Change Password & Security Form -->
        <div class="admin-card">
            <h2 class="admin-card-title" style="margin-bottom:16px">🔒 Change Admin Password</h2>
            <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:18px">Ensure your new password uses at least 6 characters with mixed case and numbers.</p>

            <form action="{{ route('admin.profile.password') }}" method="POST">
                @csrf

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Current Password *</label>
                    <input type="password" name="current_password" required placeholder="Enter current password" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">New Password *</label>
                    <input type="password" name="new_password" required placeholder="Enter new password" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <div style="margin-bottom:20px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Confirm New Password *</label>
                    <input type="password" name="new_password_confirmation" required placeholder="Re-type new password" style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                </div>

                <button type="submit" class="btn-theme" style="width:100%;justify-content:center;background:#059669">
                    🔐 Update Password
                </button>
            </form>
        </div>

    </div>

@endsection
