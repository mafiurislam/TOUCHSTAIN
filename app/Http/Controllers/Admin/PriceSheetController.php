<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class PriceSheetController extends Controller
{
    /**
     * Display the Professional Bulk Price Sheet
     */
    public function index(Request $request)
    {
        $query = Product::with('category')->orderBy('name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        $products = $query->paginate(25)->withQueryString();
        $categories = Category::where('parent_id', 0)->get();

        return view('admin.price-sheet.index', compact('products', 'categories'));
    }

    /**
     * AJAX Quick Save Single Product Prices
     */
    public function quickUpdate(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'mrp' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'bulk_min_qty' => 'nullable|integer|min:1',
            'bulk_price' => 'nullable|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
        ]);

        $price = (float)$validated['price'];
        $originalPrice = isset($validated['mrp']) && $validated['mrp'] !== '' && $validated['mrp'] !== null ? (float)$validated['mrp'] : null;
        $product->update([
            'mrp' => $originalPrice,
            'price' => $price,
            'discount' => 0,
            'discount_type' => 'amount',
            'bulk_min_qty' => !empty($validated['bulk_min_qty']) ? (int)$validated['bulk_min_qty'] : null,
            'bulk_price' => !empty($validated['bulk_price']) ? (float)$validated['bulk_price'] : null,
            'special_price' => !empty($validated['special_price']) ? (float)$validated['special_price'] : null,
        ]);

        AuditLog::record('Pricing', 'Quick Price Update', "Updated prices for [#{$product->id}] {$product->name}: Original=₹{$originalPrice}, Selling=₹{$product->price}");

        return response()->json([
            'success' => true,
            'message' => "Prices for '{$product->name}' updated successfully!",
            'product' => $product,
        ]);
    }

    /**
     * Bulk Save All Table Rows
     */
    public function bulkSave(Request $request)
    {
        $rows = $request->input('prices', []);
        $updatedCount = 0;

        foreach ($rows as $id => $data) {
            $p = Product::find($id);
            if ($p) {
                $newPrice = isset($data['price']) && is_numeric($data['price']) ? (float)$data['price'] : $p->price;
                $newMrp = array_key_exists('mrp', $data)
                    ? (($data['mrp'] !== '' && $data['mrp'] !== null && is_numeric($data['mrp'])) ? (float)$data['mrp'] : null)
                    : $p->mrp;
                $p->update([
                    'mrp' => $newMrp,
                    'price' => $newPrice,
                    'discount' => 0,
                    'discount_type' => 'amount',
                    'bulk_min_qty' => !empty($data['bulk_min_qty']) ? (int)$data['bulk_min_qty'] : $p->bulk_min_qty,
                    'bulk_price' => !empty($data['bulk_price']) ? (float)$data['bulk_price'] : $p->bulk_price,
                    'special_price' => !empty($data['special_price']) ? (float)$data['special_price'] : $p->special_price,
                ]);
                $updatedCount++;
            }
        }

        AuditLog::record('Pricing', 'Bulk Price Save', "Bulk updated prices for {$updatedCount} products");

        return back()->with('success', "Price sheet updated successfully! {$updatedCount} products were modified.");
    }

    /**
     * Export Price Sheet to CSV
     */
    public function exportCsv()
    {
        $products = Product::orderBy('id')->get();
        $filename = "price_sheet_" . date('Y_m_d_His') . ".csv";

        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['Product ID', 'SKU', 'Product Name', 'Product Price', 'Bulk Min Qty', 'Bulk Price', 'Special Offer Price']);

        foreach ($products as $p) {
            fputcsv($handle, [
                $p->id,
                $p->sku,
                $p->name,
                $p->price,
                $p->bulk_min_qty,
                $p->bulk_price,
                $p->special_price,
            ]);
        }

        fseek($handle, 0);
        return response()->streamDownload(function () use ($handle) {
            fpassthru($handle);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Import Price Sheet from CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // Skip header

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (!empty($row[0])) {
                $productId = (int)$row[0];
                $p = Product::find($productId);
                if ($p) {
                    $newPrice = isset($row[3]) && is_numeric($row[3]) ? (float)$row[3] : $p->price;
                    $p->update([
                        'mrp' => $newPrice,
                        'price' => $newPrice,
                        'discount' => 0,
                        'discount_type' => 'amount',
                        'bulk_min_qty' => isset($row[4]) && is_numeric($row[4]) ? (int)$row[4] : $p->bulk_min_qty,
                        'bulk_price' => isset($row[5]) && is_numeric($row[5]) ? (float)$row[5] : $p->bulk_price,
                        'special_price' => isset($row[6]) && is_numeric($row[6]) ? (float)$row[6] : $p->special_price,
                    ]);
                    $count++;
                }
            }
        }
        fclose($handle);

        AuditLog::record('Pricing', 'Price Sheet CSV Import', "Imported CSV price sheet and updated {$count} products");

        return back()->with('success', "Price sheet CSV imported successfully! {$count} product prices updated.");
    }
}
