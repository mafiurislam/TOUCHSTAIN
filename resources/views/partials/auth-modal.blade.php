<!-- PREMIUM FLIPKART / MEESHO STYLE CUSTOMER REGISTRATION & LOGIN MODAL + HEADER DROPDOWN STYLES -->
<style>
    /* ===== HEADER ACCOUNT ICON & DROPDOWN ===== */
    .ts-account-menu-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .ts-account-trigger {
        position: relative;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .ts-account-trigger.is-logged-in {
        background: #ECFDF5 !important;
        border: 1.5px solid #01684B !important;
    }
    .ts-account-dot {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
        border: 1.5px solid #fff;
    }
    .ts-account-dropdown {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 248px;
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.16), 0 4px 12px rgba(15, 23, 42, 0.06);
        padding: 8px;
        z-index: 9995;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px) scale(0.97);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ts-account-menu-wrap.open .ts-account-dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }
    .ts-account-dropdown::before {
        content: "";
        position: absolute;
        top: -6px;
        right: 16px;
        width: 12px;
        height: 12px;
        background: #ffffff;
        border-left: 1px solid #E2E8F0;
        border-top: 1px solid #E2E8F0;
        transform: rotate(45deg);
    }
    .ts-dd-head {
        padding: 10px 12px 10px;
        border-bottom: 1px solid #F1F5F9;
        margin-bottom: 6px;
    }
    .ts-dd-head strong {
        display: block;
        font-size: 13.5px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.3;
    }
    .ts-dd-head span {
        display: block;
        font-size: 11.5px;
        color: #64748B;
        margin-top: 2px;
    }
    .ts-dd-item {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 12px;
        border-radius: 11px;
        border: none;
        background: transparent;
        text-align: left;
        text-decoration: none;
        color: #1E293B;
        cursor: pointer;
        transition: background 0.16s ease, transform 0.12s ease;
    }
    .ts-dd-item:hover {
        background: #F0FDF4;
        color: #01684B;
    }
    .ts-dd-item-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        background: #F1F5F9;
    }
    .ts-dd-item:hover .ts-dd-item-icon {
        background: #DCFCE7;
    }
    .ts-dd-item-primary .ts-dd-item-icon {
        background: linear-gradient(135deg, #01684B, #059669);
        color: #fff;
    }
    .ts-dd-item-danger:hover {
        background: #FEF2F2;
        color: #DC2626;
    }
    .ts-dd-item-danger .ts-dd-item-icon {
        background: #FEE2E2;
        color: #DC2626;
    }
    .ts-dd-item-text strong {
        display: block;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.25;
    }
    .ts-dd-item-text small {
        display: block;
        font-size: 11px;
        color: #64748B;
        margin-top: 1px;
    }
    @media (max-width: 520px) {
        .ts-account-dropdown {
            right: -36px;
            width: 236px;
        }
        .ts-account-dropdown::before {
            right: 52px;
        }
    }

    /* ===== PREMIUM RESPONSIVE AUTH MODAL ===== */
    .ts-auth-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        overflow-y: auto;
    }
    .ts-auth-backdrop.open {
        display: flex;
        animation: tsBackdropFade 0.22s ease-out;
    }
    @keyframes tsBackdropFade {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes tsDialogPop {
        from { opacity: 0; transform: translateY(20px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .ts-auth-dialog {
        width: 100%;
        max-width: 520px;
        background: #ffffff;
        border-radius: 22px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 28px 65px rgba(15, 23, 42, 0.34);
        position: relative;
        max-height: 92vh;
        animation: tsDialogPop 0.26s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ts-auth-clean-head {
        text-align: center;
        padding: 20px 48px 12px 24px;
        border-bottom: 1px solid #F1F5F9;
        background: #ffffff;
        flex-shrink: 0;
    }
    .ts-auth-clean-head img {
        height: 44px;
        width: auto;
        object-fit: contain;
        margin: 0 auto 6px;
        display: block;
    }
    .ts-auth-clean-head h2 {
        font-size: 18px;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
        line-height: 1.25;
    }
    .ts-auth-clean-head p {
        font-size: 12px;
        color: #64748B;
        margin: 3px 0 0;
    }
    .ts-auth-right {
        padding: 18px 24px 24px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        position: relative;
        background: #ffffff;
    }
    .ts-auth-close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid #E2E8F0;
        background: #F8FAFC;
        color: #475569;
        font-size: 19px;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.16s ease;
    }
    .ts-auth-close:hover {
        background: #FEE2E2;
        color: #DC2626;
        border-color: #FCA5A5;
        transform: rotate(90deg);
    }
    .ts-auth-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        background: #F1F5F9;
        padding: 5px;
        border-radius: 12px;
        margin-bottom: 14px;
    }
    .ts-auth-tab {
        padding: 10px 12px;
        border-radius: 9px;
        font-size: 13.5px;
        font-weight: 700;
        color: #475569;
        border: none;
        background: transparent;
        cursor: pointer;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .ts-auth-tab.active {
        background: #01684B;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(1, 104, 75, 0.28);
    }
    .ts-auth-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .ts-auth-section-box {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 14px 14px 6px;
        margin: 12px 0 14px;
    }
    .ts-auth-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        font-weight: 800;
        color: #01684B;
        margin-bottom: 10px;
    }
    .ts-auth-field {
        margin-bottom: 11px;
    }
    .ts-auth-field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 5px;
    }
    .ts-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .ts-input-icon {
        position: absolute;
        left: 12px;
        font-size: 14px;
        color: #64748B;
        pointer-events: none;
    }
    .ts-auth-field input,
    .ts-auth-field select {
        width: 100%;
        height: 43px;
        padding: 0 12px 0 36px;
        border: 1.5px solid #CBD5E1;
        border-radius: 10px;
        font-size: 13.5px;
        color: #0F172A;
        background: #FFFFFF;
        box-sizing: border-box;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }
    .ts-auth-field select {
        padding-left: 12px;
    }
    .ts-auth-field input:focus,
    .ts-auth-field select:focus {
        outline: none;
        border-color: #01684B;
        box-shadow: 0 0 0 3.5px rgba(1, 104, 75, 0.12);
    }
    .ts-auth-alert {
        padding: 10px 13px;
        border-radius: 10px;
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1.45;
        margin-bottom: 14px;
        display: none;
    }
    .ts-auth-alert.error {
        display: block;
        background: #FEF2F2;
        border: 1px solid #FCA5A5;
        color: #B91C1C;
    }
    .ts-auth-alert.info {
        display: block;
        background: #ECFDF5;
        border: 1px solid #A7F3D0;
        color: #065F46;
    }
    .ts-auth-submit {
        width: 100%;
        height: 46px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #01684B 0%, #059669 100%);
        color: #ffffff;
        font-size: 14.5px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 4px;
        box-shadow: 0 8px 20px rgba(1, 104, 75, 0.28);
        transition: transform 0.14s ease, box-shadow 0.14s ease;
    }
    .ts-auth-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(1, 104, 75, 0.35);
    }
    .ts-auth-submit:active {
        transform: translateY(0);
    }
    .ts-auth-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
    .ts-login-card-intro {
        text-align: center;
        padding: 8px 0 16px;
    }
    .ts-login-card-intro .ts-login-badge-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: #ECFDF5;
        border: 1.5px solid #A7F3D0;
        color: #01684B;
        font-size: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    /* ===== MOBILE BOTTOM-SHEET RESPONSIVE EXPERIENCE ===== */
    @media (max-width: 640px) {
        .ts-auth-backdrop {
            padding: 0;
            align-items: flex-end;
        }
        .ts-auth-dialog {
            max-width: 100%;
            border-radius: 22px 22px 0 0;
            max-height: 93vh;
            animation: tsSheetSlideUp 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes tsSheetSlideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        .ts-auth-clean-head {
            padding: 16px 46px 10px 18px;
        }
        .ts-auth-clean-head img {
            height: 36px;
        }
        .ts-auth-clean-head h2 {
            font-size: 16.5px;
        }
        .ts-auth-right {
            padding: 14px 16px 24px;
        }
        .ts-auth-grid-2 {
            grid-template-columns: 1fr;
            gap: 0;
        }
        .ts-auth-field input,
        .ts-auth-field select {
            height: 46px;
            font-size: 16px; /* Prevents iOS auto-zoom */
        }
        .ts-auth-submit {
            height: 48px;
            font-size: 15px;
        }
    }
</style>

<div class="ts-auth-backdrop" id="tsAuthModalBackdrop" role="dialog" aria-modal="true" aria-labelledby="tsAuthModalTitle">
    <div class="ts-auth-dialog">
        <button type="button" class="ts-auth-close" onclick="window.TouchstainsAuth.close()" title="Close">&times;</button>

        <!-- Clean White Top Header (No Green Panel) -->
        <div class="ts-auth-clean-head">
            <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
            <h2 id="tsAuthModalTitle">Welcome to TOUCHSTAINS™</h2>
            <p>Quick Registration &amp; Auto-Fill Delivery Address</p>
        </div>

        <!-- Single-Column Interactive Auth Form -->
        <div class="ts-auth-right">
            <div class="ts-auth-tabs">
                <button type="button" class="ts-auth-tab active" id="tsTabRegisterBtn" onclick="window.TouchstainsAuth.switchTab('register')">
                    <span>📝</span> <span>Registration</span>
                </button>
                <button type="button" class="ts-auth-tab" id="tsTabLoginBtn" onclick="window.TouchstainsAuth.switchTab('login')">
                    <span>🔑</span> <span>Login</span>
                </button>
            </div>

            <div id="tsAuthNotice" class="ts-auth-alert info" style="display:block">
                🔔 প্রোডাক্ট দেখতে এবং অর্ডার করতে অনুগ্রহ করে আগে আপনার কাস্টমার অ্যাকাউন্ট রেজিস্ট্রেশন করুন (অথবা আগে রেজিস্ট্রেশন করা থাকলে Login করুন)।
            </div>

            <div id="tsAuthError" class="ts-auth-alert error"></div>

            <!-- REGISTER FORM (Passwordless: Full Name*, Mobile Number*, Email Optional + Delivery Address) -->
            <form id="tsModalRegisterForm" onsubmit="window.TouchstainsAuth.submitRegister(event)">
                <div class="ts-auth-grid-2">
                    <div class="ts-auth-field">
                        <label>Full Name <span style="color:#DC2626">*</span></label>
                        <div class="ts-input-wrap">
                            <span class="ts-input-icon">👤</span>
                            <input type="text" id="tsRegName" name="full_name" placeholder="Enter your full name" required>
                        </div>
                    </div>
                    <div class="ts-auth-field">
                        <label>Mobile Number <span style="color:#DC2626">*</span></label>
                        <div class="ts-input-wrap">
                            <span class="ts-input-icon">📱</span>
                            <input type="tel" id="tsRegPhone" name="phone" placeholder="+91 9876543210" required>
                        </div>
                    </div>
                </div>

                <div class="ts-auth-field">
                    <label>Email Address <span style="color:#64748B;font-weight:500">(Optional)</span></label>
                    <div class="ts-input-wrap">
                        <span class="ts-input-icon">✉️</span>
                        <input type="email" id="tsRegEmail" name="email" placeholder="you@example.com (Optional)">
                    </div>
                </div>

                <div class="ts-auth-section-box">
                    <div class="ts-auth-section-title">
                        <span>🏠</span>
                        <span>Delivery Address (Auto-Filled at Checkout)</span>
                    </div>

                    <div class="ts-auth-grid-2">
                        <div class="ts-auth-field">
                            <label>Address Label</label>
                            <select id="tsRegAddrType" name="address_type">
                                <option value="Home">🏠 Home</option>
                                <option value="Workplace">🏢 Workplace / Shop</option>
                                <option value="Other">📍 Other</option>
                            </select>
                        </div>
                        <div class="ts-auth-field">
                            <label>Pin Code <span style="color:#DC2626">*</span></label>
                            <div class="ts-input-wrap">
                                <span class="ts-input-icon">📮</span>
                                <input type="text" id="tsRegPincode" name="pincode" placeholder="e.g. 400022" maxlength="6" required>
                            </div>
                        </div>
                    </div>

                    <div class="ts-auth-field">
                        <label>Full Delivery Address (Street / Area / City / State) <span style="color:#DC2626">*</span></label>
                        <div class="ts-input-wrap">
                            <span class="ts-input-icon">📍</span>
                            <input type="text" id="tsRegAddress" name="address" placeholder="Flat/Room No, Building, Road, City, State" required>
                        </div>
                    </div>

                    <div class="ts-auth-grid-2">
                        <div class="ts-auth-field">
                            <label>Landmark / Road</label>
                            <div class="ts-input-wrap">
                                <span class="ts-input-icon">🛣️</span>
                                <input type="text" id="tsRegRoad" name="road" placeholder="Near Station / Market">
                            </div>
                        </div>
                        <div class="ts-auth-field">
                            <label>House / Flat No.</label>
                            <div class="ts-input-wrap">
                                <span class="ts-input-icon">🚪</span>
                                <input type="text" id="tsRegHouse" name="house" placeholder="House #12">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="ts-auth-submit" id="tsRegSubmitBtn">
                    ✓ Complete Registration &amp; Continue Shopping →
                </button>

                <p style="text-align:center;font-size:13px;color:#475569;margin:13px 0 0">
                    Already registered?
                    <a href="javascript:void(0)" onclick="window.TouchstainsAuth.switchTab('login')" style="color:#01684B;font-weight:700;text-decoration:underline">Login Here →</a>
                </p>
            </form>

            <!-- LOGIN FORM (Passwordless: Mobile Number* + Optional Email) -->
            <form id="tsModalLoginForm" style="display:none" onsubmit="window.TouchstainsAuth.submitLogin(event)">
                <div class="ts-login-card-intro">
                    <div class="ts-login-badge-icon">🔑</div>
                    <h3 style="font-size:18px;font-weight:800;color:#0F172A;margin:0 0 4px">Welcome Back!</h3>
                    <p style="font-size:12.5px;color:#64748B;margin:0">Sign in quickly using your registered mobile number — no password needed.</p>
                </div>

                <div class="ts-auth-field">
                    <label>Registered Mobile Number <span style="color:#DC2626">*</span></label>
                    <div class="ts-input-wrap">
                        <span class="ts-input-icon">📱</span>
                        <input type="tel" id="tsLoginPhone" name="phone" placeholder="Enter your registered Mobile Number" required>
                    </div>
                </div>

                <div class="ts-auth-field">
                    <label>Email Address <span style="color:#64748B;font-weight:500">(Optional)</span></label>
                    <div class="ts-input-wrap">
                        <span class="ts-input-icon">✉️</span>
                        <input type="email" id="tsLoginEmail" name="email" placeholder="Enter your Email ID (Optional)">
                    </div>
                </div>

                <button type="submit" class="ts-auth-submit" id="tsLoginSubmitBtn" style="margin-top:8px">
                    🔑 Login to My Account →
                </button>

                <p style="text-align:center;font-size:13px;color:#475569;margin:16px 0 0">
                    New to TOUCHSTAINS?
                    <a href="javascript:void(0)" onclick="window.TouchstainsAuth.switchTab('register')" style="color:#01684B;font-weight:700;text-decoration:underline">Create New Account →</a>
                </p>
            </form>
        </div>
    </div>
</div>

@php
    $tsAuthServerUser = (!empty($isCustomerRegistered) && isset($registeredUser)) ? [
        'id' => $registeredUser->id,
        'name' => $registeredUser->name,
        'f_name' => $registeredUser->f_name,
        'l_name' => $registeredUser->l_name,
        'email' => $registeredUser->email,
        'phone' => $registeredUser->phone,
    ] : null;
    $tsAuthServerAddr = (!empty($isCustomerRegistered) && isset($registeredAddress)) ? $registeredAddress : null;
@endphp
<script>
(function () {
    const SERVER_AUTH = {{ !empty($isCustomerRegistered) ? 'true' : 'false' }};
    const SERVER_USER = @json($tsAuthServerUser);
    const SERVER_ADDR = @json($tsAuthServerAddr);

    const justLoggedOut = window.location.search.includes('logged_out=1');
    if (justLoggedOut) {
        localStorage.removeItem('touchstains_registered_customer');
        localStorage.removeItem('getgozo_user');
        sessionStorage.removeItem('ts_welcome_reg_shown');
        try {
            const cleanUrl = window.location.pathname + window.location.hash;
            window.history.replaceState({}, document.title, cleanUrl);
        } catch (e) {}
    }

    function readLocalRegisteredCustomer() {
        try {
            const raw = localStorage.getItem('touchstains_registered_customer');
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            return (parsed && parsed.registered && parsed.phone) ? parsed : null;
        } catch (e) {
            return null;
        }
    }

    if (SERVER_AUTH && SERVER_USER) {
        const synced = {
            registered: true,
            f_name: SERVER_USER.f_name || '',
            l_name: SERVER_USER.l_name || '',
            name: SERVER_USER.name || '',
            phone: SERVER_USER.phone || '',
            email: SERVER_USER.email || '',
            address_type: (SERVER_ADDR && SERVER_ADDR.address_type) || 'Home',
            contact_person_name: (SERVER_ADDR && SERVER_ADDR.contact_person_name) || SERVER_USER.name || '',
            contact_person_number: (SERVER_ADDR && SERVER_ADDR.contact_person_number) || SERVER_USER.phone || '',
            address: (SERVER_ADDR && SERVER_ADDR.address) || '',
            pincode: (SERVER_ADDR && SERVER_ADDR.floor) || '',
            road: (SERVER_ADDR && SERVER_ADDR.road) || '',
            house: (SERVER_ADDR && SERVER_ADDR.house) || ''
        };
        localStorage.setItem('touchstains_registered_customer', JSON.stringify(synced));
        localStorage.setItem('getgozo_user', JSON.stringify({
            name: SERVER_USER.name,
            email: SERVER_USER.email,
            phone: SERVER_USER.phone
        }));
    }

    const localSavedCustomer = !justLoggedOut ? readLocalRegisteredCustomer() : null;
    let pendingCallback = null;
    let pendingUrl = null;
    // Stay permanently logged in once registered/logged in until explicit Logout!
    let isLoggedIn = Boolean(SERVER_AUTH || localSavedCustomer);

    function checkLoggedIn() {
        return isLoggedIn;
    }

    function toggleHeaderDropdown(ev) {
        if (ev) {
            ev.preventDefault();
            ev.stopPropagation();
        }
        const wrap = document.getElementById('tsAccountMenuWrap');
        if (wrap) wrap.classList.toggle('open');
    }

    function closeHeaderDropdown() {
        const wrap = document.getElementById('tsAccountMenuWrap');
        if (wrap) wrap.classList.remove('open');
    }

    function showError(msg) {
        const el = document.getElementById('tsAuthError');
        if (!el) return;
        if (msg) {
            el.textContent = '⚠️ ' + msg;
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }

    function switchTab(tab) {
        showError('');
        const regForm = document.getElementById('tsModalRegisterForm');
        const loginForm = document.getElementById('tsModalLoginForm');
        const regBtn = document.getElementById('tsTabRegisterBtn');
        const loginBtn = document.getElementById('tsTabLoginBtn');
        if (tab === 'login') {
            if (regForm) regForm.style.display = 'none';
            if (loginForm) loginForm.style.display = 'block';
            regBtn?.classList.remove('active');
            loginBtn?.classList.add('active');
        } else {
            if (regForm) regForm.style.display = 'block';
            if (loginForm) loginForm.style.display = 'none';
            regBtn?.classList.add('active');
            loginBtn?.classList.remove('active');
        }
    }

    function open(tab, onSuccessCb, targetUrl, customNotice) {
        closeHeaderDropdown();
        pendingCallback = typeof onSuccessCb === 'function' ? onSuccessCb : null;
        pendingUrl = targetUrl || null;
        switchTab(tab || 'register');
        const noticeEl = document.getElementById('tsAuthNotice');
        if (noticeEl) {
            if (customNotice) {
                noticeEl.textContent = customNotice;
                noticeEl.style.display = 'block';
            } else {
                noticeEl.style.display = 'none';
            }
        }
        const backdrop = document.getElementById('tsAuthModalBackdrop');
        if (backdrop) {
            backdrop.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    }

    function close() {
        const backdrop = document.getElementById('tsAuthModalBackdrop');
        if (backdrop) {
            backdrop.classList.remove('open');
            document.body.style.overflow = '';
        }
    }

    function updateHeaderDropdownToLoggedIn(u) {
        const trigger = document.getElementById('header-auth-pill');
        if (trigger) {
            trigger.classList.add('is-logged-in');
            if (!trigger.querySelector('.ts-account-dot')) {
                const dot = document.createElement('span');
                dot.className = 'ts-account-dot';
                trigger.appendChild(dot);
            }
        }
        const dd = document.getElementById('tsAccountDropdown');
        if (dd) {
            dd.innerHTML = `
                <div class="ts-dd-head">
                    <strong>${u.name || 'Customer'}</strong>
                    <span>${u.phone || ''}</span>
                </div>
                <a href="{{ url('/account') }}" class="ts-dd-item">
                    <span class="ts-dd-item-icon">👤</span>
                    <span class="ts-dd-item-text"><strong>My Profile</strong><small>View &amp; edit details</small></span>
                </a>
                <a href="{{ url('/orders') }}" class="ts-dd-item">
                    <span class="ts-dd-item-icon">📦</span>
                    <span class="ts-dd-item-text"><strong>My Orders</strong><small>Track &amp; manage orders</small></span>
                </a>
                <a href="{{ url('/address') }}" class="ts-dd-item">
                    <span class="ts-dd-item-icon">📍</span>
                    <span class="ts-dd-item-text"><strong>Saved Addresses</strong><small>Delivery address book</small></span>
                </a>
                <a href="{{ url('/logout') }}" class="ts-dd-item ts-dd-item-danger">
                    <span class="ts-dd-item-icon">🚪</span>
                    <span class="ts-dd-item-text"><strong>Logout</strong><small>Sign out of account</small></span>
                </a>
            `;
        }
    }

    function onAuthComplete(data) {
        isLoggedIn = true;
        window.IS_CUSTOMER_LOGGED_IN = true;
        const u = data.user || {};
        const a = data.address || {};
        const saved = {
            registered: true,
            f_name: u.f_name || '',
            l_name: u.l_name || '',
            name: u.name || '',
            phone: u.phone || '',
            email: u.email || '',
            address_type: a.address_type || 'Home',
            contact_person_name: a.contact_person_name || u.name || '',
            contact_person_number: a.contact_person_number || u.phone || '',
            address: a.address || '',
            pincode: a.floor || '',
            road: a.road || '',
            house: a.house || ''
        };
        localStorage.setItem('touchstains_registered_customer', JSON.stringify(saved));
        localStorage.setItem('getgozo_user', JSON.stringify({ name: u.name, email: u.email, phone: u.phone }));

        updateHeaderDropdownToLoggedIn(u);
        close();

        if (window.GetgozoApp) {
            GetgozoApp.showToast('✅ ' + (data.message || 'Welcome! Your delivery address is saved.'), 'success');
        }

        const cb = pendingCallback;
        const url = pendingUrl;
        pendingCallback = null;
        pendingUrl = null;

        if (cb) {
            cb(saved);
        } else if (url) {
            window.location.href = url;
        } else if (window.location.pathname.includes('/checkout')) {
            window.location.reload();
        }
    }

    async function submitRegister(e) {
        e.preventDefault();
        showError('');
        const btn = document.getElementById('tsRegSubmitBtn');
        const orig = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '⏳ Creating your account...'; }

        const payload = {
            full_name: document.getElementById('tsRegName')?.value.trim() || '',
            phone: document.getElementById('tsRegPhone')?.value.trim() || '',
            email: document.getElementById('tsRegEmail')?.value.trim() || '',
            address_type: document.getElementById('tsRegAddrType')?.value || 'Home',
            pincode: document.getElementById('tsRegPincode')?.value.trim() || '',
            address: document.getElementById('tsRegAddress')?.value.trim() || '',
            road: document.getElementById('tsRegRoad')?.value.trim() || '',
            house: document.getElementById('tsRegHouse')?.value.trim() || '',
        };

        try {
            const res = await fetch("{{ url('/register') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && data && data.status) {
                onAuthComplete(data);
            } else {
                showError(data.message || 'Registration failed. Please check all required fields.');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = orig; }
        }
    }

    async function submitLogin(e) {
        e.preventDefault();
        showError('');
        const btn = document.getElementById('tsLoginSubmitBtn');
        const orig = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '⏳ Logging in...'; }

        const payload = {
            phone: document.getElementById('tsLoginPhone')?.value.trim() || '',
            email: document.getElementById('tsLoginEmail')?.value.trim() || '',
        };

        try {
            const res = await fetch("{{ url('/login') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && data && data.status) {
                onAuthComplete(data);
            } else {
                showError(data.message || 'No account found. Please register first.');
            }
        } catch (err) {
            showError('Network error. Please try again.');
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = orig; }
        }
    }

    window.IS_CUSTOMER_LOGGED_IN = isLoggedIn;
    window.TouchstainsAuth = {
        isLoggedIn: checkLoggedIn,
        toggleHeaderDropdown,
        closeHeaderDropdown,
        open,
        close,
        switchTab,
        submitRegister,
        submitLogin
    };

    // Clear local persistent login cleanly whenever customer clicks any Logout link
    document.addEventListener('click', function (e) {
        const logoutLink = e.target.closest('a[href*="/logout"]');
        if (logoutLink) {
            isLoggedIn = false;
            window.IS_CUSTOMER_LOGGED_IN = false;
            localStorage.removeItem('touchstains_registered_customer');
            localStorage.removeItem('getgozo_user');
            sessionStorage.removeItem('ts_welcome_reg_shown');
        }
    }, true);

    // If user already registered on this device (localStorage) and server session expired,
    // keep them logged in seamlessly & silently restore server session with ZERO popups!
    if (!SERVER_AUTH && localSavedCustomer) {
        document.addEventListener('DOMContentLoaded', function () {
            updateHeaderDropdownToLoggedIn(localSavedCustomer);
            fetch("{{ url('/login') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    phone: localSavedCustomer.phone || '',
                    email: localSavedCustomer.email || ''
                })
            }).catch(function () {});
        });
    }

    // Close header dropdown on outside click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#tsAccountMenuWrap')) {
            closeHeaderDropdown();
        }
    });

    // Close modal on backdrop click or Escape key
    document.addEventListener('click', function (e) {
        const backdrop = document.getElementById('tsAuthModalBackdrop');
        if (backdrop && e.target === backdrop) {
            close();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            close();
            closeHeaderDropdown();
        }
    });

    // Intercept product clicks, Add to Bag, Buy Now, Wishlist, Checkout if NOT registered/logged in
    document.addEventListener('click', function (e) {
        if (checkLoggedIn()) return;
        if (e.target.closest('#tsAuthModalBackdrop') || e.target.closest('#tsAccountMenuWrap')) return;
        const path = window.location.pathname || '';
        if (path.startsWith('/login') || path.startsWith('/register') || path.startsWith('/admin')) return;

        const anchor = e.target.closest('a');
        const href = anchor ? (anchor.getAttribute('href') || '') : '';
        const isProductOrCheckoutLink = href.includes('/product/') || href.includes('/product-details') || href.includes('/checkout');
        const isProductCardClick = Boolean(e.target.closest('.gf-vcard, .gf-hcard, .product-card'));

        if (isProductOrCheckoutLink || isProductCardClick) {
            e.preventDefault();
            e.stopPropagation();
            if (window.GetgozoApp) {
                GetgozoApp.showToast('🔔 প্রোডাক্ট দেখতে বা অর্ডার করতে অনুগ্রহ করে আগে রেজিস্ট্রেশন অথবা লগইন করুন!', 'info');
            }
            const targetUrl = isProductOrCheckoutLink ? anchor.href : (e.target.closest('.gf-vcard, .gf-hcard, .product-card')?.querySelector('a')?.href || null);
            open('register', null, targetUrl, '🔔 প্রোডাক্ট দেখতে এবং অর্ডার করতে আগে আপনার অ্যাকাউন্ট রেজিস্ট্রেশন করুন! আগে রেজিস্ট্রেশন করা থাকলে Login ট্যাবে ক্লিক করুন।');
        }
    }, true);

    // Do NOT auto-open popup when website loads; only open when a guest clicks a product/purchase action or header icon!
})();
</script>
