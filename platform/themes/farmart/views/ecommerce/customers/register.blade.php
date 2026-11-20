@php Theme::layout('auth-fullscreen'); @endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Register') }} - {{ theme_option('site_title', config('app.name')) }}</title>
    {!! Theme::header() !!}
</head>
<body id="page-auth" class="auth-fullscreen-page">
    <main class="auth-screen">
        <section class="auth-screen__form">
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
            <div class="auth-screen__badge"><span class="auth-screen__badge-dot"></span>{{ __('Join the SkyMarket community') }}</div>
            <div class="auth-screen__quote"><strong>{{ __('Start your SkyMarket journey.') }}</strong><span>{{ __('Create an account to discover products and connect with trusted sellers.') }}</span></div>
        </aside>
    </main>

    <style>
        html, body.auth-fullscreen-page { height: 100%; margin: 0; }
        body.auth-fullscreen-page { background: #fff8e9; color: #28251f; font-family: inherit; overflow: auto; }
        body.auth-fullscreen-page > *:not(.auth-screen) { display: none !important; }
        body.auth-fullscreen-page .auth-screen { align-items: stretch; background: linear-gradient(130deg, #fffdf7 0%, #fff4d6 48%, #ffe7b1 100%); display: grid; gap: 22px; grid-template-columns: minmax(370px, .9fr) minmax(0, 1.1fr); min-height: 100vh; padding: 14px; }
        body.auth-fullscreen-page .auth-screen__form { align-self: center; justify-self: center; max-width: 440px; padding: 34px 28px; width: 100%; }
        body.auth-fullscreen-page .auth-brand { align-items: center; color: #28251f; display: inline-flex; font-size: 20px; font-weight: 650; letter-spacing: -.04em; margin-bottom: 48px; text-decoration: none; }
        body.auth-fullscreen-page .auth-brand img { max-height: 48px; max-width: 180px; object-fit: contain; }
        body.auth-fullscreen-page .auth-card { background: transparent !important; border: 0; border-radius: 0; box-shadow: none; margin: 0; overflow: visible; }
        body.auth-fullscreen-page .auth-card__banner, body.auth-fullscreen-page .auth-card__header-icon { display: none !important; }
        body.auth-fullscreen-page .auth-card .card-header, body.auth-fullscreen-page .auth-card .card-body { background: transparent !important; border: 0; padding: 0 !important; }
        body.auth-fullscreen-page .auth-card__header { margin-bottom: 30px; }
        body.auth-fullscreen-page .auth-card__header-title { color: #29261f; font-size: clamp(30px, 3vw, 40px); font-weight: 600; letter-spacing: -.05em; margin-bottom: 8px !important; }
        body.auth-fullscreen-page .auth-card__header-description { color: #777064; margin: 0; }
        body.auth-fullscreen-page .auth-card .form-label { color: #625c50; font-size: 13px; font-weight: 500; }
        body.auth-fullscreen-page .auth-card .form-control { background: rgba(255,255,255,.86); border: 1px solid rgba(120,99,61,.12); border-radius: 14px; min-height: 50px; }
        body.auth-fullscreen-page .auth-card .form-control:focus { border-color: #e4ae3e; box-shadow: 0 0 0 3px rgba(228,174,62,.17) !important; }
        body.auth-fullscreen-page .auth-card .btn-auth-submit { background: linear-gradient(100deg, #ffdc68 0%, #ffc74c 100%); border: 0; border-radius: 14px; color: #35290e; font-weight: 650; min-height: 52px; width: 100%; }
        body.auth-fullscreen-page .auth-card a { color: #976610; }
        body.auth-fullscreen-page .auth-screen__legal { color: #8d8679; font-size: 12px; margin: 34px 0 0; }
        body.auth-fullscreen-page .auth-screen__visual { background-color: #77766f; background-image: url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1800&q=90'); background-position: center; background-size: cover; border-radius: 24px; min-height: calc(100vh - 28px); overflow: hidden; position: relative; }
        body.auth-fullscreen-page .auth-screen__visual-wash { background: linear-gradient(145deg, rgba(255,191,72,.08), transparent 44%, rgba(31,28,23,.34)); inset: 0; position: absolute; }
        body.auth-fullscreen-page .auth-screen__badge { align-items: center; background: #ffdc68; border-radius: 14px; color: #49370e; display: flex; font-size: 13px; gap: 10px; left: 30px; padding: 14px 17px; position: absolute; top: 30px; }
        body.auth-fullscreen-page .auth-screen__badge-dot { background: #77550d; border-radius: 50%; height: 7px; width: 7px; }
        body.auth-fullscreen-page .auth-screen__quote { backdrop-filter: blur(12px); background: rgba(255,255,255,.91); border: 1px solid rgba(255,255,255,.7); border-radius: 18px; bottom: 30px; box-shadow: 0 18px 46px rgba(30,25,15,.2); display: grid; gap: 7px; left: 30px; max-width: 340px; padding: 20px 22px; position: absolute; }
        body.auth-fullscreen-page .auth-screen__quote strong { color: #343027; font-size: 16px; }
        body.auth-fullscreen-page .auth-screen__quote span { color: #716a5f; font-size: 13px; line-height: 1.55; }
        @media (max-width: 800px) { body.auth-fullscreen-page .auth-screen { grid-template-columns: minmax(0, 1fr) minmax(0, .9fr); } body.auth-fullscreen-page .auth-screen__form { padding: 26px 10px; } body.auth-fullscreen-page .auth-brand { margin-bottom: 34px; } }
        @media (max-width: 640px) { body.auth-fullscreen-page .auth-screen { display: block; padding: 8px; } body.auth-fullscreen-page .auth-screen__form { max-width: 480px; padding: 36px 22px; } body.auth-fullscreen-page .auth-brand { margin-bottom: 38px; } body.auth-fullscreen-page .auth-screen__visual { display: none; } }
    </style>
</body>
</html>
