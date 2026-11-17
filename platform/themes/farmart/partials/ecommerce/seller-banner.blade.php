@php
    $sellerBannerUrl = auth('customer')->user()?->is_vendor
        ? route('marketplace.vendor.products.create')
        : route('marketplace.vendor.become-vendor');
@endphp
<section class="sm-seller-banner" data-sm-seller-banner aria-label="{{ __('Grow with SkyMarket') }}">
    <div>
        <span class="sm-seller-banner__eyebrow">{{ __('Grow with SkyMarket') }}</span>
        <h2>{{ __('Your products. A bigger audience.') }}</h2>
        <p>{{ __('Open your supplier profile, list your products and connect with buyers.') }}</p>
    </div>
    <a href="{{ $sellerBannerUrl }}" class="sm-seller-banner__button">{{ __('Become a seller') }} <span aria-hidden="true">↗</span></a>
</section>
