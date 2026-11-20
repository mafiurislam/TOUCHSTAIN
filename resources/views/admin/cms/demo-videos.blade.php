@extends('admin.layouts.master')

@section('title', 'Live Action Demo Videos')

@push('styles')
<style>
    .demo-admin-layout {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 24px;
        align-items: start;
    }
    .demo-video-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(235px, 1fr));
        gap: 18px;
    }
    .demo-video-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        display: flex;
        flex-direction: column;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }
    .demo-video-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.09);
    }
    .demo-video-card.is-hidden-card {
        opacity: 0.72;
        border-style: dashed;
    }
    .demo-player-wrap {
        position: relative;
        background: #0F172A;
        height: 250px;
        overflow: hidden;
    }
    .demo-player-wrap video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .demo-tag-overlay {
        position: absolute;
        top: 10px;
        left: 10px;
        background: linear-gradient(135deg, #EF4444, #DC2626);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 999px;
        z-index: 2;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        pointer-events: none;
    }
    .demo-order-overlay {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(15, 23, 42, 0.78);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 8px;
        z-index: 2;
        pointer-events: none;
    }
    .demo-card-body {
        padding: 13px 14px 14px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1;
    }
    .demo-card-title {
        font-size: 14px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.3;
        margin: 0;
    }
    .demo-card-sub {
        font-size: 12px;
        color: #64748B;
        line-height: 1.35;
    }
    .demo-card-meta {
        font-size: 11.5px;
        color: #059669;
        font-weight: 700;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .demo-card-actions {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 6px;
        margin-top: auto;
        padding-top: 10px;
        border-top: 1px solid #F1F5F9;
    }
    .demo-action-btn {
        border: 1.5px solid var(--border);
        background: #fff;
        color: #1E293B;
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        text-align: center;
        transition: all 0.14s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    .demo-action-btn:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
    }
    .demo-action-btn.edit-btn {
        background: #EFF6FF;
        color: #1D4ED8;
        border-color: #BFDBFE;
    }
    .demo-action-btn.edit-btn:hover {
        background: #DBEAFE;
    }
    .demo-action-btn.delete-btn {
        background: #FEF2F2;
        color: #DC2626;
        border-color: #FECACA;
        padding: 6px 10px;
    }
    .demo-action-btn.delete-btn:hover {
        background: #FEE2E2;
    }
    .demo-input {
        width: 100%;
        padding: 9px 12px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-size: 13px;
        background: #fff;
        color: #0F172A;
        box-sizing: border-box;
    }
    .demo-input:focus {
        outline: none;
        border-color: #01684B;
        box-shadow: 0 0 0 3px rgba(1, 104, 75, 0.1);
    }
    .demo-label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        display: block;
        margin-bottom: 5px;
    }
    .demo-upload-drop {
        border: 2px dashed #CBD5E1;
        border-radius: 10px;
        padding: 12px;
        background: #F8FAFC;
        text-align: center;
    }
    .demo-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.62);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 18px;
        backdrop-filter: blur(2px);
    }
    .demo-modal-backdrop.open {
        display: flex;
    }
    .demo-modal-box {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 560px;
        max-height: 92vh;
        overflow-y: auto;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.25);
    }
    .demo-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 5;
    }
    .demo-modal-body {
        padding: 18px 20px 22px;
    }
    @media (max-width: 1080px) {
        .demo-admin-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

    @php
        $totalCount = $demoVideos->count();
        $activeCount = $demoVideos->where('status', true)->count();
        $hiddenCount = $totalCount - $activeCount;
    @endphp

    <!-- Header Summary Bar -->
    <div class="admin-card" style="margin-bottom:20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px">
        <div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <span style="display:inline-flex;align-items:center;gap:6px;background:#EF4444;color:#fff;font-size:11px;font-weight:800;padding:4px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:0.4px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#fff;display:inline-block"></span>
                    Live Action Demos
                </span>
                <h2 class="admin-card-title" style="margin:0">Instant Stain Vanish • Live Demo Videos</h2>
            </div>
            <p style="font-size:12.5px;color:var(--text-muted);margin:6px 0 0">
                Upload new videos, replace existing clips, edit titles/tags/linked products, reorder, hide, or delete videos shown on the homepage.
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <span class="status-badge status-info">Total: {{ $totalCount }}</span>
            <span class="status-badge status-success">Active: {{ $activeCount }}</span>
            @if($hiddenCount > 0)
                <span class="status-badge status-warning">Hidden: {{ $hiddenCount }}</span>
            @endif
            <a href="{{ url('/#live-demos') }}" target="_blank" class="btn-secondary" style="text-decoration:none;font-size:12.5px;padding:7px 12px">
                👁️ Preview on Storefront →
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div style="background:#FEF2F2;border:1.5px solid #FECACA;color:#991B1B;border-radius:12px;padding:12px 16px;margin-bottom:18px;font-size:13px">
            <strong>⚠️ Please fix the following:</strong>
            <ul style="margin:6px 0 0 18px;padding:0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="demo-admin-layout">

        <!-- LEFT COLUMN: Upload / Create New Demo Video -->
        <div class="admin-card">
            <h2 class="admin-card-title" style="margin-bottom:4px">🎬 Upload New Live Demo Video</h2>
            <p style="font-size:12px;color:var(--text-muted);margin:0 0 16px">
                Supports vertical or landscape MP4 / WebM / MOV videos.
            </p>

            <form action="{{ route('admin.demo-videos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom:13px">
                    <label class="demo-label">Video File (MP4 / WebM / MOV) *</label>
                    <div class="demo-upload-drop">
                        <input type="file" name="video_file" id="createVideoFile" accept="video/mp4,video/webm,video/quicktime,video/x-m4v,video/ogg" style="width:100%;font-size:12.5px">
                        <div style="font-size:11px;color:#64748B;margin-top:6px">
                            Server max upload: <strong>{{ ini_get('upload_max_filesize') ?: '2M' }}</strong> • Recommended: short 5–30s MP4 reel
                        </div>
                        <video id="createVideoPreview" controls muted playsinline style="display:none;width:100%;max-height:180px;margin-top:10px;border-radius:8px;background:#0F172A"></video>
                    </div>
                </div>

                <div style="margin-bottom:13px">
                    <label class="demo-label">OR Video URL / Existing Path <span style="font-weight:500;color:#64748B">(optional if uploading file)</span></label>
                    <input type="text" name="video_url" value="{{ old('video_url') }}" placeholder="e.g. https://...mp4 or videos/touchstains-demo-1.mp4" class="demo-input">
                </div>

                <div style="display:grid;grid-template-columns:1fr 90px;gap:10px;margin-bottom:13px">
                    <div>
                        <label class="demo-label">Tag Badge Pill</label>
                        <input type="text" name="tag_label" value="{{ old('tag_label') }}" placeholder="e.g. Ink Demo, Rust Demo" class="demo-input" maxlength="80">
                    </div>
                    <div>
                        <label class="demo-label">Order #</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $nextOrder) }}" min="0" class="demo-input">
                    </div>
                </div>

                <div style="margin-bottom:13px">
                    <label class="demo-label">Video Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Ballpen & Ink Stain Removal" class="demo-input" maxlength="200">
                </div>

                <div style="margin-bottom:13px">
                    <label class="demo-label">Subtitle / Short Proof Line</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle') }}" placeholder="e.g. Instant Results • Live Proof" class="demo-input" maxlength="220">
                </div>

                <div style="margin-bottom:14px">
                    <label class="demo-label">Linked Product ("View Product →" Button)</label>
                    <select name="product_id" class="demo-input">
                        <option value="">-- All Products (/products) --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                #{{ $prod->id }} — {{ \Illuminate\Support\Str::limit($prod->name, 46) }} (₹{{ number_format((float)$prod->price, 0) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:#0F172A;margin-bottom:16px;cursor:pointer">
                    <input type="checkbox" name="status" value="1" checked style="width:16px;height:16px;accent-color:#01684B">
                    <span>Publish immediately on Homepage</span>
                </label>

                <button type="submit" class="btn-theme" style="width:100%;justify-content:center;padding:11px 16px;font-size:13.5px">
                    📤 Upload &amp; Publish Demo Video
                </button>
            </form>
        </div>

        <!-- RIGHT COLUMN: Manage Existing Live Demo Videos -->
        <div class="admin-card">
            <div class="admin-card-header" style="margin-bottom:16px">
                <div>
                    <h2 class="admin-card-title">🎞️ Manage Homepage Live Videos ({{ $totalCount }})</h2>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:2px">
                        Click <strong>Edit</strong> to change video clip, title, subtitle, tag, linked product, or display order.
                    </p>
                </div>
            </div>

            <div class="demo-video-grid">
                @forelse($demoVideos as $video)
                    <div class="demo-video-card {{ !$video->status ? 'is-hidden-card' : '' }}">
                        <div class="demo-player-wrap">
                            @if($video->tag_label)
                                <span class="demo-tag-overlay">▶ {{ $video->tag_label }}</span>
                            @endif
                            <span class="demo-order-overlay">#{{ $video->sort_order }}</span>
                            <video src="{{ $video->video_url }}" controls playsinline preload="metadata"></video>
                        </div>

                        <div class="demo-card-body">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:6px">
                                @if($video->status)
                                    <span class="status-badge status-success">● Active</span>
                                @else
                                    <span class="status-badge status-warning">Hidden</span>
                                @endif
                                <span style="font-size:11px;color:#94A3B8;font-weight:600">ID #{{ $video->id }}</span>
                            </div>

                            <h3 class="demo-card-title">{{ $video->title }}</h3>
                            @if($video->subtitle)
                                <div class="demo-card-sub">{{ $video->subtitle }}</div>
                            @endif

                            <div class="demo-card-meta">
                                <span>🔗</span>
                                @if($video->product)
                                    <span>{{ \Illuminate\Support\Str::limit($video->product->name, 30) }}</span>
                                @elseif($video->product_id)
                                    <span>Product #{{ $video->product_id }}</span>
                                @else
                                    <span style="color:#64748B">All Products Page</span>
                                @endif
                            </div>

                            <div class="demo-card-actions">
                                <button
                                    type="button"
                                    class="demo-action-btn edit-btn"
                                    onclick="openEditDemoModal(this)"
                                    data-id="{{ $video->id }}"
                                    data-update-url="{{ route('admin.demo-videos.update', $video->id) }}"
                                    data-tag="{{ $video->tag_label }}"
                                    data-title="{{ $video->title }}"
                                    data-subtitle="{{ $video->subtitle }}"
                                    data-product-id="{{ $video->product_id }}"
                                    data-sort-order="{{ $video->sort_order }}"
                                    data-status="{{ $video->status ? '1' : '0' }}"
                                    data-video-path="{{ $video->video_path }}"
                                    data-video-url="{{ $video->video_url }}"
                                >
                                    ✏️ Edit
                                </button>

                                <form action="{{ route('admin.demo-videos.toggle', $video->id) }}" method="POST" style="margin:0">
                                    @csrf
                                    <button type="submit" class="demo-action-btn" style="width:100%">
                                        {{ $video->status ? '🙈 Hide' : '👁️ Show' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.demo-videos.delete', $video->id) }}" method="POST" onsubmit="return confirm('Delete live demo video &quot;{{ addslashes($video->title) }}&quot;? This cannot be undone.')" style="margin:0">
                                    @csrf
                                    <button type="submit" class="demo-action-btn delete-btn" title="Delete Video">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column:1 / -1;text-align:center;padding:48px 20px;color:#64748B;border:1.5px dashed var(--border);border-radius:12px">
                        <div style="font-size:32px;margin-bottom:8px">🎬</div>
                        <strong style="display:block;font-size:14px;color:#0F172A;margin-bottom:4px">No Live Demo Videos Yet</strong>
                        <span style="font-size:12.5px">Upload your first live action demo video using the form on the left.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- EDIT / REPLACE DEMO VIDEO MODAL -->
    <div id="editDemoVideoModal" class="demo-modal-backdrop" onclick="if(event.target === this) closeEditDemoModal()">
        <div class="demo-modal-box">
            <div class="demo-modal-header">
                <div>
                    <h3 style="margin:0;font-size:16px;font-weight:800;color:#0F172A">✏️ Edit / Replace Live Demo Video</h3>
                    <div style="font-size:12px;color:#64748B;margin-top:2px">Update details or upload a brand-new video clip</div>
                </div>
                <button type="button" onclick="closeEditDemoModal()" style="border:none;background:#F1F5F9;color:#334155;width:32px;height:32px;border-radius:50%;font-size:16px;font-weight:800;cursor:pointer">✕</button>
            </div>

            <div class="demo-modal-body">
                <form id="editDemoVideoForm" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div style="margin-bottom:14px;background:#0F172A;border-radius:10px;overflow:hidden;text-align:center">
                        <video id="editModalVideoPreview" controls playsinline style="width:100%;max-height:210px;display:block;object-fit:contain"></video>
                    </div>

                    <div style="margin-bottom:13px">
                        <label class="demo-label">Replace Video File <span style="font-weight:500;color:#64748B">(Leave empty to keep current video)</span></label>
                        <div class="demo-upload-drop" style="padding:10px">
                            <input type="file" name="video_file" id="editVideoFileInput" accept="video/mp4,video/webm,video/quicktime,video/x-m4v,video/ogg" style="width:100%;font-size:12.5px">
                        </div>
                    </div>

                    <div style="margin-bottom:13px">
                        <label class="demo-label">Video Path / Direct URL</label>
                        <input type="text" name="video_url" id="editVideoUrl" class="demo-input" placeholder="videos/touchstains-demo-1.mp4 or https://...">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 100px;gap:10px;margin-bottom:13px">
                        <div>
                            <label class="demo-label">Tag Badge Pill</label>
                            <input type="text" name="tag_label" id="editTagLabel" class="demo-input" maxlength="80">
                        </div>
                        <div>
                            <label class="demo-label">Display Order</label>
                            <input type="number" name="sort_order" id="editSortOrder" min="0" class="demo-input">
                        </div>
                    </div>

                    <div style="margin-bottom:13px">
                        <label class="demo-label">Video Title *</label>
                        <input type="text" name="title" id="editTitle" required class="demo-input" maxlength="200">
                    </div>

                    <div style="margin-bottom:13px">
                        <label class="demo-label">Subtitle / Short Proof Line</label>
                        <input type="text" name="subtitle" id="editSubtitle" class="demo-input" maxlength="220">
                    </div>

                    <div style="margin-bottom:14px">
                        <label class="demo-label">Linked Product ("View Product →" Button)</label>
                        <select name="product_id" id="editProductId" class="demo-input">
                            <option value="">-- All Products (/products) --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">
                                    #{{ $prod->id }} — {{ \Illuminate\Support\Str::limit($prod->name, 46) }} (₹{{ number_format((float)$prod->price, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#0F172A;margin-bottom:18px;cursor:pointer">
                        <input type="checkbox" name="status" id="editStatus" value="1" style="width:16px;height:16px;accent-color:#01684B">
                        <span>Active (Show on Homepage)</span>
                    </label>

                    <div style="display:flex;gap:10px;justify-content:flex-end">
                        <button type="button" onclick="closeEditDemoModal()" class="btn-secondary" style="padding:10px 16px">Cancel</button>
                        <button type="submit" class="btn-theme" style="padding:10px 20px">💾 Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Instant local video preview when selecting a file to upload
    document.getElementById('createVideoFile')?.addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        const preview = document.getElementById('createVideoPreview');
        if (file && preview) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else if (preview) {
            preview.style.display = 'none';
            preview.removeAttribute('src');
        }
    });

    document.getElementById('editVideoFileInput')?.addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        const preview = document.getElementById('editModalVideoPreview');
        if (file && preview) {
            preview.src = URL.createObjectURL(file);
        }
    });

    function openEditDemoModal(btn) {
        const modal = document.getElementById('editDemoVideoModal');
        const form = document.getElementById('editDemoVideoForm');
        form.action = btn.dataset.updateUrl;

        document.getElementById('editTagLabel').value = btn.dataset.tag || '';
        document.getElementById('editTitle').value = btn.dataset.title || '';
        document.getElementById('editSubtitle').value = btn.dataset.subtitle || '';
        document.getElementById('editProductId').value = btn.dataset.productId || '';
        document.getElementById('editSortOrder').value = btn.dataset.sortOrder || '1';
        document.getElementById('editVideoUrl').value = btn.dataset.videoPath || '';
        document.getElementById('editStatus').checked = btn.dataset.status === '1';

        const fileInput = document.getElementById('editVideoFileInput');
        if (fileInput) fileInput.value = '';

        const preview = document.getElementById('editModalVideoPreview');
        if (preview) {
            preview.src = btn.dataset.videoUrl || '';
        }

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeEditDemoModal() {
        const modal = document.getElementById('editDemoVideoModal');
        const preview = document.getElementById('editModalVideoPreview');
        if (preview) {
            preview.pause();
        }
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEditDemoModal();
        }
    });
</script>
@endpush
