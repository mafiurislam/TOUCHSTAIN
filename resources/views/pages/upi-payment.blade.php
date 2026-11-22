@extends('layouts.app')

@section('content')
<main class="gf-container container" style="max-width:760px;width:100%;margin:0 auto;padding:24px 16px 64px;box-sizing:border-box">
    <!-- Breadcrumb -->
    <div class="breadcrumb" style="font-size:12.5px;color:#64748B;margin-bottom:16px">
        <a href="{{ url('/') }}" style="color:#01684B;text-decoration:none">Home</a> &gt; 
        <a href="{{ url('/checkout') }}" style="color:#01684B;text-decoration:none">Checkout</a> &gt; 
        <span style="color:#1E293B;font-weight:700">UPI Payment</span>
    </div>

    <!-- Payment Container Card -->
    <div style="background:#fff;border:1.5px solid #E2E8F0;border-radius:18px;box-shadow:0 8px 30px rgba(0,0,0,0.06);overflow:hidden">
        
        <!-- Header Banner -->
        <div style="background:linear-gradient(135deg, #5B21B6 0%, #7C3AED 100%);color:#fff;padding:24px 22px;text-align:center">
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.18);padding:4px 14px;border-radius:99px;font-size:12px;font-weight:700;margin-bottom:10px">
                <span>📱 Official Direct UPI Gateway</span>
            </div>
            <h1 style="margin:0 0 6px;font-size:22px;font-weight:800;letter-spacing:-0.3px">Complete Your UPI Payment</h1>
            <p style="margin:0;font-size:13.5px;opacity:0.92">Scan QR Code or tap to pay instantly with PhonePe, Google Pay, or Paytm</p>
            
            <div style="margin-top:16px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);border-radius:12px;padding:12px 18px;display:inline-flex;align-items:center;gap:18px">
                <div style="text-align:left">
                    <span style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;opacity:0.8;display:block">Order Number</span>
                    <strong style="font-size:15px;letter-spacing:0.5px">#{{ $order->id }}</strong>
                </div>
                <div style="width:1px;height:28px;background:rgba(255,255,255,0.3)"></div>
                <div style="text-align:left">
                    <span style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;opacity:0.8;display:block">Payable Amount</span>
                    <strong style="font-size:20px;font-weight:900">₹{{ number_format($order->order_amount, 2) }}</strong>
                </div>
            </div>
        </div>

        <div style="padding:26px 22px">

            <!-- Mobile Instant Pay CTA (Visible on mobile/tablets) -->
            <div style="margin-bottom:24px;text-align:center">
                <a href="{{ $upiIntentUrl }}" class="btn-theme" style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:15px 20px;font-size:16px;font-weight:800;background:#5B21B6;border:none;border-radius:12px;color:#fff;text-decoration:none;box-shadow:0 6px 20px rgba(91,33,182,0.35);transition:all 0.2s">
                    <span style="font-size:20px">⚡</span>
                    <span>Pay ₹{{ number_format($order->order_amount, 2) }} via Any UPI App</span>
                </a>
                <span style="display:block;font-size:11.5px;color:#64748B;margin-top:6px">Tap above to open PhonePe, Google Pay, Paytm, or BHIM directly on your device</span>
            </div>

            <!-- QR Code Section -->
            <div style="background:#FAF5FF;border:1.5px solid #E9D5FF;border-radius:16px;padding:22px;text-align:center;margin-bottom:24px">
                <div style="display:inline-block;background:#fff;padding:12px;border-radius:14px;border:1.5px solid #DDD6FE;box-shadow:0 4px 16px rgba(124,58,237,0.08);margin-bottom:12px">
                    <img src="{{ asset('storage/' . ($upiQrImage ?? 'payment/upi_phonepe_qr.jpg')) }}?v={{ time() }}" alt="PhonePe QR Code" style="width:230px;max-width:100%;height:auto;aspect-ratio:1/1;object-fit:contain;display:block;margin:0 auto;border-radius:8px">
                    <div style="margin-top:8px;font-size:11px;font-weight:800;color:#6D28D9;letter-spacing:0.5px">
                        SCAN WITH ANY UPI APP
                    </div>
                </div>

                <div style="max-width:380px;margin:0 auto">
                    <h3 style="margin:0 0 4px;font-size:16px;font-weight:800;color:#1E1B4B">{{ $upiName }}</h3>
                    <div style="display:inline-flex;align-items:center;gap:8px;background:#fff;border:1.5px solid #C4B5FD;padding:6px 14px;border-radius:8px;margin-top:6px">
                        <span style="font-size:12px;color:#6B7280;font-weight:600">UPI ID:</span>
                        <code style="font-size:13.5px;font-weight:800;color:#5B21B6;letter-spacing:0.3px" id="lbl-upi-id">{{ $upiId }}</code>
                        <button type="button" onclick="copyUpiId()" style="background:#EDE9FE;border:none;border-radius:6px;padding:3px 8px;font-size:11px;font-weight:700;color:#5B21B6;cursor:pointer;margin-left:4px" id="btn-copy-upi">
                            📋 Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment Confirmation Form (Enter UTR) -->
            <div style="background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:16px;padding:20px;margin-bottom:20px">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                    <span style="font-size:22px">✅</span>
                    <div>
                        <h4 style="margin:0;font-size:14.5px;font-weight:800;color:#0F172A">Step 2: Confirm Payment After Transfer</h4>
                        <p style="margin:2px 0 0;font-size:12px;color:#64748B">Enter your 12-digit UPI Reference / UTR Number to verify and lock in your order</p>
                    </div>
                </div>

                <form action="{{ route('order.upi-payment.confirm', $order->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom:14px">
                        <label style="font-size:12px;font-weight:700;display:block;margin-bottom:6px;color:#334155">
                            UPI Transaction / UTR Number *
                        </label>
                        <input type="text" name="utr_number" required placeholder="e.g. 432587654321 (12 digits from PhonePe / GPay receipt)" style="width:100%;padding:12px 14px;border:1.5px solid #CBD5E1;border-radius:10px;font-size:14px;font-family:monospace;font-weight:700;box-sizing:border-box">
                        <span style="display:block;font-size:11px;color:#64748B;margin-top:4px">
                            💡 You can find the 12-digit UTR in your payment receipt on PhonePe, Google Pay, or Paytm.
                        </span>
                    </div>

                    <button type="submit" class="btn-theme" style="width:100%;padding:14px;font-size:15px;font-weight:800;background:#059669;border-color:#059669;border-radius:10px;display:flex;align-items:center;justify-content:center;gap:8px">
                        <span>✓ Confirm Payment &amp; Finalize Order</span>
                    </button>
                </form>
            </div>

            <!-- Order Items Quick Summary -->
            <div style="border-top:1px solid #E2E8F0;padding-top:16px;display:flex;justify-content:space-between;align-items:center;font-size:12.5px;color:#64748B;flex-wrap:wrap;gap:10px">
                <div>
                    Need help? Helpline: <strong style="color:#0F172A">+91 7679416294</strong>
                </div>
                <div>
                    <a href="{{ url('/orders') }}" style="color:#01684B;font-weight:700;text-decoration:none">View All Orders &rarr;</a>
                </div>
            </div>

        </div>

    </div>
</main>

<script>
function copyUpiId() {
    const upiId = "{{ $upiId }}";
    navigator.clipboard.writeText(upiId).then(() => {
        const btn = document.getElementById('btn-copy-upi');
        if (btn) {
            btn.innerHTML = '✓ Copied!';
            btn.style.background = '#DCFCE7';
            btn.style.color = '#166534';
            setTimeout(() => {
                btn.innerHTML = '📋 Copy';
                btn.style.background = '#EDE9FE';
                btn.style.color = '#5B21B6';
            }, 2500);
        }
    }).catch(err => {
        alert('UPI ID: ' + upiId);
    });
}
</script>
@endsection
