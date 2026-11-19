@php
    $store = $product->store;
    $phone = $store->phone ?: $store->customer?->phone;
    $email = $store->email ?: $store->customer?->email;
    $isCustomerLoggedIn = auth('customer')->check();
    $messagingEnabled = is_plugin_active('marketplace') && MarketplaceHelper::isEnabledMessagingSystem();
    $phoneDigits = preg_replace('/\D+/', '', (string) $phone);

    if (strlen($phoneDigits) === 12 && str_starts_with($phoneDigits, '254')) {
        $phoneDigits = '0' . substr($phoneDigits, 3);
    } elseif (strlen($phoneDigits) === 9 && in_array($phoneDigits[0] ?? null, ['1', '7'], true)) {
        $phoneDigits = '0' . $phoneDigits;
    }

    $displayPhone = ! $isCustomerLoggedIn && strlen($phoneDigits) >= 7
        ? substr($phoneDigits, 0, 4) . '***' . substr($phoneDigits, -3)
        : $phone;
@endphp

<style>
    #product-vendor-card { background:#fff; border:1px solid #eadfd6; border-radius:8px; box-shadow:0 12px 30px rgba(35,39,43,.11); display:block!important; margin-bottom:18px; opacity:1!important; overflow:hidden; visibility:visible!important; width:100% }
    #product-vendor-card .vendor-card__header { align-items:center; background:#fff7ef; border-bottom:1px solid #f0dfd1; display:flex; gap:12px; padding:16px }
    #product-vendor-card .vendor-card__logo { background:#fff; border:1px solid #e8ddd4; border-radius:6px; flex:0 0 52px; height:52px; object-fit:contain; padding:5px; width:52px }
    #product-vendor-card .vendor-card__identity { min-width:0 }
    #product-vendor-card .vendor-card__label { color:#777; font-size:11px; font-weight:700; margin-bottom:2px; text-transform:uppercase }
    #product-vendor-card .vendor-card__name { color:#202428; font-size:17px; font-weight:800; line-height:1.25; margin:0; overflow-wrap:anywhere }
    #product-vendor-card .vendor-card__contact { padding:14px 16px 5px }
    #product-vendor-card .vendor-card__line { align-items:flex-start; color:#555b60; display:flex; font-size:13px; gap:9px; line-height:1.4; margin-bottom:9px; overflow-wrap:anywhere }
    #product-vendor-card .vendor-card__line svg { color:#f68b1e; fill:currentColor; flex:0 0 17px; height:17px; margin-top:1px; width:17px }
    #product-vendor-card .vendor-card__line a { color:#202428; font-weight:800 }
    #product-vendor-card .vendor-card__benefits { border-top:1px solid #eee5de; margin:2px 16px 0; padding:7px 0 }
    #product-vendor-card .vendor-card__benefit { align-items:center; display:flex; gap:11px; padding:9px 0 }
    #product-vendor-card .vendor-card__benefit + .vendor-card__benefit { border-top:1px solid #f1ece8 }
    #product-vendor-card .vendor-card__benefit img { height:30px; object-fit:contain; width:30px }
    #product-vendor-card .vendor-card__benefit strong { color:#25292d; display:block; font-size:13px; line-height:1.25 }
    #product-vendor-card .vendor-card__benefit span { color:#74797d; display:block; font-size:11px; line-height:1.35 }
    #product-vendor-card .vendor-card__actions { display:grid; gap:8px; grid-template-columns:1fr 1fr; padding:8px 16px 16px }
    #product-vendor-card .vendor-card__button { align-items:center; border:1px solid #f68b1e; border-radius:6px; color:#d76d09; display:flex; font-size:12px; font-weight:800; justify-content:center; min-height:38px; padding:7px 8px; text-align:center }
    #product-vendor-card .vendor-card__button--primary { background:#f68b1e; color:#fff }
    #product-vendor-card .vendor-card__button:hover { background:#df7610; border-color:#df7610; color:#fff }
    #product-vendor-card .vendor-card__chat-form { grid-column:1/-1; margin:0 }
    #product-vendor-card .vendor-card__button--chat { background:#263238; border-color:#263238; color:#fff; gap:8px; grid-column:1/-1; width:100% }
    #product-vendor-card .vendor-card__button--chat svg { height:19px; width:19px }
    #product-vendor-card .vendor-card__button--chat:hover { background:#11191d; border-color:#11191d; color:#fff }
</style>

<aside id="product-vendor-card" aria-label="{{ __('Vendor information') }}">
    <div class="vendor-card__header">
        <img class="vendor-card__logo" src="{{ $store->logo_url }}" alt="{{ $store->name }}">
        <div class="vendor-card__identity">
            <div class="vendor-card__label">{{ __('Sold and fulfilled by') }}</div>
            <h3 class="vendor-card__name">
                {{ $store->name }}
                @include(Theme::getThemeNamespace('partials.marketplace.verified-badge'), ['customer' => $store->customer])
            </h3>
        </div>
    </div>

    <div class="vendor-card__contact">
        @if ($phone)
            <div class="vendor-card__line">
                <svg aria-hidden="true"><use href="#svg-icon-phone" xlink:href="#svg-icon-phone"></use></svg>
                @if ($isCustomerLoggedIn)
                    <a href="tel:{{ $phone }}">{{ $displayPhone }}</a>
                @else
                    <span>{{ $displayPhone }} &middot; {{ __('Sign in to reveal') }}</span>
                @endif
            </div>
        @endif
        @if ($email)
            <div class="vendor-card__line">
                <svg aria-hidden="true"><use href="#svg-icon-mail" xlink:href="#svg-icon-mail"></use></svg>
                <span>{{ $email }}</span>
            </div>
        @endif
        @if ($store->address)
            <div class="vendor-card__line">
                <svg aria-hidden="true"><use href="#svg-icon-home" xlink:href="#svg-icon-home"></use></svg>
                <span>{{ $store->address }}</span>
            </div>
        @endif
    </div>

    <div class="vendor-card__actions">
        <a class="vendor-card__button" href="{{ $store->url }}">{{ __('Visit store') }}</a>
        @if ($phone)
            <a class="vendor-card__button vendor-card__button--primary" href="{{ $isCustomerLoggedIn ? 'tel:' . $phone : route('customer.login') }}">
                {{ $isCustomerLoggedIn ? __('Call vendor') : __('View contact') }}
            </a>
        @endif
        @if ($messagingEnabled)
            @auth('customer')
                <form class="vendor-card__chat-form" action="{{ route('customer.messages.start', $store->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->original_product->id }}">
                    <button class="vendor-card__button vendor-card__button--chat" type="submit">
                        <x-core::icon name="ti ti-messages" />
                        <span>{{ __('Chat with seller') }}</span>
                    </button>
                </form>
            @else
                <a class="vendor-card__button vendor-card__button--chat" href="{{ route('customer.login') }}">
                    <x-core::icon name="ti ti-messages" />
                    <span>{{ __('Sign in to chat with seller') }}</span>
                </a>
            @endauth
        @endif
    </div>
</aside>
