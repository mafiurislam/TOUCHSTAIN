<aside class="account-nav-card" style="background:var(--bg-card,#fff);border:1px solid var(--border,#E2E8F0);border-radius:12px;padding:12px;align-self:start;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="display:flex;align-items:center;gap:12px;padding:12px;border-bottom:1px solid var(--border,#E2E8F0);margin-bottom:8px">
        <img src="{{ asset('assets/image/profile.png') }}" alt="Avatar" style="width:48px;height:48px;border-radius:50%;background:var(--primary-light,#E6F4EA);padding:4px;flex-shrink:0;">
        <div style="overflow:hidden;text-overflow:ellipsis;">
            <h4 style="font-size:15px;font-weight:700;margin:0 0 2px;color:var(--text-main,#0F172A);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $currentUser->f_name }} {{ $currentUser->l_name }}</h4>
            <p style="font-size:12px;color:var(--text-secondary,#64748B);margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $currentUser->phone }}</p>
        </div>
    </div>

    <nav style="display:flex;flex-direction:column;gap:4px;">
        <a href="{{ url('/account') }}" class="account-nav-item {{ ($activeTab ?? '') === 'account' ? 'active' : '' }}" style="{{ ($activeTab ?? '') === 'account' ? 'background:#01684B;color:#FFFFFF;' : '' }}">
            <svg class="account-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;max-width:20px;max-height:20px;flex-shrink:0;vertical-align:middle;display:inline-block;{{ ($activeTab ?? '') === 'account' ? 'stroke:#FFFFFF;' : 'stroke:#01684B;' }}">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span data-i18n="profile">My Profile</span>
        </a>

        <a href="{{ url('/orders') }}" class="account-nav-item {{ ($activeTab ?? '') === 'orders' ? 'active' : '' }}" style="{{ ($activeTab ?? '') === 'orders' ? 'background:#01684B;color:#FFFFFF;' : '' }}">
            <svg class="account-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;max-width:20px;max-height:20px;flex-shrink:0;vertical-align:middle;display:inline-block;{{ ($activeTab ?? '') === 'orders' ? 'stroke:#FFFFFF;' : 'stroke:#01684B;' }}">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            <span data-i18n="my_order">My Orders</span>
        </a>

        <a href="{{ url('/track-order') }}" class="account-nav-item {{ ($activeTab ?? '') === 'track-order' ? 'active' : '' }}" style="{{ ($activeTab ?? '') === 'track-order' ? 'background:#01684B;color:#FFFFFF;' : '' }}">
            <svg class="account-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;max-width:20px;max-height:20px;flex-shrink:0;vertical-align:middle;display:inline-block;{{ ($activeTab ?? '') === 'track-order' ? 'stroke:#FFFFFF;' : 'stroke:#01684B;' }}">
                <rect x="1" y="3" width="15" height="13" rx="1"/>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            <span data-i18n="track_order">Track Order</span>
        </a>

        <a href="{{ url('/wishlist') }}" class="account-nav-item {{ ($activeTab ?? '') === 'wishlist' ? 'active' : '' }}" style="{{ ($activeTab ?? '') === 'wishlist' ? 'background:#01684B;color:#FFFFFF;' : '' }}">
            <svg class="account-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;max-width:20px;max-height:20px;flex-shrink:0;vertical-align:middle;display:inline-block;{{ ($activeTab ?? '') === 'wishlist' ? 'stroke:#FFFFFF;' : 'stroke:#01684B;' }}">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            <span data-i18n="favourite_list">Favourite List</span>
        </a>

        <a href="{{ url('/address') }}" class="account-nav-item {{ ($activeTab ?? '') === 'address' ? 'active' : '' }}" style="{{ ($activeTab ?? '') === 'address' ? 'background:#01684B;color:#FFFFFF;' : '' }}">
            <svg class="account-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;max-width:20px;max-height:20px;flex-shrink:0;vertical-align:middle;display:inline-block;{{ ($activeTab ?? '') === 'address' ? 'stroke:#FFFFFF;' : 'stroke:#01684B;' }}">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
            </svg>
            <span data-i18n="address">Saved Addresses</span>
        </a>

        <a href="{{ url('/notifications') }}" class="account-nav-item {{ ($activeTab ?? '') === 'notifications' ? 'active' : '' }}" style="{{ ($activeTab ?? '') === 'notifications' ? 'background:#01684B;color:#FFFFFF;' : '' }}">
            <svg class="account-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;max-width:20px;max-height:20px;flex-shrink:0;vertical-align:middle;display:inline-block;{{ ($activeTab ?? '') === 'notifications' ? 'stroke:#FFFFFF;' : 'stroke:#01684B;' }}">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span data-i18n="notification">Notifications</span>
        </a>

        <a href="{{ url('/chat') }}" class="account-nav-item {{ ($activeTab ?? '') === 'chat' ? 'active' : '' }}" style="{{ ($activeTab ?? '') === 'chat' ? 'background:#01684B;color:#FFFFFF;' : '' }}">
            <svg class="account-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;max-width:20px;max-height:20px;flex-shrink:0;vertical-align:middle;display:inline-block;{{ ($activeTab ?? '') === 'chat' ? 'stroke:#FFFFFF;' : 'stroke:#01684B;' }}">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
            <span data-i18n="live_chat">Live Support Chat</span>
        </a>

        <a href="{{ url('/settings') }}" class="account-nav-item {{ ($activeTab ?? '') === 'settings' ? 'active' : '' }}" style="{{ ($activeTab ?? '') === 'settings' ? 'background:#01684B;color:#FFFFFF;' : '' }}">
            <svg class="account-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;max-width:20px;max-height:20px;flex-shrink:0;vertical-align:middle;display:inline-block;{{ ($activeTab ?? '') === 'settings' ? 'stroke:#FFFFFF;' : 'stroke:#01684B;' }}">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span data-i18n="settings">Settings</span>
        </a>

        <a href="{{ url('/logout') }}" class="account-nav-item" style="color:#DC2626;border-top:1px solid var(--border,#E2E8F0);margin-top:6px;padding-top:12px;">
            <svg class="account-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;min-width:20px;max-width:20px;max-height:20px;flex-shrink:0;vertical-align:middle;display:inline-block;stroke:#DC2626;">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span data-i18n="log_out" style="color:#DC2626;font-weight:600;">Log Out</span>
        </a>
    </nav>
</aside>
