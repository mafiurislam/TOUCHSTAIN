@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="address">Saved Addresses</span>
    </div>

    <div class="account-layout">
        @include('partials.account-sidebar', ['activeTab' => 'address'])

        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px">
                <h1 style="font-size:20px;font-weight:700" data-i18n="address">Saved Delivery Addresses ({{ $addresses->count() }})</h1>
            </div>

            <!-- Existing SQLite Addresses -->
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:28px">
                @foreach($addresses as $addr)
                    <div style="background:var(--bg-card);border:1.5px solid {{ $addr->is_default ? 'var(--primary)' : 'var(--border)' }};border-radius:var(--radius-md);padding:18px;position:relative">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                            <span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700;background:var(--primary-light);color:var(--primary)">
                                📍 {{ $addr->address_type }} {{ $addr->is_default ? '• Default' : '' }}
                            </span>
                            <form action="{{ url('/address/' . $addr->id . '/delete') }}" method="POST">
                                @csrf
                                <button type="submit" style="font-size:12px;color:var(--danger);font-weight:600">Delete</button>
                            </form>
                        </div>
                        <h4 style="font-size:15px;font-weight:700;margin-bottom:4px">{{ $addr->contact_person_name }}</h4>
                        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:4px">{{ $addr->address }}</p>
                        @if($addr->road || $addr->house)
                            <p style="font-size:12px;color:var(--text-muted)">Road: {{ $addr->road ?? '-' }}, House: {{ $addr->house ?? '-' }}, Floor: {{ $addr->floor ?? '-' }}</p>
                        @endif
                        <p style="font-size:12px;font-weight:600;margin-top:6px">📞 {{ $addr->contact_person_number }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Add New Address to SQLite Form -->
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:24px">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:16px">+ Add New Delivery Address</h3>
                <form action="{{ url('/address') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label>Address Label</label>
                            <select name="address_type" class="form-control">
                                <option value="Home">🏠 Home</option>
                                <option value="Workplace">🏢 Workplace / Shop</option>
                                <option value="Other">📍 Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Contact Person Name</label>
                            <input type="text" name="contact_person_name" value="{{ !empty($isCustomerRegistered) ? trim($currentUser->f_name . ' ' . $currentUser->l_name) : '' }}" placeholder="Enter contact person name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="contact_person_number" value="{{ !empty($isCustomerRegistered) ? $currentUser->phone : '' }}" placeholder="e.g. +91 9876543210" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Street / City / Pin Code (Full Delivery Address)</label>
                            <input type="text" name="address" placeholder="e.g. Flat/Room No, Building Name, Road, City, State - PIN" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Landmark / Road</label>
                            <input type="text" name="road" placeholder="Near NH-34 / Mosque / Market" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>House / Flat No.</label>
                            <input type="text" name="house" placeholder="House #12" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Save Address to Account</button>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

