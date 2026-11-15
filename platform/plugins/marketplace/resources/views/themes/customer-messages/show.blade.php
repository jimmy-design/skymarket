@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Conversation with :store', ['store' => $message->store->name]))

@section('content')
    @if (session('success_msg'))
        <div class="alert alert-success">{{ session('success_msg') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @foreach (collect([$message])->merge($message->replies) as $item)
                <div class="d-flex mb-3 {{ $item->sender_type === 'customer' ? 'justify-content-end' : 'justify-content-start' }}">
                    <div class="p-3 rounded-3 {{ $item->sender_type === 'customer' ? 'bg-primary text-white' : 'bg-body-tertiary' }}" style="max-width: 78%">
                        <div class="small fw-bold mb-1">{{ $item->sender_type === 'customer' ? __('You') : $message->store->name }}</div>
                        <div>{!! BaseHelper::clean(nl2br($item->content)) !!}</div>
                        <div class="small mt-2 opacity-75">{{ BaseHelper::formatDateTime($item->created_at) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer bg-white">
            <form method="POST" action="{{ route('customer.messages.reply', $message) }}">
                @csrf
                <label class="form-label fw-bold" for="message-reply">{{ __('Reply') }}</label>
                <textarea class="form-control @error('content') is-invalid @enderror" id="message-reply" name="content" rows="4" maxlength="2000" required>{{ old('content') }}</textarea>
                @if ($errors->has('content'))<div class="invalid-feedback">{{ $errors->first('content') }}</div>@endif
                <div class="d-flex justify-content-between mt-3">
                    <a class="btn btn-light" href="{{ route('customer.messages.index') }}">{{ __('Back') }}</a>
                    <button class="btn btn-primary px-4" type="submit">{{ __('Send reply') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
