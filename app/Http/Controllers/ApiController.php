<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\BankAccount;
use App\Models\BankTransfer;
use App\Models\BankTransferLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function config()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        foreach ($settings as $k => $v) {
            $decoded = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $settings[$k] = $decoded;
            }
        }
        $settings['branches'] = Branch::where('status', 1)->get();
        return response()->json($settings);
    }

    public function categories()
    {
        $categories = Category::where('parent_id', 0)
            ->where('status', 1)
            ->with(['children' => function ($q) {
                $q->where('status', 1)->orderBy('priority');
            }])
            ->orderBy('priority')
            ->get()
            ->map(function ($cat) {
                $arr = $cat->toArray();
                $arr['childes'] = $arr['children'] ?? [];
                unset($arr['children']);
                return $arr;
            });

        return response()->json($categories);
    }

    public function childCategories($id)
    {
        return response()->json(
            Category::where('parent_id', $id)->where('status', 1)->orderBy('priority')->get()
        );
    }

    public function categoryProducts($id)
    {
        $products = Product::where('status', 1)->get()->filter(function ($p) use ($id) {
            $cIds = is_array($p->category_ids) ? $p->category_ids : [];
            foreach ($cIds as $c) {
                if ((string)($c['id'] ?? '') === (string)$id) {
                    return true;
                }
            }
            return false;
        })->values();

        return response()->json($products);
    }

    public function banners()
    {
        return response()->json(Banner::where('status', 1)->get());
    }

    public function allProducts(Request $request)
    {
        $limit = (int)$request->query('limit', 240);
        $offset = (int)$request->query('offset', 1);
        $products = Product::where('status', 1)->get();
        return response()->json([
            'total_size' => $products->count(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $products,
        ]);
    }

    public function dailyNeeds(Request $request)
    {
        $products = Product::where('status', 1)->where('daily_needs', 1)->get();
        return response()->json([
            'total_size' => $products->count(),
            'limit' => 20,
            'offset' => 1,
            'products' => $products,
        ]);
    }

    public function featuredProducts(Request $request)
    {
        $products = Product::where('status', 1)->where('is_featured', 1)->get();
        return response()->json([
            'total_size' => $products->count(),
            'limit' => 20,
            'offset' => 1,
            'products' => $products,
        ]);
    }

    public function mostReviewed(Request $request)
    {
        $products = Product::where('status', 1)->orderByDesc('popularity_count')->take(24)->get();
        return response()->json([
            'total_size' => $products->count(),
            'limit' => 24,
            'offset' => 1,
            'products' => $products,
        ]);
    }

    public function productDetails($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    public function coupons()
    {
        return response()->json(Coupon::where('status', 1)->get());
    }

    public function orders()
    {
        return response()->json(Order::with(['items', 'bankTransfer'])->orderByDesc('id')->get());
    }

    public function bankAccount()
    {
        $account = BankAccount::getActiveAccount();
        return response()->json([
            'status' => true,
            'bank_account' => $account,
        ]);
    }

    public function submitBankTransfer(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|string',
            'utr_number' => 'required|string|min:6|max:64',
            'payment_screenshot' => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $screenshotPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $file = $request->file('payment_screenshot');
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension();
                $filename = 'receipt_order_' . $order->id . '_' . time() . '_' . Str::random(10) . '.' . $ext;
                $file->storeAs('payment_receipts', $filename, 'public');
                $destDir = public_path('storage/payment_receipts');
                if (!file_exists($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy($file->getRealPath(), $destDir . DIRECTORY_SEPARATOR . $filename);
                $screenshotPath = 'payment_receipts/' . $filename;
            }
        }

        $bankTransfer = null;
        DB::transaction(function () use ($order, $validated, $screenshotPath, &$bankTransfer) {
            $bankTransfer = BankTransfer::firstOrNew(['order_id' => $order->id]);
            $prevStatus = $bankTransfer->status ?? 'awaiting_payment';

            $bankTransfer->user_id = $order->user_id;
            $bankTransfer->amount = (float)$validated['amount'];
            $bankTransfer->transaction_date = $validated['transaction_date'];
            $bankTransfer->utr_number = trim($validated['utr_number']);
            if ($screenshotPath) {
                $bankTransfer->screenshot_path = $screenshotPath;
            }
            $bankTransfer->status = 'verification_pending';
            $bankTransfer->rejection_reason = null;
            $bankTransfer->save();

            $order->update([
                'payment_status' => 'verification_pending',
            ]);

            BankTransferLog::create([
                'bank_transfer_id' => $bankTransfer->id,
                'order_id' => $order->id,
                'action' => 'submitted',
                'previous_status' => $prevStatus,
                'new_status' => 'verification_pending',
                'performed_by' => $order->user_id,
                'actor_type' => 'customer',
                'notes' => 'API Payment submission with UTR: ' . $validated['utr_number'],
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Payment details submitted successfully! Your payment is under verification.',
            'order_id' => $order->id,
            'payment_status' => 'verification_pending',
            'bank_transfer' => $bankTransfer,
        ]);
    }
}

