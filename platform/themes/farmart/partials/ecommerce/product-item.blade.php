@php
    $cardStore = is_plugin_active('marketplace') && $product->store?->exists ? $product->store : null;
    $cardVerified = $cardStore?->customer?->is_vendor && $cardStore?->customer?->has_verified_badge;
    $cardMinimum = max(1, (int) $product->minimum_order_quantity, EcommerceHelper::getMinimumOrderQuantity());
@endphp
<article class="sm-item">
    <div class="product-thumbnail">
        <a class="product-loop__link" href="{{ $product->url }}" aria-label="{{ $product->name }}">
            <img class="product-thumbnail__img" src="{{ RvMedia::getImageUrl($product->image, null, false, RvMedia::getDefaultImage()) }}" data-sm-image-fallback="{{ RvMedia::getDefaultImage() }}" alt="{{ $product->name }}" loading="lazy" decoding="async" width="320" height="320">
            @if ($product->isOutOfStock())<span class="ribbons"><span class="ribbon out-stock">{{ __('Out Of Stock') }}</span></span>@endif
        </a>
        {!! Theme::partial('ecommerce.product-loop-buttons', compact('product') + (!empty($wishlistIds) ? compact('wishlistIds') : [])) !!}
    </div>
    <div class="product-details position-relative">
        <div class="product-content-box">
            <h3 class="product__title"><a href="{{ $product->url }}">{{ $product->name }}</a></h3>
            <strong class="sm-card-price">{{ \Theme\Farmart\Supports\ProductCard::price($product) }}</strong>
            <div class="sm-card-moq">{{ __('MOQ:') }} {{ trans_choice(':count item|:count items', $cardMinimum, ['count' => $cardMinimum]) }}</div>
            @if ($cardStore)
                <div class="sm-card-supplier">
                    @if ($cardVerified)<a class="sm-card-verified" href="{{ $cardStore->url }}" title="{{ __('Verified vendor') }}"><svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="m8 0 2 2.1 2.9.1.7 2.8 2.3 1.8-1.3 2.6.4 2.9-2.8.9L10.4 16 8 14.5 5.6 16l-1.8-2.8-2.8-.9.4-2.9L0 6.8 2.3 5l.7-2.8 2.9-.1L8 0Z"/><path d="m4.3 7.8 2.2 2.1 4.9-4.6" fill="none" stroke="#fff" stroke-width="1.6"/></svg>{{ __('Verified') }}</a><span aria-hidden="true">&middot;</span>@endif
                    @if ($cardStore->created_at)
                        @php $cardSellerYears = (int) $cardStore->created_at->diffInYears(now()); @endphp
                        <a href="{{ $cardStore->url }}" title="{{ $cardStore->name }} · {{ __('On SkyMarket since :date', ['date' => $cardStore->created_at->format('M Y')]) }}">{{ $cardSellerYears > 0 ? trans_choice(':count yr|:count yrs', $cardSellerYears, ['count' => $cardSellerYears]) : __('Less than 1 yr') }}</a>
                    @else
                        <a href="{{ $cardStore->url }}">{{ $cardStore->name }}</a>
                    @endif
                    @if ($cardStore->country_name)<span>&middot; {{ $cardStore->country_name }}</span>@endif
                </div>
            @endif
        @if (!empty($isFlashSale))
            <div class="deal-sold row mt-2">
                @if (Botble\Ecommerce\Facades\FlashSale::isShowSaleCountLeft())
                    <div class="deal-text col-auto">
                        <span class="sold fw-bold">
                            @if ($product->pivot->quantity > $product->pivot->sold)
                                <span class="text">{{ __('Sold') }}: </span>
                                <span class="value">{{ (int) $product->pivot->sold }} /
                                    {{ (int) $product->pivot->quantity }}</span>
                            @else
                                <span class="text text-danger">{{ __('Sold out') }}</span>
                            @endif
                        </span>
                    </div>
                @endif
                <div class="deal-progress col">
                    <div class="progress">
                        <div
                            class="progress-bar"
                            role="progressbar"
                            aria-label="{{ __('Sold out') }}"
                            aria-valuenow="{{ $product->pivot->quantity > 0 ? ($product->pivot->sold / $product->pivot->quantity) * 100 : 0 }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            style="width: {{ $product->pivot->quantity > 0 ? ($product->pivot->sold / $product->pivot->quantity) * 100 : 0 }}%"
                        >
                        </div>
                    </div>
                </div>
            </div>
        @endisset
        </div>
        <div class="product-bottom-box">{!! Theme::partial('ecommerce.product-cart-form', compact('product')) !!}</div>
    </div>
</article>
