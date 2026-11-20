<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\InventoryLog;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Inventory Stock Overview & Alerts
     */
    public function index(Request $request)
    {
        $query = Product::with('category')->orderBy('total_stock');

        if ($request->filled('filter')) {
            $f = $request->input('filter');
            if ($f === 'low') {
                $query->where('total_stock', '>', 0)->where('total_stock', '<=', 10);
            } elseif ($f === 'out') {
                $query->where('total_stock', '<=', 0);
            } elseif ($f === 'in') {
                $query->where('total_stock', '>', 10);
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%");
            });
        }

        $products = $query->paginate(20)->withQueryString();

        $stats = [
            'total_items' => Product::sum('total_stock'),
            'low_stock' => Product::where('total_stock', '>', 0)->where('total_stock', '<=', 10)->count(),
            'out_of_stock' => Product::where('total_stock', '<=', 0)->count(),
            'total_products' => Product::count(),
        ];

        $recentLogs = InventoryLog::with('product')->orderByDesc('id')->take(15)->get();

        return view('admin.inventory.index', compact('products', 'stats', 'recentLogs'));
    }

    /**
     * Adjust Product Stock (Stock In / Stock Out)
     */
    public function adjustStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:stock_in,stock_out,manual_adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $qty = (int)$validated['quantity'];
        $oldStock = (int)$product->total_stock;
        $newStock = $oldStock;

        if ($validated['type'] === 'stock_in') {
            $newStock = $oldStock + $qty;
        } elseif ($validated['type'] === 'stock_out') {
            if ($oldStock < $qty && !$request->boolean('allow_negative')) {
                return back()->with('error', "Cannot deduct {$qty} items. Only {$oldStock} items in available stock.");
            }
            $newStock = max(0, $oldStock - $qty);
        } else {
            $newStock = $qty; // Set exact
        }

        $product->update(['total_stock' => $newStock]);

        InventoryLog::create([
            'product_id' => $product->id,
            'type' => $validated['type'],
            'quantity' => $qty,
            'current_stock' => $newStock,
            'notes' => $validated['notes'] ?: "Stock adjusted via Inventory Manager",
            'user_id' => auth()->id(),
        ]);

        AuditLog::record('Inventory', 'Stock Adjustment', "Adjusted stock for [#{$product->id}] {$product->name}: Old={$oldStock}, New={$newStock}");

        return back()->with('success', "Stock for '{$product->name}' successfully updated to {$newStock} units.");
    }
}
