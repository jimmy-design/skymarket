@php
    $enabledSocialProviders = [];

    if (Route::has('auth.social') && class_exists(\Botble\SocialLogin\Facades\SocialService::class)) {
        $enabledSocialProviders = SocialService::setting('enable') ? SocialService::getProviderKeysEnabled() : [];
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
