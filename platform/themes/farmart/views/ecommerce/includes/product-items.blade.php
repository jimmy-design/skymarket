@php
    $products->loadMissing(['defaultVariation', 'variations.product', 'options', 'options.values']);
    $showSellerBanner = is_plugin_active('marketplace') && (request()->routeIs('public.index') || request()->boolean('homepage_catalog'));
@endphp
<div class="loading loading-container">
    <div class="half-circle-spinner">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
    </div>
</div>
<!--products list-->
<input
    name="page"
    data-value="{{ $products->currentPage() }}"
    type="hidden"
>
<input
    name="q"
    type="hidden"
    value="{{ BaseHelper::stringify(request()->query('q')) }}"
>
<div
    class="row @if (request()->input('layout') == 'list') row-cols-1 shop-products-listing__list @else row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-2 @endif shop-products-listing sm-reference-grid g-0">
        @php
            $firstRowCategories = \Botble\Ecommerce\Models\ProductCategory::query()
                ->wherePublished()->where('parent_id', 0)->orderBy('order')->orderBy('name')->get();
        @endphp
        <aside class="sm-first-row-categories" aria-label="{{ __('Product categories') }}">
            <nav aria-label="{{ __('Browse categories') }}" tabindex="0">
                @foreach ($firstRowCategories as $firstRowCategory)
                    @php
                        $categoryNameForIcon = mb_strtolower($firstRowCategory->name);
                        $categoryEmoji = match (true) {
                            str_contains($categoryNameForIcon, 'nursery') => "\u{1F6CF}\u{FE0F}",
                            str_contains($categoryNameForIcon, 'maternity') => "\u{1F930}",
                            str_contains($categoryNameForIcon, 'diaper') => "\u{1F6C1}",
                            str_contains($categoryNameForIcon, 'toys') => "\u{1F9F8}",
                            str_contains($categoryNameForIcon, 'clothing') => "\u{1F455}",
                            str_contains($categoryNameForIcon, 'feeding') => "\u{1F37C}",
                            str_contains($categoryNameForIcon, 'travel') => "\u{1F9F3}",
                            default => "\u{1F6CD}\u{FE0F}",
                        };
                    @endphp
                    <a href="{{ route('public.products', ['categories' => [$firstRowCategory->id]]) }}" @if (in_array($firstRowCategory->id, (array) request('categories', []))) aria-current="page" @endif>
                        <span class="sm-category-icon" aria-hidden="true">@if ($firstRowCategory->icon_image)<img src="{{ RvMedia::getImageUrl($firstRowCategory->icon_image) }}" alt="" width="20" height="20" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span hidden>{{ $categoryEmoji }}</span>@else{{ $categoryEmoji }}@endif</span>
                        <span class="sm-category-name">{{ $firstRowCategory->name }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>
    @forelse ($products as $product)
        <div class="col">
            <div class="product-inner">
                {!! Theme::partial('ecommerce.product-item', compact('product')) !!}
            </div>
        </div>
        @if ($showSellerBanner && $loop->iteration === min(11, $products->count()))
            {!! Theme::partial('ecommerce.seller-banner') !!}
        @endif
    @empty
        <div class="col-12 w-100">
            <div
                class="alert alert-warning mt-4 w-100"
                role="alert"
            >
                {{ __(':total Product(s) found', ['total' => 0]) }}
            </div>
        </div>
    @endforelse
</div>

<div class="row mt-2 mb-3">
    @php
       if (Route::currentRouteName() !== 'public.products') {
           request()->merge(['categories' => null]);
       }
    @endphp
    {!! $products->withQueryString()->links(Theme::getThemeNamespace('partials.pagination-numeric')) !!}
</div>
