<aside class="sm-action-dock" aria-label="{{ __('Quick tools') }}">
    <a class="sm-action-dock__item" href="{{ auth('customer')->check() ? route('customer.messages.index') : route('customer.login') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4.5h16v12H9l-5 3v-15Z"/><path d="M8 9h8M8 12.5h6"/></svg>
        <span>{{ __('Messages') }}</span>
    </a>
    <button class="sm-action-dock__item" type="button" data-sm-product-search>
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4.2 4.2"/></svg>
        <span>{{ __('Product search') }}</span>
    </button>
    <button class="sm-action-dock__item" type="button" data-sm-back-to-top>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20V4M5.5 10.5 12 4l6.5 6.5"/></svg>
        <span>{{ __('Back to top') }}</span>
    </button>
</aside>
