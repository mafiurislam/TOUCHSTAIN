<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\CmsPage;
use App\Models\DemoVideo;
use App\Models\Message;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    /**
     * Live Action Demo Videos CRUD Management
     */
    public function demoVideos()
    {
        DemoVideo::ensureTableAndSeed();

        $demoVideos = DemoVideo::with('product')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $products = Product::orderBy('name')->get(['id', 'name', 'price']);
        $nextOrder = ((int) DemoVideo::max('sort_order')) + 1;

        return view('admin.cms.demo-videos', compact('demoVideos', 'products', 'nextOrder'));
    }

    /**
     * Store a new Live Action Demo Video
     */
    public function storeDemoVideo(Request $request)
    {
        DemoVideo::ensureTableAndSeed();

        // Detect when PHP itself rejected an oversized upload before Laravel saw the file
        if (isset($_FILES['video_file']) && !empty($_FILES['video_file']['name'])) {
            $uploadErr = $_FILES['video_file']['error'] ?? UPLOAD_ERR_OK;
            if ($uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE) {
                $maxLimit = ini_get('upload_max_filesize') ?: '2M';
                return back()
                    ->withInput()
                    ->withErrors(['video_file' => "Uploaded video exceeds server upload limit ({$maxLimit}). Please compress the video or paste a direct video URL."]);
            }
        }

        $validated = $request->validate([
            'tag_label'  => 'nullable|string|max:80',
            'title'      => 'required|string|max:200',
            'subtitle'   => 'nullable|string|max:220',
            'product_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v,video/ogg,application/octet-stream|max:51200',
            'video_url'  => 'nullable|string|max:500',
        ]);

        $videoPath = null;

        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
            $videoPath = $this->saveUploadedDemoVideo($request->file('video_file'));
        } elseif (!empty(trim((string) ($validated['video_url'] ?? '')))) {
            $videoPath = trim((string) $validated['video_url']);
        } else {
            return back()
                ->withInput()
                ->withErrors(['video_file' => 'Please upload a video file (MP4/WebM/MOV) or provide a video URL/path.']);
        }

        $video = DemoVideo::create([
            'tag_label'  => trim((string) ($validated['tag_label'] ?? '')) ?: 'Live Demo',
            'title'      => trim($validated['title']),
            'subtitle'   => trim((string) ($validated['subtitle'] ?? '')) ?: null,
            'video_path' => $videoPath,
            'product_id' => !empty($validated['product_id']) ? (int) $validated['product_id'] : null,
            'sort_order' => isset($validated['sort_order']) ? (int) $validated['sort_order'] : (((int) DemoVideo::max('sort_order')) + 1),
            'status'     => $request->has('status') ? (bool) $request->input('status') : true,
        ]);

        try {
            AuditLog::record('CMS', 'Create Demo Video', "Added live demo video '{$video->title}'");
        } catch (\Throwable $e) {
            // Do not block save on audit log issues
        }

        return back()->with('success', "Live demo video '{$video->title}' uploaded and published!");
    }

    /**
     * Update / Edit an existing Live Action Demo Video (metadata and/or replacement video file)
     */
    public function updateDemoVideo(Request $request, $id)
    {
        DemoVideo::ensureTableAndSeed();

        $video = DemoVideo::findOrFail($id);

        if (isset($_FILES['video_file']) && !empty($_FILES['video_file']['name'])) {
            $uploadErr = $_FILES['video_file']['error'] ?? UPLOAD_ERR_OK;
            if ($uploadErr === UPLOAD_ERR_INI_SIZE || $uploadErr === UPLOAD_ERR_FORM_SIZE) {
                $maxLimit = ini_get('upload_max_filesize') ?: '2M';
                return back()
                    ->withInput()
                    ->withErrors(['video_file' => "Replacement video exceeds server upload limit ({$maxLimit}). Please compress the video or paste a direct video URL."]);
            }
        }

        $validated = $request->validate([
            'tag_label'  => 'nullable|string|max:80',
            'title'      => 'required|string|max:200',
            'subtitle'   => 'nullable|string|max:220',
            'product_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v,video/ogg,application/octet-stream|max:51200',
            'video_url'  => 'nullable|string|max:500',
        ]);

        $oldVideoPath = $video->video_path;
        $newVideoPath = $oldVideoPath;

        if ($request->hasFile('video_file') && $request->file('video_file')->isValid()) {
            $newVideoPath = $this->saveUploadedDemoVideo($request->file('video_file'));
        } elseif (!empty(trim((string) ($validated['video_url'] ?? ''))) && trim((string) $validated['video_url']) !== $oldVideoPath) {
            $newVideoPath = trim((string) $validated['video_url']);
        }

        $video->update([
            'tag_label'  => trim((string) ($validated['tag_label'] ?? '')) ?: 'Live Demo',
            'title'      => trim($validated['title']),
            'subtitle'   => trim((string) ($validated['subtitle'] ?? '')) ?: null,
            'video_path' => $newVideoPath,
            'product_id' => !empty($validated['product_id']) ? (int) $validated['product_id'] : null,
            'sort_order' => isset($validated['sort_order']) ? (int) $validated['sort_order'] : $video->sort_order,
            'status'     => $request->has('status') ? (bool) $request->input('status') : false,
        ]);

        if ($newVideoPath !== $oldVideoPath) {
            $this->deleteCustomVideoFileIfUnused($oldVideoPath, $video->id);
        }

        try {
            AuditLog::record('CMS', 'Update Demo Video', "Updated live demo video '{$video->title}'");
        } catch (\Throwable $e) {
            // Do not block update on audit log issues
        }

        return back()->with('success', "Live demo video '{$video->title}' updated successfully!");
    }

    /**
     * Toggle active / hidden visibility for a Live Action Demo Video
     */
    public function toggleDemoVideoStatus($id)
    {
        DemoVideo::ensureTableAndSeed();

        $video = DemoVideo::findOrFail($id);
        $video->status = !$video->status;
        $video->save();

        $stateLabel = $video->status ? 'Active (Visible on Home)' : 'Hidden';
        try {
            AuditLog::record('CMS', 'Toggle Demo Video', "Set live demo video '{$video->title}' to {$stateLabel}");
        } catch (\Throwable $e) {
        }

        return back()->with('success', "Video '{$video->title}' is now {$stateLabel}.");
    }

    /**
     * Delete a Live Action Demo Video
     */
    public function destroyDemoVideo($id)
    {
        DemoVideo::ensureTableAndSeed();

        $video = DemoVideo::findOrFail($id);
        $title = $video->title;
        $oldPath = $video->video_path;
        $videoId = $video->id;

        $video->delete();
        $this->deleteCustomVideoFileIfUnused($oldPath, $videoId);

        try {
            AuditLog::record('CMS', 'Delete Demo Video', "Deleted live demo video '{$title}'");
        } catch (\Throwable $e) {
        }

        return back()->with('success', "Live demo video '{$title}' deleted successfully.");
    }

    /**
     * Save uploaded video into public/videos and mirror to root videos/ for shared hosting compatibility.
     */
    protected function saveUploadedDemoVideo($file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'mp4');
        if (!in_array($ext, ['mp4', 'webm', 'mov', 'm4v', 'ogg'], true)) {
            $ext = 'mp4';
        }

        $fileName = 'demo-' . time() . '-' . Str::lower(Str::random(5)) . '.' . $ext;
        $publicDir = public_path('videos');
        if (!is_dir($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }

        $file->move($publicDir, $fileName);

        // Mirror to root videos/ if different from public/videos/ (for Hostinger root docroot setups)
        $rootDir = base_path('videos');
        if (realpath($rootDir) !== realpath($publicDir)) {
            if (!is_dir($rootDir)) {
                @mkdir($rootDir, 0755, true);
            }
            @copy($publicDir . DIRECTORY_SEPARATOR . $fileName, $rootDir . DIRECTORY_SEPARATOR . $fileName);
        }

        return 'videos/' . $fileName;
    }

    /**
     * Clean up custom uploaded video files (`videos/demo-...`) when no longer referenced.
     * Never deletes bundled `touchstains-demo-*.mp4` assets.
     */
    protected function deleteCustomVideoFileIfUnused(?string $videoPath, ?int $ignoreId = null): void
    {
        if (!$videoPath || str_starts_with($videoPath, 'http://') || str_starts_with($videoPath, 'https://')) {
            return;
        }

        $baseName = basename($videoPath);
        if (!str_starts_with($baseName, 'demo-')) {
            return;
        }

        $stillUsed = DemoVideo::where('video_path', $videoPath)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($stillUsed) {
            return;
        }

        foreach ([public_path('videos/' . $baseName), base_path('videos/' . $baseName)] as $fullPath) {
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    /**
     * Banners & Carousel Sliders (Supports Image AND Video Slides)
     */
    public function banners()
    {
        Banner::ensureMediaColumns();
        $banners = Banner::orderByDesc('id')->get();
        $products = Product::orderBy('name')->get(['id', 'name']);
        return view('admin.cms.banners', compact('banners', 'products'));
    }

    /**
     * Store new banner (Image or Video)
     */
    public function storeBanner(Request $request)
    {
        Banner::ensureMediaColumns();

        foreach (['video', 'image'] as $field) {
            if (isset($_FILES[$field]) && !empty($_FILES[$field]['name'])) {
                $err = $_FILES[$field]['error'] ?? UPLOAD_ERR_OK;
                if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
                    $maxLimit = ini_get('upload_max_filesize') ?: '2M';
                    return back()
                        ->withInput()
                        ->withErrors([$field => "Uploaded file exceeds server upload limit ({$maxLimit}). Please compress the file or paste a direct video URL."]);
                }
            }
        }

        $mediaType = $request->input('media_type', 'image');
        if (!in_array($mediaType, ['image', 'video'], true)) {
            $mediaType = 'image';
        }

        if ($mediaType === 'video') {
            $validated = $request->validate([
                'title'      => 'required|string|max:255',
                'product_id' => 'nullable|integer',
                'video'      => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v,video/ogg,application/octet-stream|max:51200',
                'video_url'  => 'nullable|string|max:500',
            ]);

            $videoName = null;
            if ($request->hasFile('video') && $request->file('video')->isValid()) {
                $videoName = $this->saveUploadedBannerMedia($request->file('video'), 'banner-video');
            } elseif (!empty(trim((string) ($validated['video_url'] ?? '')))) {
                $videoName = trim((string) $validated['video_url']);
            } else {
                return back()
                    ->withInput()
                    ->withErrors(['video' => 'Please upload a video file (MP4/WebM/MOV) or paste a direct video URL.']);
            }

            $banner = Banner::create([
                'title'      => trim($validated['title']),
                'media_type' => 'video',
                'video'      => $videoName,
                'image'      => $videoName,
                'product_id' => !empty($validated['product_id']) ? (int) $validated['product_id'] : null,
                'status'     => 1,
            ]);

            try {
                AuditLog::record('CMS', 'Create Video Banner', "Uploaded video banner '{$banner->title}'");
            } catch (\Throwable $e) {
            }

            return back()->with('success', "Video banner '{$banner->title}' uploaded and published on homepage!");
        }

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'product_id' => 'nullable|integer',
            'image'      => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ]);

        $imageName = $this->saveUploadedBannerMedia($request->file('image'), 'banner');

        $banner = Banner::create([
            'title'      => trim($validated['title']),
            'media_type' => 'image',
            'image'      => $imageName,
            'video'      => null,
            'product_id' => !empty($validated['product_id']) ? (int) $validated['product_id'] : null,
            'status'     => 1,
        ]);

        try {
            AuditLog::record('CMS', 'Create Banner', "Uploaded banner '{$banner->title}'");
        } catch (\Throwable $e) {
        }

        return back()->with('success', "Image banner '{$banner->title}' added successfully!");
    }

    /**
     * Update / Edit existing banner (can switch between Image & Video or replace file)
     */
    public function updateBanner(Request $request, $id)
    {
        Banner::ensureMediaColumns();
        $banner = Banner::findOrFail($id);

        foreach (['video', 'image'] as $field) {
            if (isset($_FILES[$field]) && !empty($_FILES[$field]['name'])) {
                $err = $_FILES[$field]['error'] ?? UPLOAD_ERR_OK;
                if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
                    $maxLimit = ini_get('upload_max_filesize') ?: '2M';
                    return back()
                        ->withInput()
                        ->withErrors([$field => "Uploaded file exceeds server upload limit ({$maxLimit})."]);
                }
            }
        }

        $mediaType = $request->input('media_type', $banner->is_video ? 'video' : 'image');
        if (!in_array($mediaType, ['image', 'video'], true)) {
            $mediaType = 'image';
        }

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'product_id' => 'nullable|integer',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'video'      => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v,video/ogg,application/octet-stream|max:51200',
            'video_url'  => 'nullable|string|max:500',
        ]);

        $oldFile = $banner->is_video ? ($banner->video ?: $banner->image) : $banner->image;
        $updateData = [
            'title'      => trim($validated['title']),
            'media_type' => $mediaType,
            'product_id' => !empty($validated['product_id']) ? (int) $validated['product_id'] : null,
        ];

        $newFile = null;
        if ($mediaType === 'video') {
            if ($request->hasFile('video') && $request->file('video')->isValid()) {
                $newFile = $this->saveUploadedBannerMedia($request->file('video'), 'banner-video');
            } elseif (!empty(trim((string) ($validated['video_url'] ?? '')))) {
                $newFile = trim((string) $validated['video_url']);
            } elseif (!$banner->is_video) {
                return back()->withErrors(['video' => 'Please upload a video file or enter a video URL when switching to Video Banner.']);
            }

            if ($newFile !== null) {
                $updateData['video'] = $newFile;
                $updateData['image'] = $newFile;
            }
        } else {
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $newFile = $this->saveUploadedBannerMedia($request->file('image'), 'banner');
                $updateData['image'] = $newFile;
                $updateData['video'] = null;
            } elseif ($banner->is_video) {
                return back()->withErrors(['image' => 'Please upload an image file when switching from Video to Image Banner.']);
            }
        }

        $banner->update($updateData);

        if ($newFile !== null && $newFile !== $oldFile) {
            $this->deleteCustomBannerMediaIfUnused($oldFile, $banner->id);
        }

        try {
            AuditLog::record('CMS', 'Update Banner', "Updated banner '{$banner->title}' ({$mediaType})");
        } catch (\Throwable $e) {
        }

        return back()->with('success', "Banner '{$banner->title}' updated successfully!");
    }

    /**
     * Delete banner
     */
    public function destroyBanner($id)
    {
        Banner::ensureMediaColumns();
        $banner = Banner::findOrFail($id);
        $title = $banner->title;
        $oldFile = $banner->is_video ? ($banner->video ?: $banner->image) : $banner->image;
        $bannerId = $banner->id;

        $banner->delete();
        $this->deleteCustomBannerMediaIfUnused($oldFile, $bannerId);

        try {
            AuditLog::record('CMS', 'Delete Banner', "Deleted banner '{$title}'");
        } catch (\Throwable $e) {
        }

        return back()->with('success', "Banner deleted successfully.");
    }

    /**
     * Save uploaded banner image or video across public/storage/banner, storage/app/public/banner, and storage/banner.
     */
    protected function saveUploadedBannerMedia($file, string $prefix = 'banner'): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: ($prefix === 'banner-video' ? 'mp4' : 'jpg'));
        $fileName = $prefix . '-' . time() . '-' . Str::lower(Str::random(4)) . '.' . $ext;

        $primaryDir = public_path('storage/banner');
        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0755, true);
        }

        $file->move($primaryDir, $fileName);
        $primaryFull = $primaryDir . DIRECTORY_SEPARATOR . $fileName;

        // Mirror to storage/app/public/banner and root storage/banner for shared hosting compatibility
        foreach ([storage_path('app/public/banner'), base_path('storage/banner')] as $mirrorDir) {
            if (realpath($mirrorDir) !== realpath($primaryDir)) {
                if (!is_dir($mirrorDir)) {
                    @mkdir($mirrorDir, 0755, true);
                }
                @copy($primaryFull, $mirrorDir . DIRECTORY_SEPARATOR . $fileName);
            }
        }

        return $fileName;
    }

    /**
     * Clean up custom uploaded banner media files (`banner-...`) when no longer referenced.
     */
    protected function deleteCustomBannerMediaIfUnused(?string $fileName, ?int $ignoreId = null): void
    {
        if (!$fileName || str_starts_with($fileName, 'http://') || str_starts_with($fileName, 'https://')) {
            return;
        }
        $base = basename($fileName);
        if (!str_starts_with($base, 'banner-')) {
            return;
        }

        $stillUsed = Banner::where(function ($q) use ($fileName, $base) {
                $q->where('image', $fileName)->orWhere('video', $fileName)
                  ->orWhere('image', $base)->orWhere('video', $base);
            })
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($stillUsed) {
            return;
        }

        foreach ([
            public_path('storage/banner/' . $base),
            storage_path('app/public/banner/' . $base),
            base_path('storage/banner/' . $base),
        ] as $fullPath) {
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    /**
     * CMS Pages list
     */
    public function cms()
    {
        $pages = CmsPage::orderBy('title')->get();
        return view('admin.cms.index', compact('pages'));
    }

    /**
     * Update CMS Page content
     */
    public function updateCmsPage(Request $request, $id)
    {
        $page = CmsPage::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
        ]);

        $page->update($validated);
        AuditLog::record('CMS', 'Update CMS Page', "Updated content for page '{$page->title}'");

        return back()->with('success', "Page '{$page->title}' content updated successfully!");
    }

    /**
     * Customer Contact Messages & Chat Enquiries
     */
    public function enquiries()
    {
        $messages = Message::with('user')->orderByDesc('id')->paginate(20);
        return view('admin.cms.enquiries', compact('messages'));
    }
}
