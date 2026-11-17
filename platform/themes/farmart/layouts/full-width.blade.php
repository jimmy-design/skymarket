{!! Theme::partial('header') !!}

<div id="main-content">
    @if (request()->routeIs('public.index') && is_plugin_active('ecommerce') && is_plugin_active('marketplace'))
        {!! Theme::partial('marketplace.discovery') !!}
    @else
        {!! Theme::content() !!}
    @endif
</div>

{!! Theme::partial('footer') !!}
