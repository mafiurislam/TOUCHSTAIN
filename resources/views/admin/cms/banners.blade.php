@extends('admin.layouts.master')

@section('title', 'Banners & Sliders Management')

@push('styles')
<style>
    .banner-mgmt-grid {
        display: grid;
        grid-template-columns: 1fr 1.8fr;
        gap: 24px;
        align-items: start;
    }
    .banner-type-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 16px;
    }
    .banner-type-tab {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 12px;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        background: #F8FAFC;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.16s ease;
    }
    .banner-type-tab:hover {
        border-color: #94A3B8;
        background: #F1F5F9;
    }
    .banner-type-tab.active {
        background: #EFF6FF;
        border-color: #2563EB;
        color: #1D4ED8;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.14);
    }
    .banner-type-tab.active-video {
        background: #FEF2F2;
        border-color: #DC2626;
        color: #B91C1C;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.14);
    }
    .banner-input {
        width: 100%;
        padding: 9px 12px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-size: 13px;
        background: #fff;
        color: #0F172A;
        box-sizing: border-box;
    }
    .banner-file-box {
        border: 1.5px dashed #CBD5E1;
        border-radius: 10px;
        padding: 10px;
        background: #F8FAFC;
    }
    .banner-media-wrap {
        position: relative;
        width: 100%;
        height: 135px;
        background: #0F172A;
        overflow: hidden;
    }
    .banner-media-wrap img,
    .banner-media-wrap video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .banner-kind-pill {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 10.5px;
        font-weight: 800;
        color: #fff;
        z-index: 2;
        pointer-events: none;
        letter-spacing: 0.3px;
    }
    .banner-kind-pill.is-img { background: rgba(37, 99, 235, 0.9); }
    .banner-kind-pill.is-vid { background: rgba(220, 38, 38, 0.92); }
    .banner-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 18px;
        backdrop-filter: blur(2px);
    }
    .banner-modal-backdrop.open { display: flex; }
    .banner-modal-card {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 520px;
        max-height: 92vh;
        overflow-y: auto;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.25);
    }
    @media (max-width: 980px) {
        .banner-mgmt-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

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

    <div class="banner-mgmt-grid">

        <!-- Left: Upload Banner Form (Supports BOTH Image & Video!) -->
        <div class="admin-card">
            <h2 class="admin-card-title" style="margin-bottom:4px">🖼️ Add Homepage Banner / Slide</h2>
            <p style="font-size:12px;color:var(--text-muted);margin:0 0 16px">Choose Image or Video slide to display on the homepage hero carousel.</p>

            @php
                $oldType = old('media_type', 'image');
            @endphp

            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" id="createBannerForm">
                @csrf
                <input type="hidden" name="media_type" id="bannerMediaTypeInput" value="{{ $oldType }}">

                <!-- 2-Option Media Switcher -->
                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Slide Media Type *</label>
                    <div class="banner-type-tabs">
                        <button type="button" id="tabBtnImage" class="banner-type-tab {{ $oldType === 'image' ? 'active' : '' }}" onclick="setCreateBannerType('image')">
                            <span>🖼️</span>
                            <span>Upload Image</span>
                        </button>
                        <button type="button" id="tabBtnVideo" class="banner-type-tab {{ $oldType === 'video' ? 'active-video' : '' }}" onclick="setCreateBannerType('video')">
                            <span>🎬</span>
                            <span>Upload Video</span>
                        </button>
                    </div>
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Banner Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Mega Fabric Stain Remover Slide" class="banner-input">
                </div>

                <!-- Option 1: Image Upload -->
                <div id="createImageGroup" style="margin-bottom:16px;{{ $oldType === 'video' ? 'display:none;' : '' }}">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Banner Image (Recommended 1200x450) *</label>
                    <div class="banner-file-box">
                        <input type="file" name="image" id="createBannerImageInput" accept="image/*" {{ $oldType === 'image' ? 'required' : '' }} style="width:100%;font-size:12.5px">
                        <img id="createBannerImagePreview" alt="Preview" style="display:none;width:100%;max-height:150px;object-fit:cover;border-radius:8px;margin-top:10px">
                    </div>
                </div>

                <!-- Option 2: Video Upload -->
                <div id="createVideoGroup" style="margin-bottom:16px;{{ $oldType === 'video' ? '' : 'display:none;' }}">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Banner Video (MP4 / WebM / MOV, 16:9 Recommended) *</label>
                    <div class="banner-file-box">
                        <input type="file" name="video" id="createBannerVideoInput" accept="video/mp4,video/webm,video/quicktime,video/x-m4v,video/ogg" style="width:100%;font-size:12.5px">
                        <div style="font-size:11px;color:#64748B;margin-top:6px">
                            Server max upload: <strong>{{ ini_get('upload_max_filesize') ?: '2M' }}</strong> • Auto-plays muted in hero slider with controls
                        </div>
                        <video id="createBannerVideoPreview" controls muted playsinline style="display:none;width:100%;max-height:160px;border-radius:8px;margin-top:10px;background:#0F172A"></video>
                    </div>

                    <div style="margin-top:10px">
                        <label style="font-size:12px;font-weight:700;display:block;margin-bottom:5px;color:#475569">OR Direct Video URL / Path <span style="font-weight:500;color:#64748B">(optional)</span></label>
                        <input type="text" name="video_url" value="{{ old('video_url') }}" placeholder="e.g. https://...mp4 or videos/touchstains-demo-1.mp4" class="banner-input">
                    </div>
                </div>

                <!-- Optional Linked Product -->
                @if(isset($products) && $products->count() > 0)
                <div style="margin-bottom:18px">
                    <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:6px">Link to Product <span style="font-weight:500;color:#64748B">(optional)</span></label>
                    <select name="product_id" class="banner-input">
                        <option value="">-- Default Shop Page (/shop) --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                #{{ $prod->id }} — {{ \Illuminate\Support\Str::limit($prod->name, 42) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <button type="submit" id="bannerSubmitBtn" class="btn-theme" style="width:100%;justify-content:center">
                    {{ $oldType === 'video' ? '🎬 Upload Video Banner' : '📤 Upload Image Banner' }}
                </button>
            </form>
        </div>

        <!-- Right: Current Banners List (Images & Videos) -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">🖼️ Active Homepage Slides ({{ count($banners) }})</h2>
                    <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Interactive sliding carousel displayed on customer storefront (supports Image &amp; Video slides)</p>
                </div>
                <a href="{{ url('/') }}" target="_blank" class="btn-secondary" style="text-decoration:none;font-size:12px;padding:6px 12px">👁️ View Home →</a>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
                @forelse($banners as $ban)
                    <div style="background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;box-shadow:var(--shadow-sm);display:flex;flex-direction:column">
                        <div class="banner-media-wrap">
                            @if($ban->is_video)
                                <span class="banner-kind-pill is-vid">🎬 VIDEO</span>
                                <video src="{{ $ban->media_url }}" controls muted playsinline preload="metadata"></video>
                            @else
                                <span class="banner-kind-pill is-img">🖼️ IMAGE</span>
                                <img src="{{ $ban->media_url }}" alt="{{ $ban->title }}" onerror="this.src='{{ asset('assets/image/placeholder.png') }}'">
                            @endif
                        </div>

                        <div style="padding:12px;display:flex;flex-direction:column;flex:1">
                            <strong style="font-size:13px;display:block;margin-bottom:10px;line-height:1.35">{{ $ban->title }}</strong>
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:6px;margin-top:auto">
                                <span class="status-badge status-success">Active</span>
                                <div style="display:flex;align-items:center;gap:6px">
                                    <button
                                        type="button"
                                        class="btn-secondary"
                                        style="padding:3px 9px;font-size:11px;color:#1D4ED8"
                                        onclick="openEditBannerModal(this)"
                                        data-update-url="{{ route('admin.banners.update', $ban->id) }}"
                                        data-title="{{ $ban->title }}"
                                        data-type="{{ $ban->is_video ? 'video' : 'image' }}"
                                        data-product-id="{{ $ban->product_id }}"
                                        data-media-url="{{ $ban->media_url }}"
                                        data-video-raw="{{ $ban->is_video ? ($ban->video ?: $ban->image) : '' }}"
                                    >
                                        ✏️ Edit
                                    </button>
                                    <form action="{{ route('admin.banners.delete', $ban->id) }}" method="POST" onsubmit="return confirm('Delete this banner slide?')" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="padding:3px 8px;font-size:11px;color:#DC2626">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:36px;color:#94A3B8;grid-column:1 / -1">No custom banners uploaded.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- EDIT BANNER MODAL (Switch between Image / Video or Replace Media) -->
    <div id="editBannerModal" class="banner-modal-backdrop" onclick="if(event.target === this) closeEditBannerModal()">
        <div class="banner-modal-card">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 18px;border-bottom:1px solid var(--border)">
                <h3 style="margin:0;font-size:15.5px;font-weight:800;color:#0F172A">✏️ Edit Homepage Banner / Slide</h3>
                <button type="button" onclick="closeEditBannerModal()" style="border:none;background:#F1F5F9;color:#334155;width:30px;height:30px;border-radius:50%;font-weight:800;cursor:pointer">✕</button>
            </div>

            <div style="padding:18px">
                <form id="editBannerForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="media_type" id="editBannerMediaType" value="image">

                    <div class="banner-type-tabs" style="margin-bottom:14px">
                        <button type="button" id="editTabImage" class="banner-type-tab" onclick="setEditBannerType('image')">🖼️ Image Banner</button>
                        <button type="button" id="editTabVideo" class="banner-type-tab" onclick="setEditBannerType('video')">🎬 Video Banner</button>
                    </div>

                    <div style="margin-bottom:14px;border-radius:10px;overflow:hidden;background:#0F172A">
                        <img id="editPreviewImg" alt="Current Banner" style="display:none;width:100%;max-height:170px;object-fit:cover">
                        <video id="editPreviewVid" controls muted playsinline style="display:none;width:100%;max-height:170px;object-fit:contain"></video>
                    </div>

                    <div style="margin-bottom:13px">
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:5px">Banner Title *</label>
                        <input type="text" name="title" id="editBannerTitle" required class="banner-input">
                    </div>

                    <div id="editImageGroup" style="margin-bottom:14px">
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:5px">Replace Banner Image <span style="font-weight:500;color:#64748B">(optional if keeping current image)</span></label>
                        <div class="banner-file-box">
                            <input type="file" name="image" id="editBannerImageInput" accept="image/*" style="width:100%;font-size:12.5px">
                        </div>
                    </div>

                    <div id="editVideoGroup" style="margin-bottom:14px;display:none">
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:5px">Upload / Replace Banner Video <span style="font-weight:500;color:#64748B">(MP4 / WebM / MOV)</span></label>
                        <div class="banner-file-box">
                            <input type="file" name="video" id="editBannerVideoInput" accept="video/mp4,video/webm,video/quicktime,video/x-m4v,video/ogg" style="width:100%;font-size:12.5px">
                        </div>
                        <div style="margin-top:8px">
                            <label style="font-size:12px;font-weight:700;display:block;margin-bottom:4px;color:#475569">OR Direct Video URL / Path</label>
                            <input type="text" name="video_url" id="editBannerVideoUrl" class="banner-input" placeholder="https://...mp4 or videos/...">
                        </div>
                    </div>

                    @if(isset($products) && $products->count() > 0)
                    <div style="margin-bottom:16px">
                        <label style="font-size:12.5px;font-weight:700;display:block;margin-bottom:5px">Link to Product</label>
                        <select name="product_id" id="editBannerProductId" class="banner-input">
                            <option value="">-- Default Shop Page (/shop) --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">#{{ $prod->id }} — {{ \Illuminate\Support\Str::limit($prod->name, 42) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div style="display:flex;justify-content:flex-end;gap:10px">
                        <button type="button" onclick="closeEditBannerModal()" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-theme">💾 Save Banner Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function setCreateBannerType(type) {
        const isVideo = type === 'video';
        document.getElementById('bannerMediaTypeInput').value = type;

        const tabImg = document.getElementById('tabBtnImage');
        const tabVid = document.getElementById('tabBtnVideo');
        tabImg.className = 'banner-type-tab' + (!isVideo ? ' active' : '');
        tabVid.className = 'banner-type-tab' + (isVideo ? ' active-video' : '');

        document.getElementById('createImageGroup').style.display = isVideo ? 'none' : 'block';
        document.getElementById('createVideoGroup').style.display = isVideo ? 'block' : 'none';

        const imgInput = document.getElementById('createBannerImageInput');
        if (imgInput) imgInput.required = !isVideo;

        const btn = document.getElementById('bannerSubmitBtn');
        if (btn) btn.textContent = isVideo ? '🎬 Upload Video Banner' : '📤 Upload Image Banner';
    }

    document.getElementById('createBannerImageInput')?.addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        const preview = document.getElementById('createBannerImagePreview');
        if (file && preview) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else if (preview) {
            preview.style.display = 'none';
        }
    });

    document.getElementById('createBannerVideoInput')?.addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        const preview = document.getElementById('createBannerVideoPreview');
        if (file && preview) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else if (preview) {
            preview.style.display = 'none';
        }
    });

    function setEditBannerType(type) {
        const isVideo = type === 'video';
        document.getElementById('editBannerMediaType').value = type;
        document.getElementById('editTabImage').className = 'banner-type-tab' + (!isVideo ? ' active' : '');
        document.getElementById('editTabVideo').className = 'banner-type-tab' + (isVideo ? ' active-video' : '');
        document.getElementById('editImageGroup').style.display = isVideo ? 'none' : 'block';
        document.getElementById('editVideoGroup').style.display = isVideo ? 'block' : 'none';
    }

    function openEditBannerModal(btn) {
        const modal = document.getElementById('editBannerModal');
        const form = document.getElementById('editBannerForm');
        form.action = btn.dataset.updateUrl;

        document.getElementById('editBannerTitle').value = btn.dataset.title || '';
        const prodSel = document.getElementById('editBannerProductId');
        if (prodSel) prodSel.value = btn.dataset.productId || '';

        const type = btn.dataset.type === 'video' ? 'video' : 'image';
        setEditBannerType(type);

        const vidUrlInput = document.getElementById('editBannerVideoUrl');
        if (vidUrlInput) vidUrlInput.value = btn.dataset.videoRaw || '';

        const imgFile = document.getElementById('editBannerImageInput');
        if (imgFile) imgFile.value = '';
        const vidFile = document.getElementById('editBannerVideoInput');
        if (vidFile) vidFile.value = '';

        const prevImg = document.getElementById('editPreviewImg');
        const prevVid = document.getElementById('editPreviewVid');
        if (type === 'video') {
            prevImg.style.display = 'none';
            prevVid.src = btn.dataset.mediaUrl || '';
            prevVid.style.display = 'block';
        } else {
            prevVid.pause();
            prevVid.style.display = 'none';
            prevImg.src = btn.dataset.mediaUrl || '';
            prevImg.style.display = 'block';
        }

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeEditBannerModal() {
        const modal = document.getElementById('editBannerModal');
        const prevVid = document.getElementById('editPreviewVid');
        if (prevVid) prevVid.pause();
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.getElementById('editBannerImageInput')?.addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        if (file) {
            document.getElementById('editPreviewVid').style.display = 'none';
            const img = document.getElementById('editPreviewImg');
            img.src = URL.createObjectURL(file);
            img.style.display = 'block';
        }
    });

    document.getElementById('editBannerVideoInput')?.addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        if (file) {
            document.getElementById('editPreviewImg').style.display = 'none';
            const vid = document.getElementById('editPreviewVid');
            vid.src = URL.createObjectURL(file);
            vid.style.display = 'block';
        }
    });
</script>
@endpush
