@if (SocialService::hasAnyProviderEnable())
    <div class="card-social-login-container">
        <div class="social-login-text">
            <div class="login-text">{{ __('Or login with') }}</div>
        </div>
        <div class="card-social-login-content">
            @foreach (SocialService::getProviderKeys() as $item)
                @if (SocialService::getProviderEnabled($item))
                    <a
                        class="sl-button sl-button-{{ $item }} mt-2"
                        aria-label="{{ __('Continue with :provider', ['provider' => ucfirst($item)]) }}"
                        href="{{ route('auth.social', array_merge([$item], $params)) }}"
                    >
                        <div class="sl-button-svg-container">
                            <span class="svg-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    @if ($item === 'apple')
                                        <path fill="currentColor" d="M16.7 13.2c0-2.1 1.7-3.1 1.8-3.2-1-1.5-2.6-1.7-3.2-1.7-1.4-.1-2.7.8-3.4.8-.7 0-1.8-.8-3-.8-1.6 0-3.1.9-3.9 2.3-1.7 2.9-.4 7.2 1.2 9.6.8 1.2 1.7 2.5 2.9 2.4 1.2-.1 1.7-.8 3.2-.8s1.9.8 3.2.8c1.3 0 2.1-1.2 2.8-2.4.9-1.3 1.2-2.6 1.2-2.7-.1 0-2.8-1.1-2.8-4.3ZM14.4 6.8c.6-.7 1-1.7.9-2.7-.9 0-2 .6-2.6 1.3-.6.6-1 1.7-.9 2.6 1 .1 2-.5 2.6-1.2Z" transform="translate(-1 -2) scale(.9)" />
                                    @else
                                        <use href="#svg-icon-sl-{{ $item }}" xlink:href="#svg-icon-sl-{{ $item }}"></use>
                                    @endif
                                </svg>
                            </span>
                        </div>
                        <div class="sl-button-label-container">
                            <span>{{ ucfirst($item) }}</span>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif
