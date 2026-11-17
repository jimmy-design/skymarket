<article class="sm-product">
    <a href="{{ $product->url }}" class="sm-product__image"><img src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}" loading="lazy" width="300" height="240"></a>
    <div class="sm-product__body">
        <span class="sm-eyebrow">{{ $product->store?->name ?: __('SkyMarket selection') }}</span>
        <a href="{{ $product->url }}" class="sm-product__name">{{ $product->name }}</a>
        <strong class="sm-product__price">{{ $product->price()->displayAsText() }}</strong>
        <span class="sm-muted">{{ __('Listed price · ask the supplier about bulk pricing') }}</span>
        @if ($product->store?->exists && MarketplaceHelper::isEnabledMessagingSystem())
            <div class="sm-product__actions"><a class="sm-button sm-button--outline" href="{{ route('public.sourcing.enquiry', $product->id) }}">{{ __('Request quote') }}</a><a class="sm-text-link" href="{{ route('public.sourcing.enquiry', ['id' => $product->id, 'mode' => 'chat']) }}">{{ __('Chat now') }} ↗</a></div>
        @else
            <a class="sm-button sm-button--outline" href="{{ $product->url }}">{{ __('View product') }} ↗</a>
        @endif
    </div>
</article>
