@php
    Theme::layout('full-width');

    $basePrice = (int) ($selectedPackage['base_price'] ?? 0);
@endphp

{!! Theme::partial('page-header', ['withTitle' => true]) !!}

<style>
    .page-header .page-title .container {
        margin-bottom: 10px !important;
        margin-top: 10px !important;
        padding-bottom: 4px !important;
        padding-top: 4px !important;
    }

    .page-header .page-title h1 {
        font-size: 20px;
        line-height: 1.25;
        margin: 0;
    }

    .package-checkout {
        background: #f5f6f8;
        padding: 30px 15px 44px;
    }

    .package-checkout__layout {
        display: block;
        margin: 0 auto;
        max-width: 420px;
    }

    .package-checkout__summary {
        background: #fff;
        border: 1px solid #dde1e4;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(25, 30, 35, 0.08);
    }

    .package-checkout__summary {
        overflow: hidden;
    }

    .package-checkout__label {
        color: #292d31;
        display: block;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .package-checkout__field {
        margin-bottom: 20px;
    }

    .package-checkout__input,
    .package-checkout__select {
        background: #fff;
        border: 1px solid #cfd4d8;
        border-radius: 7px;
        color: #202428;
        min-height: 42px;
        padding: 8px 12px;
        width: 100%;
    }

    .package-checkout__input[readonly] {
        background: #f6f7f8;
    }

    .package-checkout__hint {
        color: #747b80;
        display: block;
        font-size: 12px;
        margin-top: 6px;
    }

    .package-checkout__mpesa {
        background: #fff;
        border: 1px solid #dde1e4;
        border-radius: 7px;
        box-shadow: 0 8px 24px rgba(25, 30, 35, 0.08);
        margin-top: 18px;
        padding: 16px;
    }

    .package-checkout__mpesa-heading {
        align-items: center;
        color: #202428;
        display: flex;
        font-size: 16px;
        font-weight: 700;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .package-checkout__status {
        background: #fff0df;
        border-radius: 999px;
        color: #c65b00;
        font-size: 11px;
        padding: 5px 9px;
    }

    .package-checkout__pay {
        background: #f58220;
        border: 0;
        border-radius: 7px;
        color: #fff;
        cursor: pointer;
        font-weight: 700;
        margin-top: 14px;
        min-height: 42px;
        width: 100%;
    }

    .package-checkout__pay:hover {
        background: #dc6b0b;
    }

    .package-checkout__pay:disabled {
        background: #b8bdc1;
        cursor: not-allowed;
    }

    .package-checkout__message {
        border-radius: 6px;
        display: none;
        font-size: 13px;
        margin-top: 12px;
        padding: 10px 12px;
    }

    .package-checkout__message.is-success {
        background: #eef8f0;
        color: #237a36;
        display: block;
    }

    .package-checkout__message.is-error {
        background: #fff0ed;
        color: #b93622;
        display: block;
    }

    .package-checkout__summary-header {
        background: #fff2e5;
        border-bottom: 1px solid #ffd5ad;
        padding: 14px 16px;
    }

    .package-checkout__summary-header h2 {
        color: #d86600;
        font-size: 18px;
        margin: 0;
    }

    .package-checkout__summary-body {
        padding: 14px 16px 16px;
    }

    .package-checkout__summary-row {
        border-bottom: 1px solid #e5e7e9;
        display: flex;
        gap: 14px;
        justify-content: space-between;
        padding: 8px 0;
    }

    .package-checkout__summary-row strong {
        color: #ed7818;
        text-align: right;
    }

    .package-checkout__total {
        align-items: end;
        display: flex;
        justify-content: space-between;
        padding-top: 14px;
    }

    .package-checkout__total strong {
        color: #202428;
        font-size: 20px;
    }

    .package-checkout__summary .package-checkout__field {
        margin-top: 12px;
    }
</style>

<section class="package-checkout">
    <div class="package-checkout__layout">
        <aside class="package-checkout__summary">
            <div class="package-checkout__summary-header">
                <h2>{{ $selectedPackage['name'] }}</h2>
            </div>
            <div class="package-checkout__summary-body">
                <div class="package-checkout__summary-row">
                    <span>{{ __('Vendor') }}</span>
                    <strong>{{ $customer->name }}</strong>
                </div>

                <div class="package-checkout__field">
                    <label class="package-checkout__label" for="vendor-phone">{{ __('Registered vendor number') }}</label>
                    <input
                        class="package-checkout__input"
                        id="vendor-phone"
                        type="tel"
                        value="{{ $phone }}"
                        placeholder="{{ __('Add a phone number in vendor settings') }}"
                        readonly
                    />
                    <small class="package-checkout__hint">
                        {{ __('This number comes from your vendor account.') }}
                    </small>
                </div>

                <div class="package-checkout__total">
                    <span>{{ __('Total') }}</span>
                    <strong id="checkout-total">{{ $basePrice ? 'KSh ' . number_format($basePrice) : __('Free') }}</strong>
                </div>
            </div>
        </aside>

        <div class="package-checkout__mpesa">
            <div class="package-checkout__mpesa-heading">
                <span>{{ __('M-Pesa STK Push') }}</span>
                <span class="package-checkout__status">{{ $mpesaReady ? __('Ready') : __('Setup required') }}</span>
            </div>
            <p>{{ __('The payment request will be sent to the registered vendor number above.') }}</p>
            <button
                class="package-checkout__pay"
                id="mpesa-stk-push"
                type="button"
                data-url="{{ route('public.vendor-promotion-packages.pay', $selectedPackage['slug']) }}"
                @disabled(! $mpesaReady || $basePrice < 1)
            >
                {{ $basePrice < 1 ? __('No payment required') : ($mpesaReady ? __('Send M-Pesa STK Push') : __('M-Pesa payment unavailable')) }}
            </button>
            <div class="package-checkout__message" id="mpesa-payment-message" role="status"></div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var button = document.getElementById('mpesa-stk-push');
        var message = document.getElementById('mpesa-payment-message');

        if (!button || button.disabled || !message) {
            return;
        }

        button.addEventListener('click', function () {
            button.disabled = true;
            button.textContent = @json(__('Sending request...'));
            message.className = 'package-checkout__message';
            message.textContent = '';

            fetch(button.dataset.url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok) {
                            throw new Error(data.message || @json(__('Unable to send the M-Pesa request.')));
                        }

                        return data;
                    });
                })
                .then(function (data) {
                    message.textContent = data.message;
                    message.className = 'package-checkout__message is-success';
                    button.textContent = @json(__('STK Push sent'));
                })
                .catch(function (error) {
                    message.textContent = error.message;
                    message.className = 'package-checkout__message is-error';
                    button.disabled = false;
                    button.textContent = @json(__('Try M-Pesa again'));
                });
        });
    });
</script>
