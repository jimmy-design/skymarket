@php
    $products->loadMissing('defaultVariation');
@endphp

<section class="homepage-products-page">
    <div class="container-xxxl sm-catalog-shell">
        <div class="row my-5">
            <div class="col-12">
                <div class="row catalog-header justify-content-between">
                    <div class="col-auto catalog-header__left d-flex align-items-center">
                        <h2 class="h2 catalog-header__title d-none d-lg-block">{{ __('Products') }}</h2>
                        <a class="d-none sidebar-filter-mobile" href="#">
                            <span class="svg-icon me-2">
                                <svg>
                                    <use href="#svg-icon-filter" xlink:href="#svg-icon-filter"></use>
                                </svg>
                            </span>
                            <span>{{ __('Filter') }}</span>
                        </a>
                    </div>
                    <div class="col-auto catalog-header__right">
                        <div class="catalog-toolbar row align-items-center">
                            @include(Theme::getThemeNamespace('views.ecommerce.includes.sort'))
                            @include(Theme::getThemeNamespace('views.ecommerce.includes.layout'))
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <details class="sm-filter-details"><summary>{{ __('Filter products') }}</summary>
            <form
                    id="products-filter-form"
                    data-action="{{ route('public.products') }}"
                    data-title="{{ __('Products') }}"
                    action="{{ route('public.products') }}"
                    method="GET"
                >
                    <input type="hidden" name="q" value="{{ BaseHelper::stringify(request()->query('q')) }}">
                    @include(Theme::getThemeNamespace('views.ecommerce.includes.filters'))
                </form>
            </details>
            </div>
            <div class="col-12 products-listing position-relative">
                @include(Theme::getThemeNamespace('views.ecommerce.includes.product-items'))
            </div>
        </div>
    </div>
</section>
