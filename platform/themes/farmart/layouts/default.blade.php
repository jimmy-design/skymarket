{!! Theme::partial('header') !!}

<div id="main-content">
    @if (request()->routeIs('public.index') && is_plugin_active('ecommerce') && is_plugin_active('marketplace'))
        {!! Theme::partial('marketplace.discovery') !!}
    @else
    {!! Theme::partial('page-header', [
        'size' => Theme::get('containerSize', 'xl'),
        'withTitle' => Theme::get('withTitle', true),
    ]) !!}
    <div class="container-{{ Theme::get('containerSize', 'xl') }}">
        <div class="mb-5">
            {!! Theme::content() !!}
        </div>
    </div>
    @endif
</div>

{!! Theme::partial('footer') !!}
