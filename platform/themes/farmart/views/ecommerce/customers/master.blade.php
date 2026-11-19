@php
    Theme::layout('full-width');
    Theme::asset()
        ->container('footer')
        ->remove('ecommerce-utilities-js');
@endphp
<link rel="stylesheet" href="{{ asset('css/skymarket-customer-navigation.css') }}?v={{ filemtime(public_path('css/skymarket-customer-navigation.css')) }}">
{!! Theme::partial('page-header', ['size' => 'xxxl']) !!}

<div class="container-xxxl">
    <div class="row my-4">
        <div class="col-md-3">
            <button class="dashboard-navigation-toggle" type="button" data-account-navigation-toggle aria-expanded="true" aria-controls="customer-dashboard-navigation">
                <span>{{ __('My account') }}</span>
                <x-core::icon name="ti ti-chevron-down" />
            </button>
            <ul class="nav flex-column dashboard-navigation mb-5" id="customer-dashboard-navigation">
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
                            {{ __($item['name']) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-9 customer-page">
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
        var toggle = document.querySelector('[data-account-navigation-toggle]');
        var navigation = document.getElementById('customer-dashboard-navigation');
        if (!toggle || !navigation) return;
        if (window.matchMedia('(max-width: 767px)').matches) {
            navigation.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
        toggle.addEventListener('click', function () {
            navigation.hidden = !navigation.hidden;
            toggle.setAttribute('aria-expanded', String(!navigation.hidden));
        });
    })();
</script>
