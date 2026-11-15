<ul>
    @foreach($payments->payments as $payment)
        <li>
            @include('plugins/mpesa::detail', compact('payment'))
        </li>
    @endforeach
</ul>
