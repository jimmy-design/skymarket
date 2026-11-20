@php Theme::layout('auth-fullscreen'); @endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Login') }} - {{ theme_option('site_title', config('app.name')) }}</title>
    {!! Theme::header() !!}
</head>
<body id="page-auth" class="auth-fullscreen-page">
    {!! Theme::partial('svg-icons') !!}
    <main class="auth-screen">
        <section class="auth-screen__form auth-screen__form--login">
            <a class="auth-brand" href="{{ route('public.index') }}" aria-label="{{ theme_option('site_title', config('app.name')) }}">
                @if ($logo = theme_option('logo'))
                    <img src="{{ RvMedia::getImageUrl($logo) }}" alt="{{ theme_option('site_title', config('app.name')) }}">
                @else
                    <span>{{ theme_option('site_title', config('app.name')) }}</span>
                @endif
            </a>
            {!! $form->renderForm() !!}
            {!! Theme::partial('auth-social-buttons') !!}
            <p class="auth-register-link">{{ __("Don't have an account?") }} <a href="{{ route('customer.register') }}">{{ __("Register") }}</a></p>
            <p class="auth-screen__legal">{{ __('Your account helps you keep track of orders and discover more.') }}</p>
        </section>
        <aside class="auth-screen__visual" aria-label="{{ __('People collaborating around a table') }}">
            <div class="auth-screen__visual-wash"></div>
            <div class="auth-screen__badge"><span class="auth-screen__badge-dot"></span>{{ __('Discover something good') }}</div>
            <div class="auth-screen__quote"><strong>{{ __('A marketplace made for you.') }}</strong><span>{{ __('Find products, meet sellers, and make every purchase count.') }}</span></div>
        </aside>
    </main>

    {!! Theme::partial('auth-fullscreen-styles') !!}
</body>
</html>
