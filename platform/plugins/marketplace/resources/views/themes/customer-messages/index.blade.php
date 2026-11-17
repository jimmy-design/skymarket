@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Messages'))

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body" @if ($messages->currentPage() === 1) data-sm-buyer-inbox data-url="{{ route('customer.messages.live') }}" @endif>
            @forelse ($messages as $message)
                @php
                    $lastMessage = $message->latestReply ?: $message;
                    $hasUnread = $message->unread_count > 0;
                @endphp
                <a class="d-flex gap-3 align-items-start py-3 border-bottom text-decoration-none text-dark"
                   href="{{ route('customer.messages.show', $message) }}">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between gap-3">
                            <strong>{{ $message->store?->name ?: __('Unavailable supplier') }}</strong>
                            <small class="text-muted">{{ BaseHelper::formatDateTime($lastMessage->created_at) }}</small>
                        </div>
                        <div @class(['text-muted mt-1', 'fw-bold' => $hasUnread])>
                            {{ Str::limit($lastMessage->content, 110) }}
                        </div>
                    </div>
                    @if ($hasUnread)
                        <span class="badge bg-danger rounded-pill">{{ __('New') }}</span>
                    @endif
                </a>
            @empty
                <div class="text-center text-muted py-5">{{ __('You have no conversations yet.') }}</div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">{{ $messages->links() }}</div>
    <script src="{{ asset('js/skymarket-buyer-inbox.js') }}?v={{ filemtime(public_path('js/skymarket-buyer-inbox.js')) }}" defer></script>
@endsection
