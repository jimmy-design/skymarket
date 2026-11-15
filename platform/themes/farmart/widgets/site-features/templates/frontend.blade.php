@if ($sidebar === 'product_detail_sidebar' && ($currentProduct = Theme::get('farmartProductDetailModel')) && $currentProduct->store_id)
    @php
        $store = $currentProduct->store;
        $phone = $store->phone ?: $store->customer?->phone;
        $email = $store->email ?: $store->customer?->email;
        $isCustomerLoggedIn = auth('customer')->check();
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
        .product-vendor-card { background:#fff; border:1px solid #eadfd6; border-radius:8px; box-shadow:0 12px 30px rgba(35,39,43,.1); margin-bottom:18px; overflow:hidden }
        .product-vendor-card__header { align-items:center; background:#fff8f1; border-bottom:1px solid #f0e2d7; display:flex; gap:12px; padding:16px }
        .product-vendor-card__logo { background:#fff; border:1px solid #eadfd6; border-radius:6px; flex:0 0 52px; height:52px; object-fit:contain; padding:5px; width:52px }
        .product-vendor-card__identity { min-width:0 }
        .product-vendor-card__eyebrow { color:#777; font-size:11px; font-weight:700; margin-bottom:2px; text-transform:uppercase }
        .product-vendor-card__name { color:#202428; font-size:17px; font-weight:800; line-height:1.25; margin:0; overflow-wrap:anywhere }
        .product-vendor-card__details { padding:14px 16px 4px }
        .product-vendor-card__detail { align-items:flex-start; color:#50565b; display:flex; font-size:13px; gap:9px; line-height:1.4; margin-bottom:10px; overflow-wrap:anywhere }
        .product-vendor-card__detail svg { color:#f68b1e; fill:currentColor; flex:0 0 17px; height:17px; margin-top:1px; width:17px }
        .product-vendor-card__detail a { color:#202428; font-weight:700 }
        .product-vendor-card__benefits { border-top:1px solid #eee5de; margin:2px 16px 0; padding:8px 0 }
        .product-vendor-card__benefit { align-items:center; display:flex; gap:11px; padding:9px 0 }
        .product-vendor-card__benefit + .product-vendor-card__benefit { border-top:1px solid #f1ece8 }
        .product-vendor-card__benefit img { height:30px; object-fit:contain; width:30px }
        .product-vendor-card__benefit-title { color:#25292d; font-size:13px; font-weight:800; line-height:1.25 }
        .product-vendor-card__benefit-text { color:#74797d; font-size:11px; line-height:1.35 }
        .product-vendor-card__actions { display:grid; gap:8px; grid-template-columns:1fr 1fr; padding:8px 16px 16px }
        .product-vendor-card__button { align-items:center; border:1px solid #f68b1e; border-radius:6px; color:#d76d09; display:flex; font-size:12px; font-weight:800; justify-content:center; min-height:38px; padding:7px 9px; text-align:center }
        .product-vendor-card__button--primary { background:#f68b1e; color:#fff }
        .product-vendor-card__button:hover,.product-vendor-card__button--primary:hover { background:#df7610; border-color:#df7610; color:#fff }
    </style>

    <aside class="product-vendor-card" aria-label="{{ __('Vendor information') }}">
        <div class="product-vendor-card__header">
            <img class="product-vendor-card__logo" src="{{ $store->logo_url }}" alt="{{ $store->name }}">
            <div class="product-vendor-card__identity">
                <div class="product-vendor-card__eyebrow">{{ __('Sold and fulfilled by') }}</div>
                <h3 class="product-vendor-card__name">
                    {{ $store->name }}
                    @include(Theme::getThemeNamespace('partials.marketplace.verified-badge'), ['customer' => $store->customer])
                </h3>
            </div>
        </div>

        <div class="product-vendor-card__details">
            @if ($phone)
                <div class="product-vendor-card__detail">
                    <svg aria-hidden="true"><use href="#svg-icon-phone" xlink:href="#svg-icon-phone"></use></svg>
                    @if ($isCustomerLoggedIn)
                        <a href="tel:{{ $phone }}">{{ $displayPhone }}</a>
                    @else
                        <span>{{ $displayPhone }} &middot; {{ __('Sign in to reveal') }}</span>
                    @endif
                </div>
            @endif
            @if ($email)
                <div class="product-vendor-card__detail">
                    <svg aria-hidden="true"><use href="#svg-icon-mail" xlink:href="#svg-icon-mail"></use></svg>
                    <span>{{ $email }}</span>
                </div>
            @endif
            @if ($store->address)
                <div class="product-vendor-card__detail">
                    <svg aria-hidden="true"><use href="#svg-icon-home" xlink:href="#svg-icon-home"></use></svg>
                    <span>{{ $store->address }}</span>
                </div>
            @endif
        </div>

        <div class="product-vendor-card__benefits">
            @for ($i = 1; $i <= 5; $i++)
                @if ($title = Arr::get(Arr::get($config['data'], $i), 'title'))
                    <div class="product-vendor-card__benefit">
                        <img class="lazyload" data-src="{{ RvMedia::getImageUrl(Arr::get(Arr::get($config['data'], $i), 'icon'), null, false, RvMedia::getDefaultImage()) }}" alt="">
                        <div>
                            <div class="product-vendor-card__benefit-title">{{ $title }}</div>
                            <div class="product-vendor-card__benefit-text">{!! BaseHelper::clean(nl2br(Arr::get(Arr::get($config['data'], $i), 'subtitle'))) !!}</div>
                        </div>
                    </div>
                @endif
            @endfor
        </div>

        <div class="product-vendor-card__actions">
            <a class="product-vendor-card__button" href="{{ $store->url }}">{{ __('Visit store') }}</a>
            @if ($phone)
                <a class="product-vendor-card__button product-vendor-card__button--primary" href="{{ $isCustomerLoggedIn ? 'tel:' . $phone : route('customer.login') }}">
                    {{ $isCustomerLoggedIn ? __('Call vendor') : __('View contact') }}
                </a>
            @endif
        </div>
    </aside>
@else
    <div class="row {{ in_array($sidebar, ['pre_footer_sidebar', 'footer_sidebar', 'bottom_footer_sidebar']) ? 'row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-2 justify-content-center my-4 g-2' : 'row-cols-1 bg-light mb-5 py-3 px-4 g-0' }}">
        @for ($i = 1; $i <= 5; $i++)
            @if ($title = Arr::get(Arr::get($config['data'], $i), 'title'))
                <div class="col py-2">
                    <div class="site-info__item d-flex align-items-start">
                        <div class="site-info__image me-3 mt-1">
                            <img class="lazyload" data-src="{{ RvMedia::getImageUrl(Arr::get(Arr::get($config['data'], $i), 'icon'), null, false, RvMedia::getDefaultImage()) }}" alt="{{ $title }}">
                        </div>
                        <div class="site-info__content">
                            <div class="site-info__title h4 fw-bold">{{ $title }}</div>
                            <div class="site-info__desc">{!! BaseHelper::clean(nl2br(Arr::get(Arr::get($config['data'], $i), 'subtitle'))) !!}</div>
                        </div>
                    </div>
                </div>
            @endif
        @endfor
    </div>
@endif
