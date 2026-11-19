@php
    $categories = ProductCategoryHelper::getProductCategoriesWithUrl();
    $groupedCategories = $categories->groupBy('parent_id');
    $departments = $groupedCategories->get(0, collect());

    $categoryVisual = function (string $name): string {
        $name = mb_strtolower($name);

        return match (true) {
            str_contains($name, 'phone') || str_contains($name, 'mobile') => '📱',
            str_contains($name, 'computer') || str_contains($name, 'laptop') => '💻',
            str_contains($name, 'audio') || str_contains($name, 'speaker') || str_contains($name, 'headphone') => '🎧',
            str_contains($name, 'camera') || str_contains($name, 'photography') => '📷',
            str_contains($name, 'clothing') || str_contains($name, 'apparel') || str_contains($name, 'shirt') => '👕',
            str_contains($name, 'shoe') || str_contains($name, 'sneaker') || str_contains($name, 'sandals') => '👟',
            str_contains($name, 'bag') || str_contains($name, 'luggage') || str_contains($name, 'backpack') => '👜',
            str_contains($name, 'jewelry') || str_contains($name, 'watch') => '💍',
            str_contains($name, 'eyewear') || str_contains($name, 'sunglasses') => '🕶️',
            str_contains($name, 'sport') || str_contains($name, 'fitness') || str_contains($name, 'bicycle') => '⚽',
            str_contains($name, 'outdoor') || str_contains($name, 'camp') => '🏕️',
            str_contains($name, 'beauty') || str_contains($name, 'makeup') || str_contains($name, 'skin') => '💄',
            str_contains($name, 'hair') => '💇',
            str_contains($name, 'baby') || str_contains($name, 'parent') => '🍼',
            str_contains($name, 'toy') || str_contains($name, 'game') => '🧸',
            str_contains($name, 'health') || str_contains($name, 'medical') => '🩺',
            str_contains($name, 'pet') || str_contains($name, 'dog') || str_contains($name, 'cat') => '🐾',
            str_contains($name, 'garden') || str_contains($name, 'agriculture') || str_contains($name, 'farm') => '🌱',
            str_contains($name, 'food') || str_contains($name, 'beverage') || str_contains($name, 'coffee') => '🍽️',
            str_contains($name, 'home') || str_contains($name, 'furniture') || str_contains($name, 'kitchen') => '🏠',
            str_contains($name, 'light') || str_contains($name, 'lamp') => '💡',
            str_contains($name, 'car') || str_contains($name, 'vehicle') || str_contains($name, 'automotive') => '🚗',
            str_contains($name, 'tool') || str_contains($name, 'hardware') => '🛠️',
            str_contains($name, 'energy') || str_contains($name, 'solar') => '☀️',
            str_contains($name, 'electrical') || str_contains($name, 'electronic') => '🔌',
            str_contains($name, 'safety') || str_contains($name, 'security') => '🛡️',
            str_contains($name, 'school') || str_contains($name, 'office') => '📚',
            str_contains($name, 'machine') || str_contains($name, 'industrial') => '⚙️',
            str_contains($name, 'package') || str_contains($name, 'printing') => '📦',
            str_contains($name, 'gift') || str_contains($name, 'craft') => '🎁',
            str_contains($name, 'construction') || str_contains($name, 'building') => '🏗️',
            str_contains($name, 'material') || str_contains($name, 'raw') => '🧱',
            str_contains($name, 'service') || str_contains($name, 'logistics') => '🚚',
            default => '🛍️',
        };
    };
@endphp

@if ($departments->isNotEmpty())
    <li class="sm-category-menu" data-sm-category-menu>
        <div class="sm-category-menu__departments" role="list">
            @foreach ($departments as $department)
                @php
                    $departmentCategories = $groupedCategories->get($department->id, collect());
                @endphp
                <a
                    class="sm-category-menu__department{{ $loop->first ? ' is-active' : '' }}"
                    href="{{ route('public.single', $department->url) }}"
                    data-sm-department="{{ $department->id }}"
                    @if ($loop->first) aria-current="true" @endif
                >
                    @if ($department->icon_image)
                        <img class="sm-category-menu__art" src="{{ RvMedia::getImageUrl($department->icon_image) }}" alt="" loading="lazy">
                        <span class="sm-category-menu__art sm-category-menu__art--emoji" aria-hidden="true" hidden>{{ $categoryVisual($department->name) }}</span>
                    @else
                        <span class="sm-category-menu__art sm-category-menu__art--emoji" aria-hidden="true">{{ $categoryVisual($department->name) }}</span>
                    @endif
                    <span class="sm-category-menu__department-name">{{ $department->name }}</span>
                    <span class="sm-category-menu__arrow" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div>

        <div class="sm-category-menu__panels">
            @foreach ($departments as $department)
                @php
                    $departmentCategories = $groupedCategories->get($department->id, collect());
                @endphp
                <section
                    class="sm-category-menu__panel{{ $loop->first ? ' is-active' : '' }}"
                    data-sm-panel="{{ $department->id }}"
                    @if (! $loop->first) hidden @endif
                >
                    <div class="sm-category-menu__panel-heading">
                        <a href="{{ route('public.single', $department->url) }}">{{ $department->name }}</a>
                        <span>Browse department</span>
                    </div>

                    @if ($departmentCategories->isNotEmpty())
                        <div class="sm-category-menu__category-grid">
                            @foreach ($departmentCategories as $category)
                                @php
                                    $subcategories = $groupedCategories->get($category->id, collect());
                                @endphp
                                <section class="sm-category-menu__category">
                                    <h3>
                                        <a href="{{ route('public.single', $category->url) }}">
                                            @if ($category->icon_image)
                                                <img class="sm-category-menu__item-art" src="{{ RvMedia::getImageUrl($category->icon_image) }}" alt="" loading="lazy">
                                                <span class="sm-category-menu__item-art sm-category-menu__item-art--emoji" aria-hidden="true" hidden>{{ $categoryVisual($category->name) }}</span>
                                            @else
                                                <span class="sm-category-menu__item-art sm-category-menu__item-art--emoji" aria-hidden="true">{{ $categoryVisual($category->name) }}</span>
                                            @endif
                                            {{ $category->name }}
                                        </a>
                                    </h3>
                                    @if ($subcategories->isNotEmpty())
                                        <ul>
                                            @foreach ($subcategories as $subcategory)
                                                <li>
                                                    <a href="{{ route('public.single', $subcategory->url) }}">
                                                        @if ($subcategory->icon_image)
                                                            <img class="sm-category-menu__leaf-art" src="{{ RvMedia::getImageUrl($subcategory->icon_image) }}" alt="" loading="lazy">
                                                            <span class="sm-category-menu__leaf-art sm-category-menu__leaf-art--emoji" aria-hidden="true" hidden>{{ $categoryVisual($subcategory->name) }}</span>
                                                        @else
                                                            <span class="sm-category-menu__leaf-art sm-category-menu__leaf-art--emoji" aria-hidden="true">{{ $categoryVisual($subcategory->name) }}</span>
                                                        @endif
                                                        <span>{{ $subcategory->name }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </section>
                            @endforeach
                        </div>
                    @else
                        <p class="sm-category-menu__empty">More products are being added to this department.</p>
                    @endif
                </section>
            @endforeach
        </div>
    </li>
@endif
