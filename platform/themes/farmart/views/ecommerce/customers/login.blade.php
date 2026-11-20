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
            @php
                $socialLoginEnabled = false;
                $enabledSocialProviders = [];

                if (Route::has('auth.social') && class_exists(\Botble\SocialLogin\Facades\SocialService::class)) {
                    $socialLoginEnabled = (bool) SocialService::setting('enable');
                    $enabledSocialProviders = $socialLoginEnabled ? SocialService::getProviderKeysEnabled() : [];
                }

                $canUseAppleLogin = in_array('apple', $enabledSocialProviders, true);
                $canUseGoogleLogin = in_array('google', $enabledSocialProviders, true);
            @endphp
            <div class="auth-social-buttons" aria-label="{{ __('Social sign in') }}">
                <a class="auth-social-button auth-social-button--apple {{ $canUseAppleLogin ? '' : 'is-unavailable' }}"
                    @if ($canUseAppleLogin) href="{{ route('auth.social', 'apple') }}" @else aria-disabled="true" tabindex="-1" title="{{ __('Apple sign in is not configured') }}" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16.7 13.2c0-2.1 1.7-3.1 1.8-3.2-1-1.5-2.6-1.7-3.2-1.7-1.4-.1-2.7.8-3.4.8-.7 0-1.8-.8-3-.8-1.6 0-3.1.9-3.9 2.3-1.7 2.9-.4 7.2 1.2 9.6.8 1.2 1.7 2.5 2.9 2.4 1.2-.1 1.7-.8 3.2-.8s1.9.8 3.2.8c1.3 0 2.1-1.2 2.8-2.4.9-1.3 1.2-2.6 1.2-2.7-.1 0-2.8-1.1-2.8-4.3ZM14.4 6.8c.6-.7 1-1.7.9-2.7-.9 0-2 .6-2.6 1.3-.6.6-1 1.7-.9 2.6 1 .1 2-.5 2.6-1.2Z" transform="translate(-1 -2) scale(.9)" /></svg>
                    <span>{{ __('Apple') }}</span>
                </a>
                <a class="auth-social-button auth-social-button--google {{ $canUseGoogleLogin ? '' : 'is-unavailable' }}"
                    @if ($canUseGoogleLogin) href="{{ route('auth.social', 'google') }}" @else aria-disabled="true" tabindex="-1" title="{{ __('Google sign in is not configured') }}" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M21.35 12.2c0-.7-.06-1.3-.18-1.9H12v3.6h5.23a4.5 4.5 0 0 1-1.94 2.95v2.45h3.14c1.84-1.7 2.92-4.2 2.92-7.1Z"/><path fill="#34A853" d="M12 21.6c2.63 0 4.83-.87 6.43-2.35l-3.14-2.45c-.87.58-1.97.93-3.29.93-2.53 0-4.68-1.7-5.45-4.02H3.3v2.53A9.7 9.7 0 0 0 12 21.6Z"/><path fill="#FBBC05" d="M6.55 13.71a5.8 5.8 0 0 1 0-3.42V7.76H3.3a9.7 9.7 0 0 0 0 8.48l3.25-2.53Z"/><path fill="#EA4335" d="M12 6.14c1.43 0 2.71.49 3.73 1.46l2.8-2.8A9.37 9.37 0 0 0 12 2.4a9.7 9.7 0 0 0-8.7 5.36l3.25 2.53C7.32 7.84 9.47 6.14 12 6.14Z"/></svg>
                    <span>{{ __('Google') }}</span>
                </a>
            </div>
            <p class="auth-register-link">{{ __('Don’t have an account?') }} <a href="{{ route('customer.register') }}">{{ __('Register') }}</a></p>
            <p class="auth-screen__legal">{{ __('Your account helps you keep track of orders and discover more.') }}</p>
        </section>
        <aside class="auth-screen__visual" aria-label="{{ __('People collaborating around a table') }}">
            <div class="auth-screen__visual-wash"></div>
            <div class="auth-screen__badge"><span class="auth-screen__badge-dot"></span>{{ __('Discover something good') }}</div>
            <div class="auth-screen__quote"><strong>{{ __('A marketplace made for you.') }}</strong><span>{{ __('Find products, meet sellers, and make every purchase count.') }}</span></div>
        </aside>
    </main>

    <style>
        html, body.auth-fullscreen-page { height: 100%; margin: 0; }
        body.auth-fullscreen-page { background: #fff8e9; color: #28251f; font-family: inherit; overflow: hidden; }
        body.auth-fullscreen-page > *:not(.auth-screen) { display: none !important; }
        body.auth-fullscreen-page .auth-screen { align-items: stretch; background: linear-gradient(145deg, #e8e9e9 0%, #f5f0df 48%, #fff0c9 100%); border-radius: 32px; display: grid; gap: 18px; grid-template-columns: minmax(0, .88fr) minmax(0, 1.12fr); height: calc(100vh - 20px); margin: 10px; min-height: 0; padding: 10px; }
        body.auth-fullscreen-page .auth-screen__form { align-self: center; justify-self: center; max-width: 540px; padding: 20px 42px; width: 100%; }
        body.auth-fullscreen-page .auth-brand { align-items: center; border: 1px solid rgba(50,50,50,.25); border-radius: 999px; color: #28251f; display: inline-flex; font-size: 14px; font-weight: 550; letter-spacing: -.02em; margin-bottom: 44px; padding: 7px 16px; text-decoration: none; }
        body.auth-fullscreen-page .auth-brand img { max-height: 38px; max-width: 150px; object-fit: contain; }
        body.auth-fullscreen-page .auth-card { background: transparent !important; border: 0; border-radius: 0; box-shadow: none; margin: 0; overflow: visible; }
        body.auth-fullscreen-page .auth-card__banner, body.auth-fullscreen-page .auth-card__header-icon { display: none !important; }
        body.auth-fullscreen-page .auth-card .card-header, body.auth-fullscreen-page .auth-card .card-body { background: transparent !important; border: 0; padding: 0 !important; }
        body.auth-fullscreen-page .auth-card__header { margin-bottom: 20px; }
        body.auth-fullscreen-page .auth-card__header-title { color: #29261f; font-size: clamp(27px, 2.6vw, 34px); font-weight: 600; letter-spacing: -.05em; margin-bottom: 5px !important; }
        body.auth-fullscreen-page .auth-card__header-description { color: #777064; margin: 0; }
        body.auth-fullscreen-page .auth-card .form-label { color: #625c50; font-size: 12px; font-weight: 500; margin-bottom: 4px; }
        body.auth-fullscreen-page .auth-card .form-group { margin-bottom: 11px !important; }
        body.auth-fullscreen-page .auth-card form, body.auth-fullscreen-page .auth-card .form-group, body.auth-fullscreen-page .auth-card .position-relative { max-width: 100%; width: 100%; }
        body.auth-fullscreen-page .auth-card .form-control { background: rgba(255,255,255,.86); border: 1px solid rgba(120,99,61,.12); border-radius: 22px; font-size: 13px; min-height: 42px; padding: 9px 16px; width: 100%; }
        body.auth-fullscreen-page .auth-card .form-control:focus { border-color: #e4ae3e; box-shadow: 0 0 0 3px rgba(228,174,62,.17) !important; }
        body.auth-fullscreen-page .auth-card .btn-auth-submit { background: linear-gradient(100deg, #ffdc68 0%, #ffc74c 100%); border: 0; border-radius: 24px; color: #35290e; font-size: 14px; font-weight: 650; min-height: 46px; width: 100%; }
        body.auth-fullscreen-page .auth-screen__form--login .auth-card__header { text-align: center; }
        body.auth-fullscreen-page .auth-screen__form--login .auth-card form > :has(a[href*="register"]) { display: none !important; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-container { display: none !important; }
        body.auth-fullscreen-page .auth-social-buttons { display: flex; gap: 10px; margin-top: 12px; width: 100%; }
        body.auth-fullscreen-page .auth-social-button { align-items: center; background: rgba(255,255,255,.42); border: 1px solid rgba(92,77,49,.22); border-radius: 999px; color: #302d27 !important; display: inline-flex; flex: 1 1 0; font-size: 12px; font-weight: 500; gap: 8px; justify-content: center; min-height: 42px; padding: 7px 12px; text-decoration: none; transition: background .15s ease, border-color .15s ease; }
        body.auth-fullscreen-page .auth-social-button:hover { background: rgba(255,255,255,.9); border-color: rgba(92,77,49,.38); }
        body.auth-fullscreen-page .auth-social-button svg { flex: 0 0 17px; height: 17px; width: 17px; }
        body.auth-fullscreen-page .auth-social-button.is-unavailable { cursor: default; opacity: .72; }
        body.auth-fullscreen-page .auth-register-link { color: #777064; font-size: 12px; margin: 14px 0 0; text-align: center; }
        body.auth-fullscreen-page .auth-register-link a { font-weight: 600; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-container { margin-top: 15px; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-container .social-login-text { display: none; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content { display: flex; gap: 10px; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content .sl-button { align-items: center; background: rgba(255,255,255,.52); border: 1px solid rgba(92,77,49,.2); border-radius: 999px; box-shadow: none; color: #302d27; display: flex; flex: 1 1 0; justify-content: center; margin: 0 !important; min-height: 44px; padding: 4px 12px; text-decoration: none; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content .sl-button:hover { background: #fff; border-color: rgba(92,77,49,.38); }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content .sl-button-svg-container { padding: 4px; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content .sl-button-label-container { font-size: 12px; line-height: 1.2; margin: 0 4px; padding: 4px; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content svg { height: 19px; width: 19px; }
        body.auth-fullscreen-page .auth-card a { color: #976610; }
        body.auth-fullscreen-page .auth-screen__legal { color: #8d8679; font-size: 11px; margin: 20px 0 0; }
        body.auth-fullscreen-page .auth-screen__visual { background-color: #77766f; background-image: url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1800&q=90'); background-position: center; background-size: cover; border-radius: 30px; height: calc(100vh - 24px); min-height: 0; overflow: hidden; position: relative; }
        body.auth-fullscreen-page .auth-screen__visual-wash { background: linear-gradient(145deg, rgba(255,191,72,.08), transparent 44%, rgba(31,28,23,.34)); inset: 0; position: absolute; }
        body.auth-fullscreen-page .auth-screen__badge { align-items: center; background: #ffdc68; border-radius: 12px; color: #49370e; display: flex; font-size: 12px; gap: 9px; left: 24px; padding: 11px 14px; position: absolute; top: 24px; }
        body.auth-fullscreen-page .auth-screen__badge-dot { background: #77550d; border-radius: 50%; height: 7px; width: 7px; }
        body.auth-fullscreen-page .auth-screen__quote { backdrop-filter: blur(12px); background: rgba(255,255,255,.91); border: 1px solid rgba(255,255,255,.7); border-radius: 16px; bottom: 24px; box-shadow: 0 18px 46px rgba(30,25,15,.2); display: grid; gap: 5px; left: 24px; max-width: 310px; padding: 16px 18px; position: absolute; }
        body.auth-fullscreen-page .auth-screen__quote strong { color: #343027; font-size: 16px; }
        body.auth-fullscreen-page .auth-screen__quote span { color: #716a5f; font-size: 13px; line-height: 1.55; }
        @media (max-width: 900px) { body.auth-fullscreen-page .auth-screen { display: flex; height: calc(100vh - 16px); justify-content: center; margin: 8px; padding: 8px; } body.auth-fullscreen-page .auth-screen__form { max-width: 560px; padding: 18px 24px; } body.auth-fullscreen-page .auth-screen__visual { display: none; } }
        @media (max-width: 640px) { body.auth-fullscreen-page .auth-screen { height: calc(100dvh - 16px); margin: 8px; padding: 8px; } body.auth-fullscreen-page .auth-screen__form { max-width: 520px; padding: 14px 18px; } body.auth-fullscreen-page .auth-brand { margin-bottom: 16px; } }
    </style>
</body>
</html>
