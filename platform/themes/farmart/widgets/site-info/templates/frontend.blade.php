@php
    $currentProduct = Theme::get('farmartProductDetailModel');
    $hasCurrentProduct = is_object($currentProduct) && isset($currentProduct->store_id);
    $productStore = $sidebar !== 'footer_sidebar'
        && is_plugin_active('marketplace')
        && $hasCurrentProduct
        && $currentProduct->store_id
        ? $currentProduct->store
        : null;
    $productStorePhone = $productStore?->phone ?: $productStore?->customer?->phone;
    $supportPhone = $productStorePhone ?: ($config['phone'] ?? null);
    $supportTitle = $productStorePhone && $productStore?->name
        ? $productStore->name
        : __('Hotline Order');
    $supportDescription = $productStorePhone
        ? __('Contact this store about this product')
        : ($config['working_time'] ?? null);
    $isCustomerLoggedIn = auth('customer')->check();
    $supportPhoneDigits = preg_replace('/\D+/', '', (string) $supportPhone);

    if (strlen($supportPhoneDigits) === 12 && str_starts_with($supportPhoneDigits, '254')) {
        $supportPhoneDigits = '0' . substr($supportPhoneDigits, 3);
    } elseif (strlen($supportPhoneDigits) === 9 && in_array($supportPhoneDigits[0] ?? null, ['1', '7'], true)) {
        $supportPhoneDigits = '0' . $supportPhoneDigits;
    }

    $visibleSupportPhone = ! $isCustomerLoggedIn && strlen($supportPhoneDigits) >= 7
        ? substr($supportPhoneDigits, 0, 4) . '***' . substr($supportPhoneDigits, -3)
        : $supportPhone;
@endphp

@if ($sidebar == 'footer_sidebar')
    <div class="col-xl-3">
        <div class="widget mb-5 mb-md-0">
            <p class="h5 fw-bold widget-title mb-4">{{ $config['name'] }}</p>
            <div class="widget-description mb-4">{!! BaseHelper::clean($config['about']) !!}</div>
            <ul class="ps-0 mt-3 mb-0">
                @if ($config['phone'])
                    <li class="py-2">
                        <span class="svg-icon me-2">
                            <svg>
                                <use
                                    href="#svg-icon-phone"
                                    xlink:href="#svg-icon-phone"
                                ></use>
                            </svg>
                        </span>
                        <span>{{ __('Hotline 24/7:') }}
                            <p class="h4 ms-4 mt-2"><a href="tel:{{ $config['phone'] }}">{{ $config['phone'] }}</a></p>
                        </span>
                    </li>
                @endif
                @if ($config['address'])
                    <li class="py-2">
                        <span class="svg-icon me-2">
                            <svg>
                                <use
                                    href="#svg-icon-home"
                                    xlink:href="#svg-icon-home"
                                ></use>
                            </svg>
                        </span>
                        <span>{{ $config['address'] }}</span>
                    </li>
                @endif
                @if ($config['email'])
                    <li class="py-2">
                        <span class="svg-icon me-2">
                            <svg>
                                <use
                                    href="#svg-icon-mail"
                                    xlink:href="#svg-icon-mail"
                                ></use>
                            </svg>
                        </span>
                        <span><a href="mailto:{{ $config['email'] }}">{{ $config['email'] }}</a></span>
                    </li>
                @endif

                @if ($config['working_time'])
                    <li class="py-2">
                        <span class="me-2">
                            <i class="icon-clock3"></i>
                        </span>
                        <span>{{ $config['working_time'] }}</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
@elseif ($sidebar !== 'product_detail_sidebar' && ($supportDescription || $supportPhone))
    <style>
        .seller-details-card {
            background: #fff;
            border: 1px solid #f1ded0;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(35, 39, 43, .09);
            margin-bottom: 24px;
            overflow: hidden;
            position: relative;
        }

        #seller-details-panel {
            display: block !important;
            height: auto !important;
            opacity: 1 !important;
            overflow: visible !important;
            visibility: visible !important;
            width: 100% !important;
        }

        #seller-details-panel .seller-details-card {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .seller-details-card::before {
            background: #f68b1e;
            content: '';
            height: 100%;
            left: 0;
            position: absolute;
            top: 0;
            width: 5px;
        }

        .seller-details-card__body {
            align-items: center;
            display: flex;
            gap: 14px;
            padding: 18px 18px 18px 22px;
        }

        .seller-details-card__icon {
            align-items: center;
            background: #fff2e5;
            border: 1px solid #ffd8b5;
            border-radius: 50%;
            color: #e8750b;
            display: flex;
            flex: 0 0 48px;
            height: 48px;
            justify-content: center;
            width: 48px;
        }

        .seller-details-card__icon svg {
            fill: currentColor;
            height: 22px;
            width: 22px;
        }

        .seller-details-card__content {
            min-width: 0;
        }

        .seller-details-card__title {
            color: #22272b;
            font-size: 16px;
            font-weight: 800;
            margin: 0 0 3px;
        }

        .seller-details-card__hours {
            align-items: center;
            color: #6e7479;
            display: flex;
            font-size: 12px;
            gap: 6px;
            line-height: 1.4;
            margin: 0 0 5px;
        }

        .seller-details-card__hours::before {
            background: #2f9d57;
            border-radius: 50%;
            content: '';
            flex: 0 0 7px;
            height: 7px;
            width: 7px;
        }

        .seller-details-card__number {
            color: #171a1d;
            display: block;
            font-size: 20px;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .seller-details-card__number:hover {
            color: #e8750b;
        }

        .seller-details-card__action {
            align-items: center;
            background: #f68b1e;
            border-radius: 6px;
            color: #fff;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 13px;
            font-weight: 700;
            gap: 7px;
            justify-content: center;
            margin-left: auto;
            min-height: 40px;
            padding: 9px 14px;
        }

        .seller-details-card__action:hover {
            background: #dc6f0b;
            color: #fff;
        }

        .seller-details-card__action svg {
            fill: currentColor;
            height: 16px;
            width: 16px;
        }

        @media (max-width: 575px) {
            .seller-details-card__body {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .seller-details-card__action {
                margin-left: 62px;
                width: calc(100% - 62px);
            }
        }
    </style>

    <div
        class="row g-0 mb-4 seller-details-wrap"
        id="seller-details-panel"
        style="display: block !important; opacity: 1 !important; visibility: visible !important; width: 100% !important;"
    >
        <div class="col-12">
            <div class="seller-details-card" role="region" aria-label="{{ __('Seller details') }}">
                <div class="seller-details-card__body">
                    <div class="seller-details-card__icon" aria-hidden="true">
                        <svg>
                            <use href="#svg-icon-phone" xlink:href="#svg-icon-phone"></use>
                        </svg>
                    </div>

                    <div class="seller-details-card__content">
                        <h6 class="seller-details-card__title">{{ $supportTitle }}</h6>
                        @if ($supportDescription)
                            <p class="seller-details-card__hours">{{ $supportDescription }}</p>
                        @endif
                        @if ($supportPhone)
                            @if ($isCustomerLoggedIn)
                                <a class="seller-details-card__number" href="tel:{{ $supportPhone }}">{{ $visibleSupportPhone }}</a>
                            @else
                                <span class="seller-details-card__number">{{ $visibleSupportPhone }}</span>
                            @endif
                        @endif
                    </div>

                    @if ($supportPhone)
                        <a
                            class="seller-details-card__action"
                            href="{{ $isCustomerLoggedIn ? 'tel:' . $supportPhone : route('customer.login') }}"
                        >
                            <svg aria-hidden="true">
                                <use href="#svg-icon-phone" xlink:href="#svg-icon-phone"></use>
                            </svg>
                            <span>{{ $isCustomerLoggedIn ? __('Contact seller') : __('Sign in to view') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
