<div class="product-loop__buttons">
    <div class="quick-view-button product-loop_button product-quick-view-button">
        <a
            class="quick-view product-loop_action"
            data-url="{{ route('public.ajax.quick-view', ['product_id' => $product->id]) }}"
            data-bs-toggle="tooltip"
            href="#"
            title="{{ __('Quick view') }}"
        >
            <div class="product-loop_icon">
                <span class="svg-icon">
                    <svg>
                        <use
                            href="#svg-icon-quick-view"
                            xlink:href="#svg-icon-quick-view"
                        ></use>
                    </svg>
                </span>
            </div>
            <span class="text">{{ __('Quick view') }}</span>
        </a>
    </div>
    @if (EcommerceHelper::isCompareEnabled())
        <div class="compare-button product-compare-button product-loop_button">
            <a
                class="compare product-loop_action"
                data-url="{{ route('public.compare.add', $product->id) }}"
                href="#"
                title="{{ __('Compare') }}"
            >
                <div class="compare-icons product-loop_icon">
                    <span class="svg-icon">
                        <svg>
                            <use
                                href="#svg-icon-compare"
                                xlink:href="#svg-icon-compare"
                            ></use>
                        </svg>
                    </span>
                </div>
                <span class="text">{{ __('Compare') }}</span>
            </a>
        </div>
    @endif
</div>
