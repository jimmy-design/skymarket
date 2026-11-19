<aside class="sm-action-dock" aria-label="Quick tools">
    @if (auth('customer')->check())
        <a class="sm-action-dock__item" href="{{ route('customer.messages.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4.5h16v12H9l-5 3v-15Z"/><path d="M8 9h8M8 12.5h6"/></svg>
            <span>{{ __('Messenger') }}</span>
        </a>
    @else
        <a class="sm-action-dock__item" href="{{ route('customer.login') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4.5h16v12H9l-5 3v-15Z"/><path d="M8 9h8M8 12.5h6"/></svg>
            <span>{{ __('Messenger') }}</span>
        </a>
    @endif
    <button class="sm-action-dock__item" type="button" data-sm-product-search>
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4.2 4.2"/></svg>
        <span>{{ __('Product search') }}</span>
    </button>
    <button class="sm-action-dock__item" type="button" data-sm-back-to-top>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20V4M5.5 10.5 12 4l6.5 6.5"/></svg>
        <span>{{ __('Back to top') }}</span>
    </button>
</aside>

@if (auth('customer')->check())
    <section class="sm-messenger-window" id="sm-messenger-window" data-sm-messenger-window data-inbox-url="{{ route('customer.messages.live') }}" hidden role="dialog" aria-modal="false" aria-label="{{ __('Messenger') }}">
        <header class="sm-messenger-window__header">
            <button class="sm-messenger-window__back" type="button" data-sm-messenger-back hidden aria-label="{{ __('Back to conversations') }}">‹</button>
            <div><strong>{{ __('Messenger') }}</strong><span>{{ __('Your conversations with sellers') }}</span></div>
            <a class="sm-messenger-window__all" href="{{ route('customer.messages.index') }}">{{ __('All messages') }}</a>
            <button class="sm-messenger-window__close" type="button" data-sm-messenger-close aria-label="{{ __('Close messenger') }}">×</button>
        </header>
        <div class="sm-messenger-window__body">
            <aside class="sm-messenger-inbox">
                <div class="sm-messenger-inbox__heading">{{ __('Inbox') }}</div>
                <label class="sm-messenger-inbox__search">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4.2 4.2"/></svg>
                    <input type="search" data-sm-messenger-filter placeholder="{{ __('Search conversations') }}">
                </label>
                <div class="sm-messenger-inbox__list" data-sm-messenger-list aria-live="polite">
                    <p class="sm-messenger-inbox__message">{{ __('Loading conversations…') }}</p>
                </div>
            </aside>
            <main class="sm-messenger-conversation" data-sm-messenger-conversation>
                <div class="sm-messenger-conversation__empty">
                    <span class="sm-messenger-conversation__icon" aria-hidden="true">✉</span>
                    <strong>{{ __('Your conversations') }}</strong>
                    <span>{{ __('Choose a seller conversation to read and reply.') }}</span>
                </div>
            </main>
        </div>
    </section>
@endif
