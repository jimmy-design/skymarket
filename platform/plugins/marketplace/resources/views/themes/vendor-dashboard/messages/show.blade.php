@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))
@section('content')
    @include('plugins/marketplace::messages.conversation', ['viewer' => 'vendor'])
@stop
