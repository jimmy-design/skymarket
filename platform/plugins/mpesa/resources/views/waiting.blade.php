@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <div id="payment-status-check">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                        <h4 class="mb-3">{{ __('Processing your M-Pesa payment...') }}</h4>
                        <p class="text-muted">{{ __('Please do not close this window.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('footer')
<script>
    $(document).ready(function() {
        const token = '{{ $token }}';
        const maxAttempts = 20; // Maximum number of attempts
        let attempts = 0;

        const checkStatus = () => {
            if (attempts >= maxAttempts) {
                window.location.href = '{{ route("public.checkout.failed", ["token" => $token]) }}';
                return;
            }

            attempts++;

            $.ajax({
                url: '{{ route("check.payment.status") }}',
                method: 'GET',
                data: {
                    token: token
                },
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = response.redirect_url;
                    } else if (response.status === 'failed') {
                        window.location.href = '{{ route("public.checkout.failed", ["token" => $token]) }}';
                    } else {
                        // If still pending, wait 3 seconds and try again
                        setTimeout(checkStatus, 3000);
                    }
                },
                error: function() {
                    // On error, wait 3 seconds and try again
                    setTimeout(checkStatus, 3000);
                }
            });
        };

        // Start checking payment status
        checkStatus();
    });
</script>
@endpush