<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\BankTransfer;
use App\Models\Order;
use App\Models\Setting;
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

        $isRazorpayActive = Setting::getValue('payment_razorpay_active', '1') !== '0';
        $isCodActive = Setting::getValue('payment_cod_active', '1') !== '0';
        $isUpiActive = Setting::getValue('payment_upi_active', '1') !== '0';
        $upiId = Setting::getValue('payment_upi_id', '7039214465-3@ybl');
        $upiName = Setting::getValue('payment_upi_name', 'Naushad Ali Meher Ali');
        $upiQrImage = Setting::getValue('payment_upi_qr_image', 'payment/upi_phonepe_qr.jpg');

        return view('admin.payments.index', compact(
            'transfers',
            'bankAccount',
            'stats',
            'isRazorpayActive',
            'isCodActive',
            'isUpiActive',
            'upiId',
            'upiName',
            'upiQrImage'
        ));
    }

    /**
     * Quick Toggle for Payment Methods (Razorpay / Cash on Delivery / UPI)
     */
    public function toggleMethod(Request $request)
    {
        $method = $request->input('method'); // 'razorpay', 'cod', or 'upi'
        $status = $request->input('status'); // '1' or '0'

        if ($method === 'razorpay') {
            Setting::setValue('payment_razorpay_active', $status ? '1' : '0');
            $label = 'Online Payment (Razorpay)';
        } elseif ($method === 'cod') {
            Setting::setValue('payment_cod_active', $status ? '1' : '0');
            $label = 'Cash on Delivery (COD)';
        } elseif ($method === 'upi') {
            Setting::setValue('payment_upi_active', $status ? '1' : '0');
            $label = 'Direct UPI Payment (PhonePe / GPay / QR)';
        } else {
            return back()->with('error', 'Invalid payment method specified.');
        }

        $stateText = $status ? 'ENABLED (ON)' : 'DISABLED (OFF)';
        AuditLog::record('Payments', 'Toggle Payment Method', "{$label} set to {$stateText}");

        return back()->with('success', "✓ {$label} is now {$stateText} on checkout!");
    }

    /**
     * Update UPI Settings & Upload New QR Image (Admin Dashboard Control)
     */
    public function updateUpiSettings(Request $request)
    {
        $request->validate([
            'upi_id' => 'required|string|max:100',
            'upi_name' => 'required|string|max:150',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $upiId = trim((string)$request->input('upi_id'));
        $upiName = trim((string)$request->input('upi_name'));

        Setting::setValue('payment_upi_id', $upiId);
        Setting::setValue('payment_upi_name', $upiName);

        if ($request->hasFile('qr_image')) {
            $file = $request->file('qr_image');
            $destDir = public_path('storage/payment');
            if (!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $filename = 'upi_qr_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destDir, $filename);
            Setting::setValue('payment_upi_qr_image', 'payment/' . $filename);
        }

        AuditLog::record('Payments', 'Update UPI Settings', "Updated UPI ID ({$upiId}) and Merchant QR Code image");

        return back()->with('success', '✓ UPI Payment details and QR Code updated successfully!');
    }

    /**
     * Delete / Reset Custom QR to default PhonePe QR
     */
    public function deleteUpiQr(Request $request)
    {
        Setting::setValue('payment_upi_qr_image', 'payment/upi_phonepe_qr.jpg');
        AuditLog::record('Payments', 'Reset UPI QR', "Reset custom QR code back to default PhonePe QR");

        return back()->with('success', '✓ Custom QR Code removed. Reset back to official default PhonePe QR!');
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
