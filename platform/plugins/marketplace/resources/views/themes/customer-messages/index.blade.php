@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Messages'))

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @forelse ($messages as $message)
                @php
                    $lastMessage = $message->replies->last() ?: $message;
                    $hasUnread = $message->replies->where('sender_type', 'vendor')->whereNull('read_at')->isNotEmpty();
                @endphp
                <a class="d-flex gap-3 align-items-start py-3 border-bottom text-decoration-none text-dark"
                   href="{{ route('customer.messages.show', $message) }}">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between gap-3">
                            <strong>{{ $message->store->name }}</strong>
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
@endsection
