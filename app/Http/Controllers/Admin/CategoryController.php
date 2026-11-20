<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Categories Management
     */
    public function index()
    {
        $categories = Category::where('parent_id', 0)->with('children')->orderBy('priority')->get();
        $allParents = Category::where('parent_id', 0)->get();

        return view('admin.categories.index', compact('categories', 'allParents'));
    }

    /**
     * Store new category or subcategory
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|integer',
            'priority' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $imageName = 'default-cat.png';
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = 'cat-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/category'), $imageName);
        }

        $category = Category::create([
            'name' => $validated['name'],
            'parent_id' => (int)$validated['parent_id'],
            'priority' => (int)($validated['priority'] ?? 0),
            'image' => $imageName,
            'status' => 1,
        ]);

        AuditLog::record('Categories', 'Create Category', "Created category [#{$category->id}] {$category->name}");

        return back()->with('success', "Category '{$category->name}' created successfully!");
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|integer',
            'priority' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'parent_id' => (int)$validated['parent_id'],
            'priority' => (int)($validated['priority'] ?? $category->priority),
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = 'cat-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/category'), $imageName);
            $updateData['image'] = $imageName;
        }

        $category->update($updateData);
        AuditLog::record('Categories', 'Update Category', "Updated category [#{$id}] {$category->name}");

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

        AuditLog::record('Categories', 'Delete Category', "Deleted category [#{$id}] {$name}");

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $img = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $img = 'brand-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/brand'), $img);
        }

        $brand = Brand::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'image' => $img,
            'status' => true,
        ]);

        AuditLog::record('Brands', 'Create Brand', "Created brand [#{$brand->id}] {$brand->name}");

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

        AuditLog::record('Brands', 'Delete Brand', "Deleted brand [#{$id}] {$name}");

        return back()->with('success', "Brand '{$name}' deleted successfully.");
    }
}
