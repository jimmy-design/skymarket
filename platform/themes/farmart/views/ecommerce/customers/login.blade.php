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
        body.auth-fullscreen-page .auth-screen { align-items: stretch; background: linear-gradient(130deg, #fffdf7 0%, #fff4d6 48%, #ffe7b1 100%); border-radius: 32px; display: grid; gap: 18px; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); height: calc(100vh - 20px); margin: 10px; min-height: 0; padding: 10px; }
        body.auth-fullscreen-page .auth-screen__form { align-self: center; justify-self: center; max-width: 540px; padding: 20px 30px; width: 100%; }
        body.auth-fullscreen-page .auth-brand { align-items: center; color: #28251f; display: inline-flex; font-size: 18px; font-weight: 650; letter-spacing: -.04em; margin-bottom: 28px; text-decoration: none; }
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
