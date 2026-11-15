@php
    Theme::layout('full-width');
@endphp

{!! Theme::partial('page-header', ['withTitle' => true]) !!}

<style>
    .promotion-packages {
        background: #f5f6f8;
        padding: 42px 0 58px;
    }

    .promotion-packages__intro {
        margin: 0 auto 22px;
        max-width: 780px;
        text-align: center;
    }

    .promotion-packages__intro h1 {
        color: #202428;
        font-size: 30px;
        margin-bottom: 10px;
    }

    .promotion-packages__intro p {
        color: #656b70;
        font-size: 16px;
        margin: 0;
    }

    .promotion-packages__periods {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        margin-bottom: 28px;
    }

    .promotion-packages__period {
        background: #fff;
        border: 1px solid #dfe3e6;
        border-radius: 999px;
        color: #454b50;
        font-size: 13px;
        font-weight: 600;
        padding: 7px 13px;
    }

    .promotion-packages__grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(5, minmax(0, 240px));
        justify-content: center;
    }

    .promotion-package {
        background: #fff;
        border: 1px solid #d9dde1;
        border-radius: 8px;
        box-shadow: 0 5px 18px rgba(25, 30, 35, 0.07);
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
        position: relative;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .promotion-package:hover {
        border-color: #f68b1e;
        box-shadow: 0 14px 34px rgba(25, 30, 35, 0.14);
        transform: translateY(-3px);
    }

    .promotion-package--popular {
        border: 2px solid #f68b1e;
    }

    .promotion-package__badge {
        background: #f68b1e;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        padding: 5px 10px;
        position: absolute;
        right: 0;
        text-transform: uppercase;
        top: 0;
        z-index: 1;
    }

    .promotion-package__body {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 0 14px 14px;
    }

    .promotion-package__header {
        background: #fff2e5;
        border-bottom: 1px solid #ffd5ad;
        margin: 0 -14px 10px;
        min-height: 48px;
        padding: 13px 14px;
    }

    .promotion-package__name {
        color: #d86600;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.25;
        margin: 0;
    }

    .promotion-package__features {
        list-style: none;
        margin: 0 0 12px;
        padding: 0;
    }

    .promotion-package__more {
        border-bottom: 1px solid #e2e5e8;
        margin: -2px 0 12px;
    }

    .promotion-package__more summary {
        color: #ed7818;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        padding: 9px 0;
    }

    .promotion-package__more .promotion-package__features {
        margin-bottom: 4px;
    }

    .promotion-package__features li {
        align-items: flex-start;
        border-bottom: 1px solid #e2e5e8;
        display: flex;
        gap: 10px;
        justify-content: space-between;
        min-height: 43px;
        padding: 10px 0;
    }

    .promotion-package__feature-label,
    .promotion-package__feature-value {
        font-size: 13px;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .promotion-package__feature-label {
        color: #292d31;
    }

    .promotion-package__feature-value {
        color: #ed7818;
        font-weight: 600;
        text-align: right;
    }

    .promotion-package__level {
        align-items: center;
        border-bottom: 1px solid #e2e5e8;
        color: #ed7818;
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        margin-bottom: 12px;
        padding: 2px 0 10px;
    }

    .promotion-package__level-dots {
        display: flex;
        gap: 4px;
    }

    .promotion-package__level-dot {
        background: #f0f1f2;
        border: 1px solid #c8cdd1;
        border-radius: 50%;
        height: 8px;
        width: 8px;
    }

    .promotion-package__level-dot.is-active {
        background: #f68b1e;
        border-color: #f68b1e;
    }

    .promotion-package__price {
        color: #202428;
        font-size: 16px;
        font-weight: 700;
        margin: auto 0 10px;
        text-align: right;
    }

    .promotion-package__action {
        background: #f68b1e;
        border-radius: 7px;
        color: #fff;
        display: block;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 12px;
        text-align: center;
    }

    .promotion-package__action:hover {
        background: #df7610;
        color: #fff;
    }

    @media (max-width: 1399px) {
        .promotion-packages__grid {
            grid-template-columns: repeat(4, minmax(0, 230px));
        }
    }

    @media (max-width: 1199px) {
        .promotion-packages__grid {
            grid-template-columns: repeat(3, minmax(0, 230px));
        }
    }

    @media (max-width: 767px) {
        .promotion-packages {
            padding: 30px 0 42px;
        }

        .promotion-packages__grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="promotion-packages">
    <div class="container-xxxl">
        <div class="promotion-packages__intro">
            <h1>{{ __('Promote your products') }}</h1>
            <p>{{ __('Choose a vendor tier, then select how long you want stronger product visibility and sponsored placement.') }}</p>
        </div>

        <div class="promotion-packages__periods" aria-label="{{ __('Available billing periods') }}">
            @foreach ($periods as $period)
                <span class="promotion-packages__period">{{ __($period['name']) }}</span>
            @endforeach
        </div>

        <div class="promotion-packages__grid">
            @foreach ($packages as $package)
                <article @class(['promotion-package', 'promotion-package--popular' => $package['popular'] ?? false])>
                    @if ($package['popular'] ?? false)
                        <span class="promotion-package__badge">{{ __('Popular') }}</span>
                    @endif
                    <div class="promotion-package__body">
                        <header class="promotion-package__header">
                            <h2 class="promotion-package__name">{{ __($package['name']) }}</h2>
                        </header>
                        <ul class="promotion-package__features">
                            @foreach (array_slice($package['features'], 0, 5) as $feature)
                                <li>
                                    <span class="promotion-package__feature-label">{{ __($feature['label']) }}</span>
                                    <span class="promotion-package__feature-value">{{ __($feature['value']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if (count($package['features']) > 5)
                            <details class="promotion-package__more">
                                <summary>{{ __('View :count more benefits', ['count' => count($package['features']) - 5]) }}</summary>
                                <ul class="promotion-package__features">
                                    @foreach (array_slice($package['features'], 5) as $feature)
                                        <li>
                                            <span class="promotion-package__feature-label">{{ __($feature['label']) }}</span>
                                            <span class="promotion-package__feature-value">{{ __($feature['value']) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                        <div class="promotion-package__level">
                            <span>{{ __('Features') }}</span>
                            <span class="promotion-package__level-dots" aria-label="{{ __('Feature level :level of 5', ['level' => $package['level']]) }}">
                                @for ($level = 1; $level <= 5; $level++)
                                    <span @class(['promotion-package__level-dot', 'is-active' => $level <= $package['level']])></span>
                                @endfor
                            </span>
                        </div>
                        <div class="promotion-package__price">{{ __($package['price_label'] ?? 'Price coming soon') }}</div>
                        <a class="promotion-package__action" href="{{ $isVendor ? route('public.vendor-promotion-packages.checkout', $package['slug']) : $actionUrl }}">
                            {{ $isVendor ? __('Choose :package', ['package' => $package['name']]) : ($customer ? __('Become a vendor') : __('Login to continue')) }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
