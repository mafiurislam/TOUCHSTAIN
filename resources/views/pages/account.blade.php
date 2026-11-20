@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="profile">My Profile</span>
    </div>

    <div class="account-layout">
        @include('partials.account-sidebar', ['activeTab' => 'account'])

        <div>
            <!-- Top Stats Cards -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-bottom:24px">
                <a href="{{ url('/orders') }}" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:18px;display:flex;align-items:center;gap:14px">
                    <img src="{{ asset('assets/image/my_order.png') }}" alt="Orders" style="width:40px;height:40px">
                    <div>
                        <div style="font-size:22px;font-weight:800;color:var(--primary)">{{ $ordersCount ?? 1 }}</div>
                        <div style="font-size:12px;color:var(--text-secondary)">Total Orders</div>
                    </div>
                </a>
                <a href="{{ url('/wishlist') }}" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:18px;display:flex;align-items:center;gap:14px">
                    <img src="{{ asset('assets/image/heart_outline.png') }}" alt="Wishlist" style="width:40px;height:40px">
                    <div>
                        <div style="font-size:22px;font-weight:800;color:var(--primary)">{{ count($wishlistIds ?? []) }}</div>
                        <div style="font-size:12px;color:var(--text-secondary)">Favourite Items</div>
                    </div>
                </a>
            </div>

            <!-- Update Profile Form (Saves to SQLite) -->
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:24px">
                <h2 style="font-size:18px;font-weight:700;margin-bottom:18px" data-i18n="profile">Personal Information</h2>
                <form action="{{ url('/account/update') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label data-i18n="first_name">First Name</label>
                            <input type="text" name="f_name" value="{{ $currentUser->f_name }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="last_name">Last Name</label>
                            <input type="text" name="l_name" value="{{ $currentUser->l_name }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label data-i18n="email">Email Address</label>
                            <input type="email" name="email" value="{{ $currentUser->email }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="phone">Phone Number</label>
                            <input type="text" name="phone" value="{{ $currentUser->phone }}" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" data-i18n="update_profile">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

