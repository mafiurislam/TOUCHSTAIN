@extends('admin.layouts.master')

@section('title', 'Customer Enquiries & Chat Messages')

@section('content')

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">💬 Customer Enquiries & Live Support Messages</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Incoming customer support questions, product feedback, and order inquiries</p>
            </div>
        </div>

        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Time</th>
                        <th>Sender Name</th>
                        <th>Customer Message</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                        <tr>
                            <td style="color:var(--text-muted);font-weight:700">{{ $msg->id }}</td>
                            <td style="font-size:12px;color:var(--text-muted);white-space:nowrap">{{ $msg->created_at ? $msg->created_at->format('d M H:i') : 'N/A' }}</td>
                            <td>
                                <strong>{{ $msg->user ? $msg->user->name : 'Guest Customer' }}</strong>
                                <div style="font-size:11.5px;color:var(--text-muted)">{{ $msg->user ? $msg->user->phone : '' }}</div>
                            </td>
                            <td>
                                <div style="background:#F8FAFC;padding:10px 14px;border-radius:8px;border:1px solid var(--border);font-size:13px;line-height:1.4">
                                    {{ $msg->message }}
                                </div>
                            </td>
                            <td style="text-align:right">
                                @if($msg->user && $msg->user->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $msg->user->phone) }}" target="_blank" class="btn-theme" style="padding:4px 10px;font-size:11.5px;background:#10B981">
                                        💬 WhatsApp Reply
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:36px;color:#94A3B8">No customer enquiries recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px">
            {{ $messages->links() }}
        </div>
    </div>

@endsection
