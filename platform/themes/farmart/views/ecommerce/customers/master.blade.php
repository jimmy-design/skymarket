@php
    Theme::layout('full-width');
    Theme::asset()
        ->container('footer')
        ->remove('ecommerce-utilities-js');
@endphp
<link rel="stylesheet" href="{{ asset('css/skymarket-customer-navigation.css') }}?v={{ filemtime(public_path('css/skymarket-customer-navigation.css')) }}">
{!! Theme::partial('page-header', ['size' => 'xxxl']) !!}

<div class="container-xxxl">
    <div class="customer-account-layout row my-4">
        <div class="customer-account-sidebar col-md-auto" data-customer-account-sidebar>
            <button class="customer-account-sidebar__toggle" type="button" data-account-navigation-toggle aria-expanded="false" aria-controls="customer-dashboard-navigation" aria-label="{{ __('Expand account menu') }}" title="{{ __('Expand account menu') }}">
                <x-core::icon name="ti ti-menu-2" />
                <span>{{ __('My account') }}</span>
            </button>
            <ul class="nav flex-column dashboard-navigation" id="customer-dashboard-navigation">
                @foreach (DashboardMenu::getAll('customer') as $item)
                    <li class="nav-item" id="{{ $item['id'] }}">
                        <a
                            class="nav-link
                            @if ($item['active']) active @endif"
                            href="{{ $item['url']  }}"
                            aria-current="@if ($item['active']) true @else false @endif"
                        >
                            @if ($item['icon'])
                                <x-core::icon :name="$item['icon']" />
                            @endif
                            <span class="customer-account-sidebar__label">{{ __($item['name']) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col customer-page">
            <div class="customer-dashboard-container">
                <div class="customer-dashboard-content">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        var sidebar = document.querySelector('[data-customer-account-sidebar]');
        var toggle = document.querySelector('[data-account-navigation-toggle]');
        if (!toggle || !sidebar) return;
        toggle.addEventListener('click', function () {
            var expanded = sidebar.classList.toggle('is-expanded');
            toggle.setAttribute('aria-expanded', String(expanded));
        });
        sidebar.addEventListener('mouseleave', function () {
            if (!window.matchMedia('(hover: hover)').matches) return;
            sidebar.classList.remove('is-expanded');
            toggle.setAttribute('aria-expanded', 'false');
        });
    })();
</script>
