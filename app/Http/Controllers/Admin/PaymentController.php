<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\BankTransfer;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Payments & AU Bank Transfer Reconcile
     */
    public function index(Request $request)
    {
        $bankAccount = BankAccount::getActiveAccount();
        $query = BankTransfer::with(['order.items', 'order.user', 'user', 'approver', 'logs'])->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $transfers = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => BankTransfer::count(),
            'pending' => BankTransfer::where('status', 'verification_pending')->count(),
            'confirmed' => BankTransfer::where('status', 'payment_confirmed')->count(),
            'rejected' => BankTransfer::where('status', 'payment_rejected')->count(),
        ];

        return view('admin.payments.index', compact('transfers', 'bankAccount', 'stats'));
    }

    /**
     * Approve Bank Transfer
     */
    public function approve($id)
    {
        $transfer = BankTransfer::with('order')->findOrFail($id);
        $transfer->update([
            'status' => 'payment_confirmed',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($transfer->order) {
            $transfer->order->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
            ]);

            try {
                $settings = \App\Models\DeliverySetting::getOrCreate();
                if ($settings->is_active && $settings->auto_shipment_on_confirm) {
                    $deliveryManager = app(\App\Services\Delivery\DeliveryManager::class);
                    $deliveryManager->processFullShipment($transfer->order);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Shiprocket Auto-Dispatch notice: ' . $e->getMessage());
            }
        }

        AuditLog::record('Payments', 'Approve Payment', "Approved bank transfer [#{$id}] for Order #{$transfer->order_id}");

        return back()->with('success', "Payment for Order #{$transfer->order_id} approved and marked as Paid!");
    }

    /**
     * Reject Bank Transfer
     */
    public function reject(Request $request, $id)
    {
        $transfer = BankTransfer::findOrFail($id);
        $reason = $request->input('reason', 'Payment receipt verification failed');

        $transfer->update([
            'status' => 'payment_rejected',
            'rejection_reason' => $reason,
        ]);

        AuditLog::record('Payments', 'Reject Payment', "Rejected bank transfer [#{$id}]: {$reason}");

        return back()->with('error', "Bank transfer [#{$id}] was rejected.");
    }
}
