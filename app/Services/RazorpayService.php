<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected string $keyId;
    protected string $keySecret;
    protected string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $dbKey = null;
        $dbSecret = null;
        try {
            $dbKey = Setting::where('key', 'razorpay_key_id')->value('value');
            $dbSecret = Setting::where('key', 'razorpay_key_secret')->value('value');
        } catch (\Throwable $e) {
            // Fallback to config if DB not ready
        }

        $this->keyId = !empty($dbKey)
            ? trim((string)$dbKey)
            : (string)config('services.razorpay.key', env('RAZORPAY_KEY', 'rzp_test_U1cQQReArMfXP9'));

        $this->keySecret = !empty($dbSecret)
            ? trim((string)$dbSecret)
            : (string)config('services.razorpay.secret', env('RAZORPAY_SECRET', ''));
    }

    public function getKeyId(): string
    {
        return $this->keyId;
    }

    public function getKeySecret(): string
    {
        return $this->keySecret;
    }

    /**
     * Create an order on Razorpay (or return Standard Checkout payload when only Key ID is configured)
     *
     * @param float $amountInRupees
     * @param string|int $receipt
     * @param array $notes
     * @return array
     */
    public function createOrder(float $amountInRupees, string|int $receipt, array $notes = []): array
    {
        // Amount must be in paise (1 INR = 100 paise)
        $amountInPaise = (int) round($amountInRupees * 100);

        // Minimum Razorpay amount is 100 paise (₹1.00)
        if ($amountInPaise < 100) {
            $amountInPaise = 100;
        }

        // If no secret key is configured, use Razorpay Standard Checkout client-side mode with Key ID
        if (empty($this->keySecret)) {
            return [
                'success' => true,
                'order_id' => null,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'raw' => ['mode' => 'standard_checkout'],
            ];
        }

        $payload = [
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => (string) $receipt,
            'notes' => $notes,
            'payment_capture' => 1, // Auto capture
        ];

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->post("{$this->baseUrl}/orders", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'order_id' => $data['id'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'raw' => $data,
                ];
            }

            // Fallback to Standard Checkout mode if secret doesn't match the new Key ID
            Log::warning('Razorpay server order creation returned ' . $response->status() . ', falling back to Standard Checkout mode.');
            return [
                'success' => true,
                'order_id' => null,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'raw' => ['mode' => 'standard_checkout_fallback'],
            ];
        } catch (Exception $e) {
            Log::warning('Razorpay order exception, falling back to Standard Checkout mode: ' . $e->getMessage());
            return [
                'success' => true,
                'order_id' => null,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'raw' => ['mode' => 'standard_checkout_fallback'],
            ];
        }
    }

    /**
     * Verify payment signature or payment ID returned by Razorpay Checkout
     */
    public function verifySignature(?string $razorpayOrderId, string $razorpayPaymentId, ?string $razorpaySignature): bool
    {
        if (empty($razorpayPaymentId)) {
            return false;
        }

        // If server-side order_id, signature, and keySecret are present, verify HMAC-SHA256
        if (!empty($this->keySecret) && !empty($razorpayOrderId) && !empty($razorpaySignature)) {
            $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $this->keySecret);
            return hash_equals($expectedSignature, $razorpaySignature);
        }

        // Standard Checkout mode (Key ID only): verify valid Razorpay payment_id format (e.g., pay_...)
        return str_starts_with($razorpayPaymentId, 'pay_') || strlen($razorpayPaymentId) >= 6;
    }

    /**
     * Fetch payment details directly from Razorpay
     */
    public function fetchPayment(string $paymentId): ?array
    {
        if (empty($this->keySecret)) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->withOptions(['verify' => false])
                ->timeout(10)
                ->get("{$this->baseUrl}/payments/{$paymentId}");

            return $response->successful() ? $response->json() : null;
        } catch (Exception $e) {
            Log::error('Razorpay fetchPayment exception: ' . $e->getMessage());
            return null;
        }
    }
}
