@extends('layouts.app')

@section('content')
<main class="container">
    <div class="breadcrumb">
        <a href="{{ url('/') }}">Home</a> <span>›</span>
        <span data-i18n="live_chat">Customer Support Live Chat</span>
    </div>

    <div class="account-layout">
        @include('partials.account-sidebar', ['activeTab' => 'chat'])

        <div class="chat-window">
            <div class="chat-header">
                <img src="{{ asset('storage/restaurant/touchstains-logo.png') }}?v=2.3" alt="TOUCHSTAINS" style="width:46px;height:46px;object-fit:contain;background:#fff;border-radius:10px;padding:3px" onerror="this.src='{{ asset('assets/image/touchstains-logo.png') }}'">
                <div>
                    <h3 style="font-size:16px;font-weight:700">TOUCHSTAINS Support Desk</h3>
                    <p style="font-size:12px;opacity:0.9">● Online • Helpline: +91 7679416294 / 7039214465 • touchstains@gmail.com</p>
                </div>
            </div>

            <div class="chat-messages" id="chat-messages-box">
                @foreach($messages as $m)
                    <div class="chat-bubble {{ $m->sender === 'customer' ? 'outgoing' : 'incoming' }}">
                        <div>{{ $m->message }}</div>
                        <div class="time">{{ $m->created_at ? $m->created_at->format('h:i A') : 'Just now' }}</div>
                    </div>
                @endforeach
            </div>

            <form action="{{ url('/chat/send') }}" method="POST" class="chat-input-bar">
                @csrf
                <input type="text" name="message" class="form-control" placeholder="Type your message to TOUCHSTAINS support..." required autocomplete="off">
                <button type="submit" class="btn-primary">
                    <img src="{{ asset('assets/image/send.png') }}" alt="Send" style="width:18px;height:18px;filter:brightness(0) invert(1)"> Send
                </button>
            </form>
        </div>
    </div>
</main>
@endsection

