<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Categories Management
     */
    public function index()
    {
        Category::ensureColumns();

        $orderCol = Schema::hasColumn('categories', 'priority') ? 'priority' : (Schema::hasColumn('categories', 'position') ? 'position' : 'id');

        $categories = Category::where('parent_id', 0)
            ->with(['children' => function ($q) use ($orderCol) {
                $q->orderBy($orderCol);
            }])
            ->orderBy($orderCol)
            ->get();

        $allParents = Category::where('parent_id', 0)->orderBy('name')->get();

        return view('admin.categories.index', compact('categories', 'allParents'));
    }

    /**
     * Store new category or subcategory
     */
    public function store(Request $request)
    {
        Category::ensureColumns();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|integer',
            'priority' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
        ], [
            'name.required' => 'Category name is required.',
        ]);

        $imageName = 'default-cat.png';
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = 'cat-' . time() . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('storage/category');
            if (!file_exists($dest)) {
                @mkdir($dest, 0755, true);
            }
            $file->move($dest, $imageName);
        }

        $priorityVal = (int)($validated['priority'] ?? 0);
        $slugVal = Str::slug($validated['name']) ?: ('cat-' . time());

        $catData = [
            'name' => trim($validated['name']),
            'slug' => $slugVal,
            'parent_id' => (int)$validated['parent_id'],
            'image' => $imageName,
            'status' => 1,
        ];

        if (Schema::hasColumn('categories', 'priority')) {
            $catData['priority'] = $priorityVal;
        }
        if (Schema::hasColumn('categories', 'position')) {
            $catData['position'] = $priorityVal;
        }

        $category = Category::create($catData);

        try {
            AuditLog::record('Categories', 'Create Category', "Created category [#{$category->id}] {$category->name}");
        } catch (\Throwable $e) {
            // Ignore audit log error
        }

        return back()->with('success', "Category '{$category->name}' created successfully!");
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        Category::ensureColumns();
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|integer',
            'priority' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
        ]);

        $priorityVal = (int)($validated['priority'] ?? $category->priority ?? $category->position ?? 0);
        $slugVal = Str::slug($validated['name']) ?: ('cat-' . time());

        $updateData = [
            'name' => trim($validated['name']),
            'slug' => $slugVal,
            'parent_id' => (int)$validated['parent_id'],
        ];

        if (Schema::hasColumn('categories', 'priority')) {
            $updateData['priority'] = $priorityVal;
        }
        if (Schema::hasColumn('categories', 'position')) {
            $updateData['position'] = $priorityVal;
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = 'cat-' . time() . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('storage/category');
            if (!file_exists($dest)) {
                @mkdir($dest, 0755, true);
            }
            $file->move($dest, $imageName);
            $updateData['image'] = $imageName;
        }

        $category->update($updateData);

        try {
            AuditLog::record('Categories', 'Update Category', "Updated category [#{$id}] {$category->name}");
        } catch (\Throwable $e) {
            // Ignore audit log error
        }

        return back()->with('success', "Category '{$category->name}' updated successfully!");
    }

    /**
     * Delete Category
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $name = $category->name;
        // Delete child categories as well
        Category::where('parent_id', $id)->delete();
        $category->delete();

        try {
            AuditLog::record('Categories', 'Delete Category', "Deleted category [#{$id}] {$name}");
        } catch (\Throwable $e) {
            // Ignore audit log error
        }

        return back()->with('success', "Category '{$name}' deleted successfully.");
    }

    /**
     * Brands Management View
     */
    public function brands()
    {
        $brands = Brand::withCount('products')->orderBy('name')->get();
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Store Brand
     */
    public function storeBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
        ]);

        $img = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $img = 'brand-' . time() . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('storage/brand');
            if (!file_exists($dest)) {
                @mkdir($dest, 0755, true);
            }
            $file->move($dest, $img);
        }

        $brand = Brand::create([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'image' => $img,
            'status' => true,
        ]);

        try {
            AuditLog::record('Brands', 'Create Brand', "Created brand [#{$brand->id}] {$brand->name}");
        } catch (\Throwable $e) {
            // Ignore audit log error
        }

        return back()->with('success', "Brand '{$brand->name}' created successfully!");
    }

    /**
     * Delete Brand
     */
    public function destroyBrand($id)
    {
        $brand = Brand::findOrFail($id);
        $name = $brand->name;
        $brand->delete();

        try {
            AuditLog::record('Brands', 'Delete Brand', "Deleted brand [#{$id}] {$name}");
        } catch (\Throwable $e) {
            // Ignore audit log error
        }

        return back()->with('success', "Brand '{$name}' deleted successfully.");
    }
}
