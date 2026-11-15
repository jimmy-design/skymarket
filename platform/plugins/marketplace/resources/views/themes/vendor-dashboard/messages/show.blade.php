@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <x-core::card>
        <x-core::card.body>
            <x-core::datagrid>
                <x-core::datagrid.item>
                    <x-slot:title>{{ __('Sent at') }}</x-slot:title>
                    {{ BaseHelper::formatDateTime($message->created_at) }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>{{ __('Name') }}</x-slot:title>
                    {{ $message->name }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>{{ __('Email') }}</x-slot:title>
                    <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                </x-core::datagrid.item>
            </x-core::datagrid>

            <div class="mt-4">
                @foreach (collect([$message])->merge($message->replies) as $item)
                    <div class="d-flex mb-3 {{ $item->sender_type === 'vendor' ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="p-3 rounded {{ $item->sender_type === 'vendor' ? 'bg-primary text-white' : 'bg-body-tertiary' }}" style="max-width: 78%">
                            <div class="fw-bold mb-1">{{ $item->sender_type === 'vendor' ? __('You') : $message->name }}</div>
                            <div>{!! BaseHelper::clean(nl2br($item->content)) !!}</div>
                            <small class="opacity-75">{{ BaseHelper::formatDateTime($item->created_at) }}</small>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($message->customer_id)
                <form method="POST" action="{{ route('marketplace.vendor.messages.reply', $message) }}" class="mt-4">
                    @csrf
                    <label class="form-label fw-bold" for="vendor-message-reply">{{ __('Reply') }}</label>
                    <textarea class="form-control" id="vendor-message-reply" name="content" rows="4" maxlength="2000" required>{{ old('content') }}</textarea>
                    <x-core::button type="submit" class="mt-3" icon="ti ti-send">{{ __('Send reply') }}</x-core::button>
                </form>
            @else
                <div class="alert alert-info mt-4">{{ __('This guest message can only be answered by email.') }}</div>
            @endif
        </x-core::card.body>

        <x-core::card.footer>
            <x-core::button tag="a" :href="route('marketplace.vendor.messages.index')" icon="ti ti-arrow-left">
                {{ __('Back') }}
            </x-core::button>
        </x-core::card.footer>
    </x-core::card>
@stop
