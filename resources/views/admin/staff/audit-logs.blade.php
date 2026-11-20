@extends('admin.layouts.master')

@section('title', 'Security & Audit Logs')

@section('content')

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">🔒 Security & Administrative Audit Trail</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Detailed record of administrative actions, logins, status modifications, and sensitive operations</p>
            </div>
            <a href="{{ route('admin.staff') }}" class="btn-secondary">← Back to Staff</a>
        </div>

        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td style="color:var(--text-muted);font-weight:700">{{ $log->id }}</td>
                            <td style="font-size:12px;color:var(--text-muted);white-space:nowrap">
                                {{ $log->created_at ? $log->created_at->format('d M Y, h:i:s A') : 'N/A' }}
                            </td>
                            <td>
                                <strong>{{ $log->user_name ?: 'System' }}</strong>
                            </td>
                            <td>
                                <span class="status-badge status-info">{{ $log->module }}</span>
                            </td>
                            <td style="font-weight:700">{{ $log->action }}</td>
                            <td style="font-size:12px;color:#334155;max-width:350px">
                                {{ $log->details ?: 'N/A' }}
                            </td>
                            <td>
                                <code style="font-size:11.5px">{{ $log->ip_address ?: '127.0.0.1' }}</code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:36px;color:#94A3B8">No security audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px">
            {{ $logs->links() }}
        </div>
    </div>

@endsection
