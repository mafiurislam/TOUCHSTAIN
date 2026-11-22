<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products with filters
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subCategory'])->orderByDesc('id');

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('stock_status')) {
            $st = $request->input('stock_status');
            if ($st === 'low') {
                $query->where('total_stock', '>', 0)->where('total_stock', '<=', 10);
            } elseif ($st === 'out') {
                $query->where('total_stock', '<=', 0);
            } elseif ($st === 'in') {
                $query->where('total_stock', '>', 10);
            }
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::where('parent_id', 0)->with('children')->get();
        $brands = Brand::where('status', true)->get();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Show form to create new product
     */
    public function create()
    {
        $categories = Category::where('parent_id', 0)->with('children')->get();
        $brands = Brand::where('status', true)->get();

        return view('admin.products.create', compact('categories', 'brands'));
    }

    /**
     * Store new product in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'sub_category_id' => 'nullable|integer',
            'brand_id' => 'nullable|integer',
            'capacity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'mrp' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'total_stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:products,sku',
            'barcode' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,webm,ogg,mov,m4v,avi,mkv|max:102400',
            'video_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagesList = [];
        if ($request->hasFile('images')) {
            $imagesList = array_values(array_filter($imagesList, fn($img) => $img && $img !== 'place_holder.png'));
            foreach ($request->file('images') as $file) {
                $filename = date('Y-m-d') . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $this->saveUploadedProductImage($file, $filename);
                $imagesList[] = $filename;
            }
        }
        if (empty($imagesList)) {
            $imagesList = ['place_holder.png'];
        }

        $videoFilename = null;
        if ($request->hasFile('video')) {
            $vfile = $request->file('video');
            $videoFilename = date('Y-m-d') . '-video-' . uniqid() . '.' . $vfile->getClientOriginalExtension();
            $this->saveUploadedProductVideo($vfile, $videoFilename);
        }

        $videoThumbFilename = null;
        if ($request->hasFile('video_thumbnail')) {
            $vtfile = $request->file('video_thumbnail');
            $videoThumbFilename = date('Y-m-d') . '-vthumb-' . uniqid() . '.' . $vtfile->getClientOriginalExtension();
            $this->saveUploadedProductImage($vtfile, $videoThumbFilename);
        }

        $price = (float)$validated['price'];
        $originalPrice = isset($validated['mrp']) && $validated['mrp'] !== '' && $validated['mrp'] !== null ? (float)$validated['mrp'] : null;
        $sku = !empty($validated['sku']) ? $validated['sku'] : 'TS-' . strtoupper(Str::random(6));
        $catIds = [['id' => (string)$validated['category_id'], 'position' => 1]];
        if (!empty($validated['sub_category_id'])) {
            $catIds[] = ['id' => (string)$validated['sub_category_id'], 'position' => 2];
        }

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'category_ids' => $catIds,
            'brand_id' => $validated['brand_id'] ?? null,
            'price' => $price,
            'mrp' => $originalPrice,
            'discount' => 0,
            'discount_type' => 'amount',
            'total_stock' => (int)$validated['total_stock'],
            'capacity' => (float)$validated['capacity'],
            'unit' => trim($validated['unit']),
            'description' => $validated['description'] ?? '',
            'sku' => $sku,
            'barcode' => $validated['barcode'] ?? '890' . rand(100000000, 999999999),
            'image' => $imagesList,
            'video' => $videoFilename,
            'video_thumbnail' => $videoThumbFilename,
            'is_featured' => $request->boolean('is_featured'),
            'is_best_seller' => $request->boolean('is_best_seller'),
            'is_new_arrival' => $request->boolean('is_new_arrival'),
            'status' => $request->boolean('status', true),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'bulk_min_qty' => (int)$request->input('bulk_min_qty', 5),
            'bulk_price' => $request->filled('bulk_price') ? (float)$request->input('bulk_price') : null,
        ]);

        // Record Initial Inventory Log
        InventoryLog::create([
            'product_id' => $product->id,
            'type' => 'stock_in',
            'quantity' => (int)$validated['total_stock'],
            'current_stock' => (int)$validated['total_stock'],
            'notes' => 'Initial stock on product creation',
            'user_id' => auth()->id(),
        ]);

        AuditLog::record('Products', 'Create Product', "Created product [#{$product->id}] {$product->name}");

        return redirect()->route('admin.products')->with('success', "Product '{$product->name}' successfully added!");
    }

    /**
     * Show form to edit product
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('parent_id', 0)->with('children')->get();
        $brands = Brand::where('status', true)->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update product in database
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'sub_category_id' => 'nullable|integer',
            'brand_id' => 'nullable|integer',
            'capacity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'mrp' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'total_stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,webm,ogg,mov,m4v,avi,mkv|max:102400',
            'video_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagesList = is_array($product->image) ? $product->image : [];

        // If keep_images array is provided, retain only the images that were not removed
        if ($request->has('keep_images')) {
            $keep = (array) $request->input('keep_images', []);
            $imagesList = array_values(array_filter($imagesList, fn($img) => in_array($img, $keep, true)));
        }

        if ($request->hasFile('images')) {
            $imagesList = array_values(array_filter($imagesList, fn($img) => $img && $img !== 'place_holder.png'));
            foreach ($request->file('images') as $file) {
                $filename = date('Y-m-d') . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $this->saveUploadedProductImage($file, $filename);
                $imagesList[] = $filename;
            }
        }

        // Handle video removal & upload
        $videoFilename = $product->video;
        $videoThumbFilename = $product->video_thumbnail;

        if ($request->boolean('remove_video')) {
            if ($product->video) {
                $this->deleteProductVideoFile($product->video);
            }
            $videoFilename = null;
            if ($product->video_thumbnail) {
                $this->deleteProductImageFileIfUnused($product->video_thumbnail, $product->id);
            }
            $videoThumbFilename = null;
        }

        if ($request->hasFile('video')) {
            if ($product->video) {
                $this->deleteProductVideoFile($product->video);
            }
            $vfile = $request->file('video');
            $videoFilename = date('Y-m-d') . '-video-' . uniqid() . '.' . $vfile->getClientOriginalExtension();
            $this->saveUploadedProductVideo($vfile, $videoFilename);
        }

        if ($request->hasFile('video_thumbnail')) {
            if ($product->video_thumbnail) {
                $this->deleteProductImageFileIfUnused($product->video_thumbnail, $product->id);
            }
            $vtfile = $request->file('video_thumbnail');
            $videoThumbFilename = date('Y-m-d') . '-vthumb-' . uniqid() . '.' . $vtfile->getClientOriginalExtension();
            $this->saveUploadedProductImage($vtfile, $videoThumbFilename);
        }

        $oldStock = (int)$product->total_stock;
        $newStock = (int)$validated['total_stock'];
        $price = (float)$validated['price'];
        $originalPrice = isset($validated['mrp']) && $validated['mrp'] !== '' && $validated['mrp'] !== null ? (float)$validated['mrp'] : null;
        $catIds = [['id' => (string)$validated['category_id'], 'position' => 1]];
        if (!empty($validated['sub_category_id'])) {
            $catIds[] = ['id' => (string)$validated['sub_category_id'], 'position' => 2];
        }

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'category_ids' => $catIds,
            'brand_id' => $validated['brand_id'] ?? null,
            'price' => $price,
            'mrp' => $originalPrice,
            'discount' => 0,
            'discount_type' => 'amount',
            'total_stock' => $newStock,
            'capacity' => (float)$validated['capacity'],
            'unit' => trim($validated['unit']),
            'description' => $validated['description'] ?? '',
            'sku' => $validated['sku'] ?? $product->sku,
            'barcode' => $validated['barcode'] ?? $product->barcode,
            'image' => $imagesList,
            'video' => $videoFilename,
            'video_thumbnail' => $videoThumbFilename,
            'is_featured' => $request->boolean('is_featured'),
            'is_best_seller' => $request->boolean('is_best_seller'),
            'is_new_arrival' => $request->boolean('is_new_arrival'),
            'status' => $request->boolean('status', true),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'bulk_min_qty' => (int)$request->input('bulk_min_qty', 5),
            'bulk_price' => $request->filled('bulk_price') ? (float)$request->input('bulk_price') : null,
        ]);

        // If stock changed manually, log it
        if ($oldStock !== $newStock) {
            $diff = $newStock - $oldStock;
            InventoryLog::create([
                'product_id' => $product->id,
                'type' => $diff > 0 ? 'stock_in' : 'stock_out',
                'quantity' => abs($diff),
                'current_stock' => $newStock,
                'notes' => "Manual stock adjustment in Edit Product ({$diff})",
                'user_id' => auth()->id(),
            ]);
        }

        AuditLog::record('Products', 'Update Product', "Updated product [#{$product->id}] {$product->name}");

        return redirect()->route('admin.products')->with('success', "Product '{$product->name}' updated successfully!");
    }

    /**
     * Delete a single image from a product's gallery
     */
    public function deleteImage(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $imageName = (string) $request->input('image_name');

        if (empty($imageName)) {
            return response()->json(['success' => false, 'message' => 'Image name is required.'], 422);
        }

        $current = is_array($product->image) ? $product->image : [];
        $newGallery = [];
        $found = false;

        foreach ($current as $img) {
            if ($img === $imageName && !$found) {
                $found = true;
                $this->deleteProductImageFileIfUnused($img, $product->id);
                continue;
            }
            $newGallery[] = $img;
        }

        $product->update([
            'image' => array_values($newGallery),
        ]);

        try {
            AuditLog::record('Products', 'Delete Product Image', "Removed image '{$imageName}' from product [#{$product->id}] {$product->name}");
        } catch (\Throwable $e) {
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Image removed successfully from product gallery.',
                'remaining_count' => count($newGallery),
                'remaining_images' => $newGallery,
            ]);
        }

        return back()->with('success', "Image removed from product gallery.");
    }

    /**
     * Delete the product video and thumbnail
     */
    public function deleteVideo(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->video) {
            $this->deleteProductVideoFile($product->video);
        }
        if ($product->video_thumbnail) {
            $this->deleteProductImageFileIfUnused($product->video_thumbnail, $product->id);
        }

        $product->update([
            'video' => null,
            'video_thumbnail' => null,
        ]);

        try {
            AuditLog::record('Products', 'Delete Product Video', "Removed video from product [#{$product->id}] {$product->name}");
        } catch (\Throwable $e) {
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product video removed successfully.',
            ]);
        }

        return back()->with('success', "Product video removed successfully.");
    }

    /**
     * Save uploaded product image to public/storage/product and mirror to storage/app/public/product and storage/product.
     */
    protected function saveUploadedProductImage($file, string $filename): void
    {
        $primaryDir = public_path('storage/product');
        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0755, true);
        }
        $file->move($primaryDir, $filename);
        $primaryFull = $primaryDir . DIRECTORY_SEPARATOR . $filename;

        foreach ([storage_path('app/public/product'), base_path('storage/product')] as $mirrorDir) {
            if (realpath($mirrorDir) !== realpath($primaryDir)) {
                if (!is_dir($mirrorDir)) {
                    @mkdir($mirrorDir, 0755, true);
                }
                @copy($primaryFull, $mirrorDir . DIRECTORY_SEPARATOR . $filename);
            }
        }
    }

    /**
     * Delete physical image file if not used by any other product and not a seed asset.
     */
    protected function deleteProductImageFileIfUnused(?string $filename, ?int $ignoreProductId = null): void
    {
        if (!$filename || $filename === 'place_holder.png' || str_starts_with($filename, 'http')) {
            return;
        }
        // Never delete default demo/seed photos
        if (str_starts_with($filename, 'touchstains-')) {
            return;
        }

        $stillUsed = Product::where('id', '!=', $ignoreProductId ?? 0)
            ->where('image', 'like', "%{$filename}%")
            ->exists();

        if ($stillUsed) {
            return;
        }

        foreach ([
            public_path('storage/product/' . $filename),
            storage_path('app/public/product/' . $filename),
            base_path('storage/product/' . $filename),
        ] as $fullPath) {
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    /**
     * Save uploaded product video to public/storage/product/videos and mirror.
     */
    protected function saveUploadedProductVideo($file, string $filename): void
    {
        $primaryDir = public_path('storage/product/videos');
        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0755, true);
        }
        $file->move($primaryDir, $filename);
        $primaryFull = $primaryDir . DIRECTORY_SEPARATOR . $filename;

        foreach ([storage_path('app/public/product/videos'), base_path('storage/product/videos')] as $mirrorDir) {
            if (realpath($mirrorDir) !== realpath($primaryDir)) {
                if (!is_dir($mirrorDir)) {
                    @mkdir($mirrorDir, 0755, true);
                }
                @copy($primaryFull, $mirrorDir . DIRECTORY_SEPARATOR . $filename);
            }
        }
    }

    /**
     * Delete physical video file.
     */
    protected function deleteProductVideoFile(?string $filename): void
    {
        if (!$filename || str_starts_with($filename, 'http')) {
            return;
        }
        foreach ([
            public_path('storage/product/videos/' . $filename),
            storage_path('app/public/product/videos/' . $filename),
            base_path('storage/product/videos/' . $filename),
        ] as $fullPath) {
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    /**
     * Delete product safely
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $name = $product->name;
        $product->delete();

        AuditLog::record('Products', 'Delete Product', "Deleted product [#{$id}] {$name}");

        return back()->with('success', "Product '{$name}' was deleted successfully.");
    }

    /**
     * Export products to CSV
     */
    public function exportCsv()
    {
        $products = Product::with('category')->get();
        $filename = "products_export_" . date('Y_m_d_His') . ".csv";

        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Name', 'SKU', 'Barcode', 'Category', 'Price', 'Stock', 'Unit', 'Status']);

        foreach ($products as $p) {
            fputcsv($handle, [
                $p->id,
                $p->name,
                $p->sku,
                $p->barcode,
                $p->category->name ?? 'N/A',
                $p->price,
                $p->total_stock,
                $p->unit,
                $p->status ? 'Active' : 'Inactive',
            ]);
        }

        fseek($handle, 0);
        return response()->streamDownload(function () use ($handle) {
            fpassthru($handle);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
