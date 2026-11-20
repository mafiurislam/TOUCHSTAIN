@extends('admin.layouts.master')

@section('title', 'Website CMS Pages')

@section('content')

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h1 class="admin-card-title">📄 Website CMS Pages Management</h1>
                <p style="font-size:12.5px;color:var(--text-muted);margin-top:2px">Edit website policy pages and content without touching source code</p>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:24px">
            @forelse($pages as $pg)
                <div style="background:#F8FAFC;border:1px solid var(--border);border-radius:12px;padding:22px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;border-bottom:1px solid var(--border);padding-bottom:10px">
                        <div>
                            <h3 style="font-size:16px;font-weight:800;color:var(--text-dark);margin:0">{{ $pg->title }}</h3>
                            <span style="font-size:11.5px;color:var(--text-muted)">Public URL: <code>{{ url('/' . $pg->slug) }}</code></span>
                        </div>
                        <a href="{{ url('/' . $pg->slug) }}" target="_blank" class="btn-secondary" style="font-size:12px">View Live ↗</a>
                    </div>

                    <form action="{{ route('admin.cms.update', $pg->id) }}" method="POST">
                        @csrf
                        <div style="margin-bottom:12px">
                            <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Page Title</label>
                            <input type="text" name="title" value="{{ $pg->title }}" required style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px">
                        </div>

                        <div style="margin-bottom:14px">
                            <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">HTML Content</label>
                            <textarea name="content" rows="6" required style="width:100%;padding:10px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:monospace">{{ $pg->content }}</textarea>
                        </div>

                        <div style="text-align:right">
                            <button type="submit" class="btn-theme" style="padding:8px 20px;font-size:12.5px">💾 Save Page Changes</button>
                        </div>
                    </form>
                </div>
            @empty
                <div style="text-align:center;padding:36px;color:#94A3B8">No CMS pages configured.</div>
            @endforelse
        </div>
    </div>

@endsection
