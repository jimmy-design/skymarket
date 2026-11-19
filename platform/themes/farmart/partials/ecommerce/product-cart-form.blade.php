<form
    class="cart-form"
    action="{{ route('public.cart.add-to-cart') }}"
    method="POST"
>
    @csrf
    @if (!empty($withVariations) && $product->variations()->count())
        <div class="pr_switch_wrap">
            {!! render_product_swatches($product, [
                'selected' => $selectedAttrs,
            ]) !!}
        </div>
        <div
            class="number-items-available"
            style="display: none; margin-bottom: 10px;"
        ></div>
    @endif

    @if (isset($withProductOptions) && $withProductOptions)
        {!! render_product_options($product) !!}
    @endif

    <input
        class="hidden-product-id"
        name="id"
        type="hidden"
        value="{{ $product->is_variation || !$product->defaultVariation->product_id ? $product->id : $product->defaultVariation->product_id }}"
    />

    @if (EcommerceHelper::isCartEnabled() || !empty($withButtons))
        {!! apply_filters(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, null, $product) !!}
        <div class="product-button">
            @if (EcommerceHelper::isCartEnabled())
                {!! Theme::partial('ecommerce.product-quantity', compact('product')) !!}
                <button
                    class="btn btn-primary mb-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                    name="add_to_cart"
                    type="submit"
                    value="1"
                    title="{{ __('Add to cart') }}"
                    @if ($product->isOutOfStock()) disabled @endif
                >
                    <span class="svg-icon">
                        <svg>
                            <use
                                href="#svg-icon-cart"
                                xlink:href="#svg-icon-cart"
                            ></use>
                        </svg>
                    </span>
                    <span class="add-to-cart-text ms-2">{{ __('Add to cart') }}</span>
                </button>

                @if (EcommerceHelper::isQuickBuyButtonEnabled() && isset($withBuyNow) && $withBuyNow)
                    <button
                        class="btn btn-primary btn-black mb-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                        name="checkout"
                        type="submit"
                        value="1"
                        title="{{ __('Buy Now') }}"
                        @if ($product->isOutOfStock()) disabled @endif
                    >
                        <span class="add-to-cart-text ms-2">{{ __('Buy Now') }}</span>
                    </button>
                @endif
            @endif
            @if (!empty($withButtons))
                {!! Theme::partial('ecommerce.product-loop-buttons', compact('product', 'wishlistIds')) !!}
            @endif
        </div>
    @endif
</form>
@if (!empty($withEnquiry) && is_plugin_active('marketplace') && $product->store?->exists && MarketplaceHelper::isEnabledMessagingSystem())
    @auth('customer')
        @if ((int) $product->store->customer_id !== (int) auth('customer')->id())
            <form action="{{ route('customer.messages.start', $product->store_id) }}" method="POST" class="sm-product-enquiry mt-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->original_product->id }}">
                <label class="sm-product-enquiry__label" for="sm-product-enquiry-message">{{ __('Message the supplier about this product') }}</label>
                <textarea id="sm-product-enquiry-message" name="content" rows="3" maxlength="2000" required placeholder="{{ __('Ask about price, availability, or delivery…') }}">{{ old('content') }}</textarea>
                <button type="submit" class="btn btn-primary sm-product-enquiry__submit">
                    <x-core::icon name="ti ti-messages" />
                    <span>{{ __('Send enquiry') }}</span>
                </button>
            </form>
        @endif
    @else
        <a class="btn btn-primary sm-product-enquiry__submit mt-3" href="{{ route('customer.login') }}">
            <x-core::icon name="ti ti-messages" />
            <span>{{ __('Sign in to send an enquiry') }}</span>
        </a>
    @endauth
@endif
