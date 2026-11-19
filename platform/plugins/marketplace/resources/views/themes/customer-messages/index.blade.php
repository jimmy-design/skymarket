@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Messages'))

@section('content')
    @include('plugins/marketplace::messages.customer-workspace')
@endsection
