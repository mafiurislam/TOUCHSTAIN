@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="contact_us">Contact Us &amp; Store Location</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;margin-bottom:32px">
        <!-- Store Info Card -->
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px">
            <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="height:56px;margin-bottom:14px;object-fit:contain" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
            <h1 style="font-size:22px;font-weight:800;margin-bottom:10px" data-i18n="contact_us">Get in Touch with TOUCHSTAINS</h1>
            <p style="font-size:13px;color:var(--text-secondary);margin-bottom:24px">
                Have questions about TOUCHSTAINS Fabric Stain &amp; Rust Remover products, bulk orders, or online payments? Reach out to our team directly!
            </p>

            <div style="display:flex;flex-direction:column;gap:18px">
                <div style="display:flex;gap:14px;align-items:flex-start">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <img src="{{ asset('assets/image/location.png') }}" alt="" style="width:22px;height:22px">
                    </div>
                    <div>
                        <h4 style="font-size:14px;font-weight:700">Official Head Office Address</h4>
                        <p style="font-size:13px;color:var(--text-secondary)">{{ $settings['ecommerce_address'] ?? 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022' }}</p>
                    </div>
                </div>

                <div style="display:flex;gap:14px;align-items:flex-start">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <img src="{{ asset('assets/image/call.png') }}" alt="" style="width:22px;height:22px">
                    </div>
                    <div>
                        <h4 style="font-size:14px;font-weight:700">Customer Helpline &amp; WhatsApp</h4>
                        <p style="font-size:13px;color:var(--text-secondary)">
                            <a href="tel:+917679416294" style="color:var(--primary);font-weight:700">+91 7679416294</a> / 
                            <a href="tel:+917039214465" style="color:var(--primary);font-weight:700">7039214465</a>
                            (8:00 AM – 10:00 PM Daily)
                        </p>
                    </div>
                </div>

                <div style="display:flex;gap:14px;align-items:flex-start">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <img src="{{ asset('assets/image/mail.png') }}" alt="" style="width:22px;height:22px">
                    </div>
                    <div>
                        <h4 style="font-size:14px;font-weight:700">Email Support</h4>
                        <p style="font-size:13px;color:var(--text-secondary)"><a href="mailto:touchstains@gmail.com" style="color:var(--primary);font-weight:700">touchstains@gmail.com</a></p>
                    </div>
                </div>
            </div>

            <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap">
                <a href="{{ url('/chat') }}" class="btn-primary">💬 Start Live Chat</a>
                <a href="https://wa.me/917679416294?text=Hello%20TOUCHSTAINS!" target="_blank" rel="noopener" class="btn-outline">📱 WhatsApp Us</a>
            </div>
        </div>

        <!-- Send Inquiry Form (Saves to SQLite Messages) -->
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px">
            <h2 style="font-size:18px;font-weight:700;margin-bottom:16px">Send Us a Message</h2>
            <form action="{{ url('/chat/send') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" value="{{ $currentUser->f_name }} {{ $currentUser->l_name }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" value="{{ $currentUser->phone }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Message / Inquiry</label>
                    <textarea name="message" rows="4" class="form-control" placeholder="Write your message or bulk grocery requirement here..." required></textarea>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center">Send Message to Getgozo Team</button>
            </form>
        </div>
    </div>
</main>
@endsection

