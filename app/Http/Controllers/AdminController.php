<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Message;
use App\Models\BankAccount;
use App\Models\BankTransfer;
use App\Models\BankTransferLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_categories' => Category::where('parent_id', 0)->count(),
            'total_subcategories' => Category::where('parent_id', '>', 0)->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
            'delivered_orders' => Order::where('order_status', 'delivered')->count(),
            'total_revenue' => Order::where('order_status', '!=', 'canceled')->sum('order_amount'),
            'total_customers' => User::where('role', 'customer')->count(),
        ];

        $recentOrders = Order::with(['user', 'items', 'shipment'])->orderByDesc('id')->take(25)->get();
        $products = Product::orderByDesc('id')->take(40)->get();
        $categories = Category::where('parent_id', 0)->with('children')->orderBy('priority')->get();
        $coupons = Coupon::orderByDesc('id')->get();
        $messages = Message::with('user')->orderByDesc('id')->take(20)->get();
        $settings = Setting::pluck('value', 'key')->toArray();
        $deliverySettings = \App\Models\DeliverySetting::getOrCreate();
        $stats['total_shipments'] = \App\Models\Shipment::count();

        // Bank Transfer Management & Settings
        $bankTransfers = BankTransfer::with(['order.items', 'order.user', 'user', 'approver', 'logs'])
            ->orderByDesc('id')
            ->get();
        $bankAccount = BankAccount::getActiveAccount();

        // Extra bank transfer statistics
        $bankStats = [
            'total' => $bankTransfers->count(),
            'pending' => $bankTransfers->where('status', 'verification_pending')->count(),
            'awaiting' => $bankTransfers->where('status', 'awaiting_payment')->count(),
            'confirmed' => $bankTransfers->where('status', 'payment_confirmed')->count(),
            'rejected' => $bankTransfers->where('status', 'payment_rejected')->count(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'products',
            'categories',
            'coupons',
            'messages',
            'settings',
            'bankTransfers',
            'bankAccount',
            'bankStats',
            'deliverySettings'
        ));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $newStatus = $request->input('order_status', $order->order_status);
        $order->update([
            'order_status' => $newStatus,
            'payment_status' => $request->input('payment_status', $order->payment_status),
        ]);

        $msg = "Order #{$id} updated to " . str_replace('_', ' ', $order->order_status) . "!";

        // Automated Shiprocket Shipment creation on confirmation if configured
        if ($newStatus === 'confirmed') {
            try {
                $deliverySettings = \App\Models\DeliverySetting::getOrCreate();
                if ($deliverySettings->auto_shipment_on_confirm) {
                    $deliveryManager = app(\App\Services\Delivery\DeliveryManager::class);
                    $shipRes = $deliveryManager->processFullShipment($order);
                    if (!empty($shipRes['success']) && !empty($shipRes['shipment'])) {
                        $courier = $shipRes['shipment']->courier_name ?? 'Courier';
                        $awb = $shipRes['shipment']->awb_number ?? 'Assigned';
                        $msg .= " 🚚 Auto-booked with {$courier} (AWB: {$awb})";
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Shiprocket auto shipment creation error: " . $e->getMessage());
            }
        }

        return back()->with('success', $msg);
    }

    public function storeProduct(Request $request)
    {
        $catId = (int)$request->input('category_id', 20);
        $subId = (int)$request->input('sub_category_id', 25);

        Product::create([
            'name' => $request->input('name', 'New Grocery Item'),
            'description' => $request->input('description', 'Fresh quality product from Getgozo Jadupur.'),
            'image' => [$request->input('image', '2026-08-06-6a740cf633900.png')],
            'price' => (float)$request->input('price', 100),
            'mrp' => (float)$request->input('price', 100),
            'discount' => 0,
            'discount_type' => 'amount',
            'tax' => 0,
            'tax_type' => 'percent',
            'unit' => $request->input('unit', 'kg'),
            'capacity' => (float)$request->input('capacity', 1),
            'total_stock' => (int)$request->input('total_stock', 50),
            'daily_needs' => $request->boolean('daily_needs', true) ? 1 : 0,
            'is_featured' => $request->boolean('is_featured', false) ? 1 : 0,
            'status' => 1,
            'maximum_order_quantity' => 10,
            'category_ids' => [
                ['id' => (string)$catId, 'position' => 1],
                ['id' => (string)$subId, 'position' => 2],
            ],
        ]);

        return back()->with('success', 'New product added to SQLite database!');
    }

    public function deleteProduct($id)
    {
        Product::where('id', $id)->delete();
        return back()->with('success', "Product #{$id} deleted.");
    }

    public function updateSettings(Request $request)
    {
        $fields = [
            'ecommerce_name',
            'ecommerce_phone',
            'ecommerce_email',
            'ecommerce_address',
            'minimum_order_value',
            'free_delivery_over_amount',
            'delivery_charge',
            'footer_text',
        ];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                Setting::setValue($field, $request->input($field));
            }
        }
        return back()->with('success', 'Store settings updated in SQLite database!');
    }

    /**
     * Approve customer bank transfer payment.
     */
    public function approveBankTransfer(Request $request, $id)
    {
        $transfer = BankTransfer::with('order')->findOrFail($id);
        $order = $transfer->order;
        $adminUser = Auth::user() ?: User::where('role', 'admin')->first() ?: User::first();
        $adminId = $adminUser ? $adminUser->id : 1;

        DB::transaction(function () use ($transfer, $order, $adminId, $request) {
            $prevStatus = $transfer->status;
            $note = $request->input('admin_note', $transfer->admin_notes);

            $transfer->update([
                'status' => 'payment_confirmed',
                'approved_by' => $adminId,
                'approved_at' => now(),
                'admin_notes' => $note,
            ]);

            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                    'order_status' => $order->order_status === 'pending' ? 'confirmed' : $order->order_status,
                ]);
            }

            BankTransferLog::create([
                'bank_transfer_id' => $transfer->id,
                'order_id' => $order ? $order->id : 0,
                'action' => 'approved',
                'previous_status' => $prevStatus,
                'new_status' => 'payment_confirmed',
                'performed_by' => $adminId,
                'actor_type' => 'admin',
                'notes' => 'Admin verified and approved payment. Order #' . ($order ? $order->id : $transfer->order_id) . ' marked as Paid.' . ($note ? ' Note: ' . $note : ''),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => "Payment for Order #{$transfer->order_id} approved and confirmed successfully!",
                'transfer' => $transfer->fresh(),
            ]);
        }

        return back()->with('success', "Payment for Order #{$transfer->order_id} has been APPROVED! Order status updated to Paid.");
    }

    /**
     * Reject customer bank transfer payment with reason.
     */
    public function rejectBankTransfer(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:3|max:500',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $transfer = BankTransfer::with('order')->findOrFail($id);
        $order = $transfer->order;
        $adminUser = Auth::user() ?: User::where('role', 'admin')->first() ?: User::first();
        $adminId = $adminUser ? $adminUser->id : 1;

        DB::transaction(function () use ($transfer, $order, $adminId, $validated) {
            $prevStatus = $transfer->status;
            $reason = trim($validated['rejection_reason']);
            $note = $validated['admin_note'] ?? $transfer->admin_notes;

            $transfer->update([
                'status' => 'payment_rejected',
                'rejection_reason' => $reason,
                'rejected_at' => now(),
                'admin_notes' => $note,
            ]);

            if ($order) {
                $order->update([
                    'payment_status' => 'payment_rejected',
                ]);
            }

            BankTransferLog::create([
                'bank_transfer_id' => $transfer->id,
                'order_id' => $order ? $order->id : 0,
                'action' => 'rejected',
                'previous_status' => $prevStatus,
                'new_status' => 'payment_rejected',
                'performed_by' => $adminId,
                'actor_type' => 'admin',
                'notes' => 'Admin rejected payment. Reason: ' . $reason,
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => "Payment for Order #{$transfer->order_id} marked as rejected.",
                'transfer' => $transfer->fresh(),
            ]);
        }

        return back()->with('success', "Payment for Order #{$transfer->order_id} has been REJECTED. Customer has been alerted to resubmit.");
    }

    /**
     * Update internal admin note for a bank transfer.
     */
    public function noteBankTransfer(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $transfer = BankTransfer::findOrFail($id);
        $transfer->update([
            'admin_notes' => $validated['admin_notes'],
        ]);

        $adminUser = Auth::user() ?: User::where('role', 'admin')->first() ?: User::first();
        $adminId = $adminUser ? $adminUser->id : 1;

        BankTransferLog::create([
            'bank_transfer_id' => $transfer->id,
            'order_id' => $transfer->order_id,
            'action' => 'note_added',
            'previous_status' => $transfer->status,
            'new_status' => $transfer->status,
            'performed_by' => $adminId,
            'actor_type' => 'admin',
            'notes' => 'Admin note updated: ' . $validated['admin_notes'],
        ]);

        return back()->with('success', "Note saved for Bank Transfer #{$id}.");
    }

    /**
     * Update Merchant Bank Account Settings (AU Small Finance Bank).
     */
    public function updateBankAccount(Request $request)
    {
        $validated = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:64',
            'ifsc_code' => 'required|string|max:32',
            'account_type' => 'required|string|max:64',
            'branch_name' => 'required|string|max:255',
            'branch_address' => 'required|string',
            'instructions' => 'nullable|string',
        ]);

        $account = BankAccount::first();
        if (!$account) {
            $account = new BankAccount();
        }

        $account->account_holder_name = trim($validated['account_holder_name']);
        $account->bank_name = trim($validated['bank_name']);
        $account->account_number = trim($validated['account_number']);
        $account->ifsc_code = strtoupper(trim($validated['ifsc_code']));
        $account->account_type = trim($validated['account_type']);
        $account->branch_name = trim($validated['branch_name']);
        $account->branch_address = trim($validated['branch_address']);
        $account->is_active = $request->has('is_active') ? (bool)$request->input('is_active') : true;
        if (!empty($validated['instructions'])) {
            $account->instructions = trim($validated['instructions']);
        }
        $account->save();

        return back()->with('success', 'Merchant AU Small Finance Bank account settings updated successfully!');
    }
}

