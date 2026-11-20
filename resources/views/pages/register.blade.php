@extends('layouts.app')

@section('content')
<style>
    .ts-page-auth-wrap {
        max-width: 740px;
        margin: 28px auto 56px;
        padding: 0 16px;
        box-sizing: border-box;
    }
    .ts-page-auth-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
    }
    .ts-page-auth-banner {
        background: #ffffff;
        color: #0F172A;
        padding: 26px 24px 18px;
        text-align: center;
        border-bottom: 1px solid #F1F5F9;
        position: relative;
    }
    .ts-page-auth-body {
        padding: 28px 30px 32px;
    }
    .ts-step-card {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 18px 18px 8px;
        margin-bottom: 18px;
    }
    .ts-step-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }
    .ts-step-num {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #01684B;
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ts-page-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    .ts-page-field {
        margin-bottom: 14px;
    }
    .ts-page-field label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 6px;
    }
    .ts-page-field .ts-p-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .ts-page-field .ts-p-icon {
        position: absolute;
        left: 13px;
        font-size: 15px;
        pointer-events: none;
    }
    .ts-page-field input,
    .ts-page-field select {
        width: 100%;
        height: 45px;
        padding: 0 14px 0 38px;
        border: 1.5px solid #CBD5E1;
        border-radius: 11px;
        font-size: 14px;
        color: #0F172A;
        background: #ffffff;
        box-sizing: border-box;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }
    .ts-page-field select {
        padding-left: 14px;
    }
    .ts-page-field input:focus,
    .ts-page-field select:focus {
        outline: none;
        border-color: #01684B;
        box-shadow: 0 0 0 3.5px rgba(1, 104, 75, 0.12);
    }
    .ts-page-submit-btn {
        width: 100%;
        height: 50px;
        border: none;
        border-radius: 13px;
        background: linear-gradient(135deg, #01684B 0%, #059669 100%);
        color: #ffffff;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(1, 104, 75, 0.28);
        transition: transform 0.14s ease, box-shadow 0.14s ease;
    }
    .ts-page-submit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(1, 104, 75, 0.34);
    }
    @media (max-width: 640px) {
        .ts-page-auth-wrap {
            margin: 14px auto 40px;
            padding: 0 12px;
        }
        .ts-page-auth-banner {
            padding: 22px 18px 18px;
        }
        .ts-page-auth-body {
            padding: 20px 16px 24px;
        }
        .ts-step-card {
            padding: 14px 14px 4px;
        }
        .ts-page-grid-2 {
            grid-template-columns: 1fr;
            gap: 0;
        }
        .ts-page-field input,
        .ts-page-field select {
            height: 47px;
            font-size: 16px;
        }
    }
</style>

<main class="ts-page-auth-wrap">
    <div class="ts-page-auth-card">
        <div class="ts-page-auth-banner">
            <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:54px;margin:0 auto 12px;object-fit:contain;display:block" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
            <h1 style="font-size:22px;font-weight:800;margin:0 0 6px;color:#0F172A" data-i18n="create_account">Customer Registration</h1>
            <p style="font-size:13px;color:#64748B;margin:0;max-width:480px;margin-inline:auto">
                No password required! Enter your details &amp; delivery address once — it automatically fills in on every order.
            </p>
        </div>

        <div class="ts-page-auth-body">
            @if(request('redirect') === 'checkout')
                <div style="background:#ECFDF5;border:1.5px solid #A7F3D0;color:#065F46;padding:12px 16px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:18px;display:flex;align-items:center;gap:10px">
                    <span style="font-size:20px">🛒</span>
                    <span>Complete your quick registration below to automatically fill your delivery address at Checkout.</span>
                </div>
            @endif

            <form action="{{ url('/register') }}" method="POST" id="customerRegisterForm" onsubmit="syncRegistrationToLocalStorage()">
                @csrf
                <input type="hidden" name="redirect" value="{{ request('redirect', 'checkout') }}">

                <!-- Step 1: Customer Personal Info -->
                <div class="ts-step-card">
                    <div class="ts-step-head">
                        <span class="ts-step-num">1</span>
                        <div>
                            <strong style="font-size:14.5px;color:#0F172A;display:block">Personal Details</strong>
                            <span style="font-size:11.5px;color:#64748B">Simple passwordless registration with your mobile number</span>
                        </div>
                    </div>

                    <div class="ts-page-grid-2">
                        <div class="ts-page-field">
                            <label>First Name <span style="color:#DC2626">*</span></label>
                            <div class="ts-p-input-wrap">
                                <span class="ts-p-icon">👤</span>
                                <input type="text" name="f_name" id="reg_f_name" value="{{ old('f_name') }}" placeholder="Enter First Name" required oninput="autoFillContactPerson()">
                            </div>
                        </div>
                        <div class="ts-page-field">
                            <label>Last Name <span style="color:#DC2626">*</span></label>
                            <div class="ts-p-input-wrap">
                                <span class="ts-p-icon">👤</span>
                                <input type="text" name="l_name" id="reg_l_name" value="{{ old('l_name') }}" placeholder="Enter Last Name" required oninput="autoFillContactPerson()">
                            </div>
                        </div>
                    </div>

                    <div class="ts-page-grid-2">
                        <div class="ts-page-field">
                            <label>Mobile Number <span style="color:#DC2626">*</span></label>
                            <div class="ts-p-input-wrap">
                                <span class="ts-p-icon">📱</span>
                                <input type="tel" name="phone" id="reg_phone" value="{{ old('phone') }}" placeholder="+91 9876543210" required>
                            </div>
                        </div>
                        <div class="ts-page-field">
                            <label>Email Address <span style="color:#64748B;font-weight:500">(Optional)</span></label>
                            <div class="ts-p-input-wrap">
                                <span class="ts-p-icon">✉️</span>
                                <input type="email" name="email" id="reg_email" value="{{ old('email') }}" placeholder="you@example.com (Optional)">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Delivery Address -->
                <div class="ts-step-card">
                    <div class="ts-step-head">
                        <span class="ts-step-num">2</span>
                        <div>
                            <strong style="font-size:14.5px;color:#0F172A;display:block">Delivery Address (Auto-Filled at Checkout)</strong>
                            <span style="font-size:11.5px;color:#64748B">Saved permanently to your account for instant ordering</span>
                        </div>
                    </div>

                    <div class="ts-page-grid-2">
                        <div class="ts-page-field">
                            <label>Address Label <span style="color:#DC2626">*</span></label>
                            <select name="address_type" id="reg_address_type">
                                <option value="Home">🏠 Home</option>
                                <option value="Workplace">🏢 Workplace / Shop</option>
                                <option value="Other">📍 Other</option>
                            </select>
                        </div>
                        <div class="ts-page-field">
                            <label>Contact Person Name <span style="color:#DC2626">*</span></label>
                            <div class="ts-p-input-wrap">
                                <span class="ts-p-icon">🪪</span>
                                <input type="text" name="contact_person_name" id="reg_contact_person_name" value="{{ old('contact_person_name') }}" placeholder="Recipient Full Name" required>
                            </div>
                        </div>
                    </div>

                    <div class="ts-page-grid-2">
                        <div class="ts-page-field">
                            <label>Full Delivery Address <span style="color:#DC2626">*</span></label>
                            <div class="ts-p-input-wrap">
                                <span class="ts-p-icon">📍</span>
                                <input type="text" name="address" id="reg_address" value="{{ old('address') }}" placeholder="Flat/Building, Street, Area, City, State" required>
                            </div>
                        </div>
                        <div class="ts-page-field">
                            <label>Pin Code <span style="color:#DC2626">*</span></label>
                            <div class="ts-p-input-wrap">
                                <span class="ts-p-icon">📮</span>
                                <input type="text" name="pincode" id="reg_pincode" value="{{ old('pincode') }}" placeholder="e.g. 400022" maxlength="6" required>
                            </div>
                        </div>
                    </div>

                    <div class="ts-page-grid-2">
                        <div class="ts-page-field">
                            <label>Landmark / Road</label>
                            <div class="ts-p-input-wrap">
                                <span class="ts-p-icon">🛣️</span>
                                <input type="text" name="road" id="reg_road" value="{{ old('road') }}" placeholder="Near Station / Mosque / Market">
                            </div>
                        </div>
                        <div class="ts-page-field">
                            <label>House / Flat No.</label>
                            <div class="ts-p-input-wrap">
                                <span class="ts-p-icon">🚪</span>
                                <input type="text" name="house" id="reg_house" value="{{ old('house') }}" placeholder="House #12">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="ts-page-submit-btn">
                    ✓ Complete Registration &amp; Save Delivery Address →
                </button>
            </form>

            <div style="text-align:center;margin-top:20px;font-size:13.5px;color:#475569">
                Already registered an account?
                <a href="{{ url('/login' . (request('redirect') ? '?redirect=' . request('redirect') : '')) }}" style="color:#01684B;font-weight:800;text-decoration:underline" data-i18n="login">Login Here →</a>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
function autoFillContactPerson() {
    const fn = document.getElementById('reg_f_name')?.value.trim() || '';
    const ln = document.getElementById('reg_l_name')?.value.trim() || '';
    const cp = document.getElementById('reg_contact_person_name');
    if (cp && !cp.dataset.manuallyEdited) {
        cp.value = (fn + ' ' + ln).trim();
    }
}
document.getElementById('reg_contact_person_name')?.addEventListener('input', function() {
    this.dataset.manuallyEdited = 'true';
});

function syncRegistrationToLocalStorage() {
    const fn = document.getElementById('reg_f_name')?.value.trim() || '';
    const ln = document.getElementById('reg_l_name')?.value.trim() || '';
    const fullName = document.getElementById('reg_contact_person_name')?.value.trim() || (fn + ' ' + ln).trim();
    const phone = document.getElementById('reg_phone')?.value.trim() || '';
    const email = document.getElementById('reg_email')?.value.trim() || '';
    const addrType = document.getElementById('reg_address_type')?.value || 'Home';
    const address = document.getElementById('reg_address')?.value.trim() || '';
    const pincode = document.getElementById('reg_pincode')?.value.trim() || '';
    const road = document.getElementById('reg_road')?.value.trim() || '';
    const house = document.getElementById('reg_house')?.value.trim() || '';

    const customerData = {
        registered: true,
        f_name: fn,
        l_name: ln,
        name: fullName,
        phone: phone,
        email: email,
        address_type: addrType,
        contact_person_name: fullName,
        contact_person_number: phone,
        address: pincode && !address.includes(pincode) ? (address + ' - ' + pincode) : address,
        pincode: pincode,
        road: road,
        house: house
    };
    localStorage.setItem('touchstains_registered_customer', JSON.stringify(customerData));
    localStorage.setItem('getgozo_user', JSON.stringify({ name: fullName, email: email, phone: phone }));
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        const raw = localStorage.getItem('touchstains_registered_customer');
        if (!raw) return;
        const c = JSON.parse(raw);
        if (!c) return;
        const setIfEmpty = (id, val) => {
            const el = document.getElementById(id);
            if (el && !el.value.trim() && val) el.value = val;
        };
        setIfEmpty('reg_f_name', c.f_name || '');
        setIfEmpty('reg_l_name', c.l_name || '');
        setIfEmpty('reg_phone', c.phone || '');
        setIfEmpty('reg_email', c.email || '');
        setIfEmpty('reg_contact_person_name', c.contact_person_name || c.name || '');
        setIfEmpty('reg_address', c.address || '');
        setIfEmpty('reg_pincode', c.pincode || '');
        setIfEmpty('reg_road', c.road || '');
        setIfEmpty('reg_house', c.house || '');
        const typeEl = document.getElementById('reg_address_type');
        if (typeEl && c.address_type) typeEl.value = c.address_type;
    } catch (e) {}
});
</script>
@endpush
@endsection
