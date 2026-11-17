@php
    $isVendor = $viewer === 'vendor';
    $partner = $isVendor ? $message->name : ($message->store?->name ?: __('Supplier'));
    $items = collect([$message])->merge($message->replies)->sortBy('id');
    $routePrefix = $isVendor ? 'marketplace.vendor.messages.' : 'customer.messages.';
@endphp
<link rel="stylesheet" href="{{ asset('css/skymarket-messenger.css') }}?v={{ filemtime(public_path('css/skymarket-messenger.css')) }}">
<section class="sm-chat" data-sm-chat data-stream="{{ route($routePrefix . 'stream', $message) }}" data-viewer="{{ $viewer }}" data-partner="{{ $partner }}" data-you="{{ __('You') }}" data-last-id="{{ $items->max('id') }}">
    <header class="sm-chat__header">
        <a href="{{ route($routePrefix . 'index') }}" class="sm-chat__back" aria-label="{{ __('Back to messages') }}">←</a>
        <span class="sm-avatar" aria-hidden="true">{{ mb_substr($partner, 0, 1) }}</span>
        <div><h2>{{ $partner }}</h2><span class="sm-chat__status" data-chat-status role="status">{{ __('Connecting…') }}</span></div>
        @if ($message->store)<a class="sm-text-link ms-auto" href="{{ $message->store->url }}">{{ __('View supplier') }} ↗</a>@endif
    </header>
    <div class="sm-chat__notice">{{ __('Keep product details, agreed prices and delivery arrangements in this conversation. Never share passwords or payment PINs.') }}</div>
    <div class="sm-chat__messages" data-chat-messages role="log" aria-label="{{ __('Conversation') }}" aria-live="polite" aria-relevant="additions">
        @foreach ($items as $item)
            <article class="sm-message {{ $item->sender_type === $viewer ? 'sm-message--own' : '' }}" data-message-id="{{ $item->id }}" data-sender="{{ $item->sender_type }}">
                <div class="sm-message__bubble"><strong>{{ $item->sender_type === $viewer ? __('You') : $partner }}</strong><p>{{ $item->content }}</p><footer><time datetime="{{ $item->created_at->toIso8601String() }}">{{ BaseHelper::formatDateTime($item->created_at) }}</time>@if ($item->sender_type === $viewer)<span data-read-state>{{ $item->read_at ? __('Read') : __('Sent') }}</span>@endif</footer></div>
            </article>
        @endforeach
    </div>
    @if (!$isVendor || $message->customer_id)
        <form class="sm-chat__composer" method="POST" action="{{ route($routePrefix . 'reply', $message) }}" data-chat-form>
            @csrf
            <label class="visually-hidden" for="sm-chat-content">{{ __('Your message') }}</label>
            <textarea id="sm-chat-content" name="content" rows="2" maxlength="2000" placeholder="{{ __('Ask about pricing, quantities or delivery…') }}" required>{{ old('content') }}</textarea>
            <button class="sm-button" type="submit">{{ __('Send message') }} ↗</button>
            <p class="sm-chat__error" data-chat-error role="alert"></p>
            <span class="sm-chat__hint">{{ __('Enter to send · Shift + Enter for a new line') }}</span>
        </form>
    @else
        <p class="sm-chat__notice">{{ __('This guest enquiry can be answered by email. Registered buyers can receive live replies here.') }} <a href="mailto:{{ $message->email }}">{{ $message->email }}</a></p>
    @endif
</section>
<script src="{{ asset('js/skymarket-messenger.js') }}?v={{ filemtime(public_path('js/skymarket-messenger.js')) }}" defer></script>
