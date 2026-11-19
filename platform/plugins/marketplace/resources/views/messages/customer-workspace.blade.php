<link rel="stylesheet" href="{{ asset('css/skymarket-customer-messenger.css') }}?v={{ filemtime(public_path('css/skymarket-customer-messenger.css')) }}">

<section class="sm-customer-messenger" data-sm-account-messenger-fit aria-label="{{ __('Messages') }}">
    <aside class="sm-customer-messenger__inbox">
        <header class="sm-customer-messenger__inbox-header">
            <h1>{{ __('Messages') }}</h1>
            <label class="sm-customer-messenger__search">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4.2 4.2"/></svg>
                <input type="search" data-sm-account-message-filter placeholder="{{ __('Search conversations') }}" aria-label="{{ __('Search conversations') }}">
            </label>
            <div class="sm-customer-messenger__filters">
                <button class="is-active" type="button" data-sm-account-filter="all">{{ __('All') }}</button>
                <button type="button" data-sm-account-filter="unread">{{ __('Unread') }} <b>{{ $conversations->sum('unread_count') }}</b></button>
            </div>
        </header>

        <nav class="sm-customer-messenger__threads" aria-label="{{ __('Conversations') }}">
            @forelse ($conversations as $conversation)
                @php
                    $lastMessage = $conversation->latestReply ?: $conversation;
                    $isActive = $message && (string) $message->getKey() === (string) $conversation->getKey();
                    $partner = $conversation->store?->name ?: __('Unavailable supplier');
                @endphp
                <a class="sm-customer-thread {{ $isActive ? 'is-active' : '' }}"
                   href="{{ route('customer.messages.show', $conversation) }}"
                   data-sm-account-message-thread
                   data-unread="{{ $conversation->unread_count > 0 ? 'true' : 'false' }}"
                   data-search="{{ Str::lower($partner . ' ' . $lastMessage->content) }}">
                    @if ($conversation->store?->logo_url)
                        <img class="sm-customer-thread__avatar" src="{{ $conversation->store->logo_url }}" alt="{{ $partner }}">
                    @else
                        <span class="sm-customer-thread__avatar" aria-hidden="true">{{ mb_substr($partner, 0, 1) }}</span>
                    @endif
                    <span class="sm-customer-thread__details">
                        <span class="sm-customer-thread__heading"><strong>{{ $partner }} @include(Theme::getThemeNamespace('partials.marketplace.verified-badge'), ['customer' => $conversation->store?->customer])</strong><time>{{ BaseHelper::formatDateTime($lastMessage->created_at) }}</time></span>
                        <span class="sm-customer-thread__preview">{{ Str::limit($lastMessage->content, 100) }}</span>
                    </span>
                    @if ($conversation->unread_count > 0)<span class="sm-customer-thread__unread" aria-label="{{ __('Unread messages') }}"></span>@endif
                </a>
            @empty
                <p class="sm-customer-messenger__empty-inbox">{{ __('You have no conversations yet.') }}</p>
            @endforelse
        </nav>
    </aside>

    <main class="sm-customer-messenger__conversation">
        @if ($message)
            @include('plugins/marketplace::messages.conversation', ['viewer' => 'customer'])
        @else
            <div class="sm-customer-messenger__welcome">
                <span aria-hidden="true">✉</span>
                <strong>{{ __('Your conversations') }}</strong>
                <p>{{ __('Choose a seller conversation to read and reply.') }}</p>
            </div>
        @endif
    </main>
</section>

<script>
    (function () {
        var messenger = document.querySelector('[data-sm-account-messenger-fit]');
        function fitMessenger() {
            if (!messenger) return;
            var available = window.innerHeight - messenger.getBoundingClientRect().top - 16;
            messenger.style.height = Math.max(320, available) + 'px';
        }
        fitMessenger();
        window.addEventListener('resize', fitMessenger, { passive: true });

        var input = document.querySelector('[data-sm-account-message-filter]');
        var filter = 'all';
        var threads = document.querySelectorAll('[data-sm-account-message-thread]');
        function updateThreads() {
            var query = input.value.trim().toLocaleLowerCase();
            threads.forEach(function (thread) {
                thread.hidden = !thread.dataset.search.includes(query) || (filter === 'unread' && thread.dataset.unread !== 'true');
            });
        }
        input.addEventListener('input', updateThreads);
        document.querySelectorAll('[data-sm-account-filter]').forEach(function (button) {
            button.addEventListener('click', function () {
                filter = button.dataset.smAccountFilter;
                document.querySelectorAll('[data-sm-account-filter]').forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });
                updateThreads();
            });
        });
    })();
</script>
