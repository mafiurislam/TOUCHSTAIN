@extends('layouts.app')

@section('content')
<main class="gf-container container" style="max-width:1100px;width:100%;margin:0 auto;padding:20px 24px 56px;box-sizing:border-box">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> &gt; <span>Checkout</span>
    </div>

    @php
        $isReg = !empty($isCustomerRegistered);
        $regUser = $registeredUser ?? null;
        $regAddr = $registeredAddress ?? null;

        $regAddressType = $regAddr->address_type ?? 'Home';
        $regName = $isReg
            ? ($regAddr->contact_person_name ?? trim(($regUser->f_name ?? '') . ' ' . ($regUser->l_name ?? '')))
            : '';
        $regPhone = $isReg
            ? ($regAddr->contact_person_number ?? ($regUser->phone ?? ''))
            : '';
        $regEmail = $isReg
            ? ($regUser->email ?? '')
            : '';
        $regStreetAddress = $isReg
            ? ($regAddr->address ?? '')
            : '';
        $regRoad = $isReg
            ? ($regAddr->road ?? '')
            : '';
        $regHouse = $isReg
            ? ($regAddr->house ?? '')
            : '';
    @endphp

    <div class="checkout-grid">
        <!-- Left: Options & Address & Payment Selection -->
        <div>
            <!-- 1. Delivery Type -->
            <div class="checkout-section">
                <h3>📍 1. Select Delivery Type &amp; Branch</h3>
                <div class="option-cards-grid">
                    <div class="option-card selected" id="opt-delivery" onclick="selectCheckoutDeliveryType('delivery')">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span style="font-size:24px">🚚</span>
                            <div>
                                <h4>Home Delivery</h4>
                                <p>Deliver to your doorstep (Free over ₹69)</p>
                            </div>
                        </div>
                    </div>
                    <div class="option-card" id="opt-pickup" onclick="selectCheckoutDeliveryType('self_pickup')">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span style="font-size:24px">🏪</span>
                            <div>
                                <h4>Self Pickup (₹0 Fee)</h4>
                                <p>Branch: TOUCHSTAINS Mumbai (Sion East)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Delivery Address (Input Form Design as Requested) -->
            <div class="checkout-section" id="checkout-address-section">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px">
                    <h3 style="margin-bottom:0;border-bottom:none;padding-bottom:0">🏠 2. Delivery Address</h3>
                    <span id="address-autofill-badge" style="font-size:12px;font-weight:700;color:#01684B;background:#EDF4F2;padding:4px 12px;border-radius:99px;border:1px solid #A7F3D0;display:{{ $isReg ? 'inline-flex' : 'none' }};align-items:center;gap:5px">
                        ✓ Auto-filled from Registration
                    </span>
                </div>

                <div style="border-top:1px solid var(--border);padding-top:16px">
                    <h4 style="font-size:16px;font-weight:800;color:var(--text-primary);margin:0 0 16px">+ Add New Delivery Address</h4>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Address Label</label>
                            <select name="address_type" id="chk-address-type" class="form-control">
                                <option value="Home" {{ $regAddressType === 'Home' ? 'selected' : '' }}>🏠 Home</option>
                                <option value="Workplace" {{ in_array($regAddressType, ['Workplace', 'Workplace / Shop']) ? 'selected' : '' }}>🏢 Workplace / Shop</option>
                                <option value="Other" {{ $regAddressType === 'Other' ? 'selected' : '' }}>📍 Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Contact Person Name</label>
                            <input type="text" name="contact_person_name" id="chk-customer-name" class="form-control" value="{{ $regName }}" placeholder="Enter customer full name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="contact_person_number" id="chk-customer-phone" class="form-control" value="{{ $regPhone }}" placeholder="e.g. +91 9876543210" required>
                        </div>
                        <div class="form-group">
                            <label>Street / City / Pin Code (Full Delivery Address)</label>
                            <input type="text" name="address" id="chk-customer-address" class="form-control" value="{{ $regStreetAddress }}" placeholder="e.g. Flat/Room No, Building Name, Road, City, State - PIN" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Landmark / Road</label>
                            <input type="text" name="road" id="chk-customer-road" class="form-control" value="{{ $regRoad }}" placeholder="Near NH-34 / Mosque / Market">
                        </div>
                        <div class="form-group">
                            <label>House / Flat No.</label>
                            <input type="text" name="house" id="chk-customer-house" class="form-control" value="{{ $regHouse }}" placeholder="House #12">
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:4px">
                        <button type="button" class="btn-primary" id="btn-save-checkout-address" onclick="saveCheckoutAddressToAccount()" style="background:#01684B;border-color:#01684B">
                            Save Address to Account
                        </button>
                        <span id="save-address-feedback" style="font-size:12.5px;color:#059669;font-weight:600;display:none">
                            ✓ Address saved to your account!
                        </span>
                    </div>
                </div>
            </div>

            <!-- 3. Preferred Delivery Time Slot -->
            <div class="checkout-section">
                <h3>🕒 3. Preferred Delivery Time Slot</h3>
                <div class="option-cards-grid" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr))">
                    <div class="option-card selected" onclick="selectCheckoutTimeSlot(this, 'Express Delivery (Today within 45 - 60 Mins)')">
                        <div>
                            <h4>⚡ Express Delivery</h4>
                            <p>Today within 45 - 60 Mins</p>
                        </div>
                    </div>
                    <div class="option-card" onclick="selectCheckoutTimeSlot(this, 'Morning Slot (09:00 AM - 01:00 PM)')">
                        <div>
                            <h4>🌅 Morning Slot</h4>
                            <p>09:00 AM - 01:00 PM</p>
                        </div>
                    </div>
                    <div class="option-card" onclick="selectCheckoutTimeSlot(this, 'Evening Slot (04:00 PM - 09:00 PM)')">
                        <div>
                            <h4>🌆 Evening Slot</h4>
                            <p>04:00 PM - 09:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Select Payment Method (ONLY Razorpay & Cash on Delivery) -->
            <div class="checkout-section">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                    <h3 style="margin-bottom:0">💳 4. Select Payment Method</h3>
                    <span style="font-size:12px;color:#059669;font-weight:700">🔒 256-Bit SSL Secured</span>
                </div>

                <div class="option-cards-grid" style="grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px">
                    <!-- Option 1: Online Payment (Razorpay) -->
                    <div class="option-card selected" id="pay-razorpay" onclick="selectCheckoutPayment('razorpay')">
                        <div style="display:flex;align-items:flex-start;gap:12px">
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg, #0C2340 0%, #1A365D 100%);display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(12,35,64,0.25);flex-shrink:0">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22 6L11 20H4L12 8H7L12 2H18L13 6H22Z" fill="#38BDF8"/>
                                </svg>
                            </div>
                            <div style="flex:1">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:6px">
                                    <h4 style="margin:0;font-size:15px;font-weight:700;color:#111827">Online Payment (Razorpay)</h4>
                                    <span style="font-size:11px;font-weight:700;background:#DCFCE7;color:#166534;padding:2px 8px;border-radius:99px;border:1px solid #BBF7D0">Instant</span>
                                </div>
                                <p style="font-size:12px;color:#4B5563;margin:4px 0 8px;line-height:1.4">
                                    UPI (Google Pay, PhonePe, Paytm, BHIM), Cards, NetBanking, Wallets
                                </p>
                                <div style="display:flex;flex-wrap:wrap;gap:6px">
                                    <span style="font-size:10.5px;font-weight:700;background:#F3F4F6;color:#374151;padding:2px 8px;border-radius:4px;border:1px solid #E5E7EB">📱 UPI</span>
                                    <span style="font-size:10.5px;font-weight:700;background:#F3F4F6;color:#374151;padding:2px 8px;border-radius:4px;border:1px solid #E5E7EB">💳 Cards</span>
                                    <span style="font-size:10.5px;font-weight:700;background:#F3F4F6;color:#374151;padding:2px 8px;border-radius:4px;border:1px solid #E5E7EB">🏦 NetBanking</span>
                                    <span style="font-size:10.5px;font-weight:700;background:#F3F4F6;color:#374151;padding:2px 8px;border-radius:4px;border:1px solid #E5E7EB">👛 Wallets</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Cash on Delivery -->
                    <div class="option-card" id="pay-cod" onclick="selectCheckoutPayment('cash_on_delivery')">
                        <div style="display:flex;align-items:flex-start;gap:12px">
                            <div style="width:40px;height:40px;border-radius:10px;background:#F0FDF4;border:1.5px solid #86EFAC;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
                                💵
                            </div>
                            <div style="flex:1">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:6px">
                                    <h4 style="margin:0;font-size:15px;font-weight:700;color:#111827">Cash on Delivery</h4>
                                    <span style="font-size:11px;font-weight:700;background:#FEF3C7;color:#92400E;padding:2px 8px;border-radius:99px;border:1px solid #FDE68A">COD</span>
                                </div>
                                <p style="font-size:12px;color:#4B5563;margin:4px 0 0;line-height:1.4">
                                    Pay cash or UPI to the delivery executive when your package arrives at your door.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Razorpay Security Box -->
                <div id="razorpay-info-box" style="margin-top:16px;background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:12px;padding:14px 18px">
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="font-size:22px">🛡️</div>
                        <div style="flex:1">
                            <strong style="color:#0F172A;font-size:13px;display:block">Razorpay Trusted Payment Gateway</strong>
                            <span style="font-size:12px;color:#475569">
                                Seamlessly pay with any UPI App (GPay, PhonePe, Paytm), Credit/Debit Card (Visa, MasterCard, RuPay), NetBanking (SBI, HDFC, ICICI, Axis), or Cred. Instant verification with zero extra charge!
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Note -->
                <div class="form-group" style="margin-top:16px;margin-bottom:0">
                    <label style="font-size:13px;font-weight:600">Order Note / Delivery Instructions (Optional)</label>
                    <input type="text" id="checkout-order-note" class="form-control" placeholder="e.g. Landmark, please call on arrival, or deliver after 2 PM...">
                </div>
            </div>
        </div>

        <!-- Right: Order Summary & Action Button -->
        <div>
            <div class="summary-card">
                <h3>Order Summary</h3>
                <div id="checkout-items-preview" style="max-height:240px;overflow-y:auto;margin-bottom:14px;border-bottom:1px solid var(--border);padding-bottom:10px"></div>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="chk-subtotal">₹0</span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span id="chk-delivery">FREE</span>
                </div>
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span class="amount" id="chk-total">₹0</span>
                </div>

                <button type="button" class="btn-primary" id="btn-confirm-order" style="width:100%;justify-content:center;margin-top:18px;padding:15px;font-size:15px;background:#01684B;border-color:#01684B;box-shadow:0 4px 14px rgba(1,104,75,0.3)" onclick="handleCheckoutSubmit()">
                    ⚡ Pay Now via Razorpay
                </button>

                <div style="text-align:center;margin-top:12px;font-size:11.5px;color:#6B7280;display:flex;align-items:center;justify-content:center;gap:6px">
                    <span>🔒 Secured by <strong>Razorpay</strong> &bull; 100% Buyer Protection</span>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<!-- Official Razorpay Checkout JS SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
let currentPaymentMethod = 'razorpay';
const RAZORPAY_KEY = "{{ $razorpayKey ?? 'rzp_test_U1cQQReArMfXP9' }}";
const SERVER_CUSTOMER_REGISTERED = {{ $isReg ? 'true' : 'false' }};
const REGISTER_CHECKOUT_URL = "{{ url('/register?redirect=checkout') }}";

function getLocalRegisteredCustomer() {
    try {
        const raw = localStorage.getItem('touchstains_registered_customer');
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        return (parsed && parsed.registered && parsed.phone && parsed.address) ? parsed : null;
    } catch (e) {
        return null;
    }
}

function isCustomerAccountRegistered() {
    return SERVER_CUSTOMER_REGISTERED || Boolean(getLocalRegisteredCustomer());
}

function hydrateRegisteredCustomerAddress() {
    const localCust = getLocalRegisteredCustomer();
    if (!localCust) return;

    const typeEl = document.getElementById('chk-address-type');
    const nameEl = document.getElementById('chk-customer-name');
    const phoneEl = document.getElementById('chk-customer-phone');
    const addrEl = document.getElementById('chk-customer-address');
    const roadEl = document.getElementById('chk-customer-road');
    const houseEl = document.getElementById('chk-customer-house');

    if (typeEl && localCust.address_type) typeEl.value = localCust.address_type;
    if (nameEl && !nameEl.value.trim()) nameEl.value = localCust.contact_person_name || localCust.name || '';
    if (phoneEl && !phoneEl.value.trim()) phoneEl.value = localCust.phone || '';
    if (addrEl && !addrEl.value.trim()) {
        let fullAddr = localCust.address || '';
        if (localCust.pincode && !fullAddr.includes(localCust.pincode)) {
            fullAddr += ' - ' + localCust.pincode;
        }
        addrEl.value = fullAddr;
    }
    if (roadEl && !roadEl.value.trim()) roadEl.value = localCust.road || '';
    if (houseEl && !houseEl.value.trim()) houseEl.value = localCust.house || '';

    const badge = document.getElementById('address-autofill-badge');
    if (badge) badge.style.display = 'inline-flex';
}

async function saveCheckoutAddressToAccount() {
    const addressType = document.getElementById('chk-address-type')?.value || 'Home';
    const contactName = document.getElementById('chk-customer-name')?.value.trim() || '';
    const contactPhone = document.getElementById('chk-customer-phone')?.value.trim() || '';
    const streetAddress = document.getElementById('chk-customer-address')?.value.trim() || '';
    const landmarkRoad = document.getElementById('chk-customer-road')?.value.trim() || '';
    const houseNo = document.getElementById('chk-customer-house')?.value.trim() || '';

    if (!contactName) {
        GetgozoApp.showToast('Please enter Contact Person Name.', 'error');
        document.getElementById('chk-customer-name')?.focus();
        return;
    }
    if (!contactPhone) {
        GetgozoApp.showToast('Please enter Phone Number.', 'error');
        document.getElementById('chk-customer-phone')?.focus();
        return;
    }
    if (!streetAddress) {
        GetgozoApp.showToast('Please enter Street / City / Pin Code (Full Delivery Address).', 'error');
        document.getElementById('chk-customer-address')?.focus();
        return;
    }

    // Save locally as well
    const existingLocal = getLocalRegisteredCustomer() || {};
    const updatedCustomer = {
        ...existingLocal,
        registered: true,
        name: contactName,
        contact_person_name: contactName,
        phone: contactPhone,
        address_type: addressType,
        address: streetAddress,
        road: landmarkRoad,
        house: houseNo,
        updated_at: new Date().toISOString()
    };
    localStorage.setItem('touchstains_registered_customer', JSON.stringify(updatedCustomer));

    const btn = document.getElementById('btn-save-checkout-address');
    const feedback = document.getElementById('save-address-feedback');
    const origText = btn ? btn.innerHTML : 'Save Address to Account';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
    }

    try {
        const res = await fetch("{{ url('/address') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.LARAVEL_CSRF_TOKEN
            },
            body: JSON.stringify({
                address_type: addressType,
                contact_person_name: contactName,
                contact_person_number: contactPhone,
                address: streetAddress,
                road: landmarkRoad,
                house: houseNo
            })
        });
        const data = await res.json();
        if (data && data.address && data.address.id && window.GetgozoApp) {
            GetgozoApp.state.selectedAddressId = data.address.id;
        }
    } catch (e) {
        console.warn('Local address save completed:', e);
    }

    if (btn) {
        btn.disabled = false;
        btn.innerHTML = origText;
    }
    if (feedback) {
        feedback.style.display = 'inline';
    }
    hydrateRegisteredCustomerAddress();
    GetgozoApp.showToast('✓ Delivery address saved to your account!', 'success');
}

function getCheckoutAddressData() {
    return {
        address_type: document.getElementById('chk-address-type')?.value || 'Home',
        contact_person_name: document.getElementById('chk-customer-name')?.value.trim() || '',
        contact_person_number: document.getElementById('chk-customer-phone')?.value.trim() || '',
        address: document.getElementById('chk-customer-address')?.value.trim() || '',
        road: document.getElementById('chk-customer-road')?.value.trim() || '',
        house: document.getElementById('chk-customer-house')?.value.trim() || '',
    };
}

function selectCheckoutDeliveryType(type) {
    if (window.GetgozoApp) {
        GetgozoApp.setDeliveryType(type);
    }
    const optDel = document.getElementById('opt-delivery');
    const optPick = document.getElementById('opt-pickup');
    const addrSec = document.getElementById('checkout-address-section');
    if (type === 'delivery') {
        optDel?.classList.add('selected');
        optPick?.classList.remove('selected');
        if (addrSec) addrSec.style.display = 'block';
    } else {
        optDel?.classList.remove('selected');
        optPick?.classList.add('selected');
        if (addrSec) addrSec.style.display = 'none';
    }
}

function selectCheckoutTimeSlot(el, slotText) {
    document.querySelectorAll('.option-cards-grid .option-card').forEach(c => {
        if (!c.id) c.classList.remove('selected');
    });
    el.classList.add('selected');
    if (window.GetgozoApp) {
        GetgozoApp.state.selectedTimeSlot = slotText;
    }
}

function selectCheckoutPayment(method) {
    currentPaymentMethod = method;
    if (window.GetgozoApp) {
        GetgozoApp.state.paymentMethod = method;
    }

    const payRzp = document.getElementById('pay-razorpay');
    const payCod = document.getElementById('pay-cod');
    const rzpBox = document.getElementById('razorpay-info-box');
    const btn = document.getElementById('btn-confirm-order');

    if (method === 'razorpay') {
        payRzp?.classList.add('selected');
        payCod?.classList.remove('selected');
        if (rzpBox) rzpBox.style.display = 'block';
        if (btn) {
            btn.innerHTML = '⚡ Pay Now via Razorpay';
            btn.style.background = '#01684B';
            btn.style.borderColor = '#01684B';
            btn.style.boxShadow = '0 4px 14px rgba(1,104,75,0.3)';
        }
    } else {
        payCod?.classList.add('selected');
        payRzp?.classList.remove('selected');
        if (rzpBox) rzpBox.style.display = 'none';
        if (btn) {
            btn.innerHTML = '✓ Confirm Order (Cash on Delivery)';
            btn.style.background = '#2563EB';
            btn.style.borderColor = '#2563EB';
            btn.style.boxShadow = '0 4px 14px rgba(37,99,235,0.3)';
        }
    }
}

async function handleCheckoutSubmit() {
    // 1. Ensure customer is registered/logged in via global Auth Modal
    if (!isCustomerAccountRegistered()) {
        if (window.TouchstainsAuth) {
            GetgozoApp.showToast('🔔 অর্ডার প্লেস করতে অনুগ্রহ করে আগে রেজিস্ট্রেশন অথবা লগইন করুন!', 'info');
            window.TouchstainsAuth.open('register', () => {
                hydrateRegisteredCustomerAddress();
            });
            return;
        }
        window.location.href = REGISTER_CHECKOUT_URL;
        return;
    }

    const totals = GetgozoApp.getCartTotals();
    if (totals.items.length === 0) {
        GetgozoApp.showToast('Your shopping bag is empty! Please add a product first.', 'error');
        return;
    }
    const minOrder = 1;
    if (totals.subtotal < minOrder) {
        GetgozoApp.showToast('Minimum order value is ₹' + minOrder, 'error');
        return;
    }

    // 2. Validate Delivery Address Fields in Section 2
    const addrData = getCheckoutAddressData();
    const isDelivery = (GetgozoApp.state.deliveryType || 'delivery') === 'delivery';
    if (isDelivery) {
        if (!addrData.contact_person_name) {
            GetgozoApp.showToast('Please enter Contact Person Name in Delivery Address.', 'error');
            document.getElementById('chk-customer-name')?.focus();
            return;
        }
        if (!addrData.contact_person_number) {
            GetgozoApp.showToast('Please enter Phone Number in Delivery Address.', 'error');
            document.getElementById('chk-customer-phone')?.focus();
            return;
        }
        if (!addrData.address) {
            GetgozoApp.showToast('Please enter your Full Delivery Address (Street / City / Pin Code).', 'error');
            document.getElementById('chk-customer-address')?.focus();
            return;
        }
    }

    if (currentPaymentMethod === 'razorpay') {
        await initiateRazorpayPayment(totals, addrData);
    } else {
        await placeCodOrder(totals, addrData);
    }
}

/**
 * Handle Razorpay Online Payment Flow
 */
async function initiateRazorpayPayment(totals, addrData) {
    const btn = document.getElementById('btn-confirm-order');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Initializing Razorpay Gateway...';
    }

    const orderAmount = GetgozoApp.state.deliveryType === 'self_pickup' 
        ? (totals.subtotal - totals.couponDiscount) 
        : totals.grandTotal;

    const payload = {
        items: totals.items,
        subtotal: totals.subtotal,
        discount: totals.totalDiscount,
        coupon_discount: totals.couponDiscount,
        coupon_code: GetgozoApp.state.appliedCoupon ? GetgozoApp.state.appliedCoupon.code : null,
        delivery_charge: GetgozoApp.state.deliveryType === 'self_pickup' ? 0 : totals.deliveryFee,
        order_amount: orderAmount,
        order_type: GetgozoApp.state.deliveryType || 'delivery',
        time_slot: GetgozoApp.state.selectedTimeSlot || 'Express Delivery (Today within 45 - 60 Mins)',
        address_id: GetgozoApp.state.selectedAddressId || null,
        order_note: document.getElementById('checkout-order-note')?.value || '',
        address_type: addrData.address_type,
        contact_person_name: addrData.contact_person_name,
        contact_person_number: addrData.contact_person_number,
        address: addrData.address,
        road: addrData.road,
        house: addrData.house,
    };

    try {
        const response = await fetch("{{ route('checkout.create-razorpay-order') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.LARAVEL_CSRF_TOKEN
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!data || !data.status) {
            GetgozoApp.showToast(data.message || 'Failed to initialize Razorpay payment. Please try again.', 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
            return;
        }

        // Open Razorpay Standard Checkout Popup
        const rawContact = String(data.customer_phone || "917679416294").split('/')[0].replace(/\D/g, '').slice(-10);
        const cleanContact = rawContact.length === 10 ? ('+91' + rawContact) : '+917679416294';

        const options = {
            key: data.key || RAZORPAY_KEY,
            amount: data.amount,
            currency: data.currency || "INR",
            name: "TOUCHSTAINS™",
            description: "Fabric Stain Remover Order #" + data.order_id,
            image: "https://mafiurislam.github.io/TOUCHSTAIN/storage/restaurant/touchstains-logo.png",
            prefill: {
                name: data.customer_name || "Valued Customer",
                email: data.customer_email || "customer@touchstains.com",
                contact: cleanContact
            },
            theme: {
                color: "#01684B" // TOUCHSTAINS brand green
            },
            notes: {
                order_id: String(data.order_id)
            },
            handler: async function (rzpResponse) {
                // Payment was completed successfully in Razorpay modal!
                if (btn) btn.innerHTML = '⏳ Verifying Payment...';
                
                try {
                    const verifyRes = await fetch("{{ route('checkout.verify-razorpay-payment') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': window.LARAVEL_CSRF_TOKEN
                        },
                        body: JSON.stringify({
                            order_id: data.order_id,
                            razorpay_order_id: rzpResponse.razorpay_order_id || data.razorpay_order_id || null,
                            razorpay_payment_id: rzpResponse.razorpay_payment_id,
                            razorpay_signature: rzpResponse.razorpay_signature || null,
                        })
                    });

                    const verifyData = await verifyRes.json();

                    if (verifyData && verifyData.status) {
                        GetgozoApp.state.cart = {};
                        GetgozoApp.state.appliedCoupon = null;
                        GetgozoApp.saveCart();
                        GetgozoApp.showToast('✓ Payment successful! Order confirmed.', 'success');
                        setTimeout(() => {
                            window.location.href = verifyData.redirect_url || ("{{ url('/order-success') }}?id=" + data.order_id);
                        }, 500);
                    } else {
                        GetgozoApp.showToast(verifyData.message || 'Payment verification failed.', 'error');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = origHtml;
                        }
                    }
                } catch (err) {
                    console.error('Verification error:', err);
                    GetgozoApp.showToast('Network error verifying payment. Please refresh or contact support.', 'error');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                    }
                }
            },
            modal: {
                ondismiss: function () {
                    GetgozoApp.showToast('Payment window closed. You can retry payment anytime.', 'info');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                    }
                }
            }
        };

        if (data.razorpay_order_id) {
            options.order_id = data.razorpay_order_id;
        }

        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function (failResponse) {
            console.error('Payment failed:', failResponse.error);
            GetgozoApp.showToast('Payment failed: ' + (failResponse.error.description || 'Transaction declined'), 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        });

        rzp.open();

    } catch (err) {
        console.error('Razorpay init error:', err);
        GetgozoApp.showToast('Unable to connect to payment gateway. Please try again.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
}

/**
 * Handle Cash on Delivery Flow (Clean JSON POST + Full Error Reporting)
 */
async function placeCodOrder(totals, addrData) {
    const btn = document.getElementById('btn-confirm-order');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Confirming COD Order...';
    }

    const orderAmount = GetgozoApp.state.deliveryType === 'self_pickup'
        ? (totals.subtotal - totals.couponDiscount)
        : totals.grandTotal;

    const addressPayload = addrData || getCheckoutAddressData();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.LARAVEL_CSRF_TOKEN || '';

    const payload = {
        items: totals.items,
        subtotal: totals.subtotal,
        discount: totals.totalDiscount || 0,
        coupon_discount: totals.couponDiscount || 0,
        coupon_code: GetgozoApp.state.appliedCoupon ? GetgozoApp.state.appliedCoupon.code : null,
        delivery_charge: GetgozoApp.state.deliveryType === 'self_pickup' ? 0 : totals.deliveryFee,
        order_amount: orderAmount,
        payment_method: 'cash_on_delivery',
        order_type: GetgozoApp.state.deliveryType || 'delivery',
        time_slot: GetgozoApp.state.selectedTimeSlot || 'Express Delivery (Today within 45 - 60 Mins)',
        address_id: GetgozoApp.state.selectedAddressId || null,
        order_note: document.getElementById('checkout-order-note')?.value || '',
        address_type: addressPayload.address_type || 'Home',
        contact_person_name: addressPayload.contact_person_name || '',
        contact_person_number: addressPayload.contact_person_number || '',
        address: addressPayload.address || '',
        road: addressPayload.road || '',
        house: addressPayload.house || ''
    };

    try {
        const res = await fetch("{{ route('checkout.place-order') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        });

        let data = null;
        try {
            data = await res.json();
        } catch (parseErr) {
            data = null;
        }

        if (res.ok && data && data.status) {
            // Clear cart silently in storage without flashing "empty bag" before redirect
            GetgozoApp.state.cart = {};
            GetgozoApp.state.appliedCoupon = null;
            localStorage.setItem('getgozo_cart_v2', '{}');
            localStorage.removeItem('getgozo_cart');
            GetgozoApp.showToast('✓ Order placed successfully with Cash on Delivery!', 'success');
            if (btn) btn.innerHTML = '✓ Order Confirmed! Redirecting...';
            window.location.href = data.redirect_url || ("{{ url('/order-success') }}?id=" + data.order_id);
        } else {
            GetgozoApp.showToast((data && data.message) || 'Failed to place COD order (HTTP ' + res.status + '). Please try again.', 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    } catch (e) {
        console.error('COD order error:', e);
        GetgozoApp.showToast('Unable to connect to server. Please check your internet and try again.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    hydrateRegisteredCustomerAddress();
    selectCheckoutPayment('razorpay');
});
</script>
@endpush
@endsection
