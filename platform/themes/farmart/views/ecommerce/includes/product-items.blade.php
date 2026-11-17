@php
    $products->loadMissing(['defaultVariation', 'variations.product', 'options', 'options.values']);
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
    @if ($products->isNotEmpty())
        @php
            $firstRowCategories = \Botble\Ecommerce\Models\ProductCategory::query()
                ->wherePublished()->where('parent_id', 0)->orderBy('order')->orderBy('name')->get();
        @endphp
        <aside class="sm-first-row-categories" aria-label="{{ __('Product categories') }}">
            <h2><span aria-hidden="true">≡</span> {{ __('Categories') }}</h2>
            <nav aria-label="{{ __('Browse categories') }}">
                <a href="{{ route('public.products') }}">{{ __('All products') }} <span aria-hidden="true">›</span></a>
                @foreach ($firstRowCategories as $firstRowCategory)
                    <a href="{{ route('public.products', ['categories' => [$firstRowCategory->id]]) }}" @if (in_array($firstRowCategory->id, (array) request('categories', []))) aria-current="page" @endif>
                        <span>{{ $firstRowCategory->name }}</span><span aria-hidden="true">›</span>
                    </a>
                @endforeach
            </nav>
        </aside>
    @endif
    @forelse ($products as $product)
        <div class="col">
            <div class="product-inner">
                {!! Theme::partial('ecommerce.product-item', compact('product')) !!}
            </div>
        </div>
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
