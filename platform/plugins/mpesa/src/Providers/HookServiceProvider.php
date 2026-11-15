<?php

namespace Njovu\Mpesa\Providers;

use Botble\Payment\Enums\PaymentMethodEnum;
use Njovu\Mpesa\Services\Gateways\MpesaPaymentService;
use Collective\Html\HtmlFacade as Html;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Botble\Payment\Facades\PaymentMethods;
use Illuminate\Support\Facades\Log;
use Botble\Base\Facades\Assets;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerMpesaMethod'], 2, 2);

        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithMpesa'], 2, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 2);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['MPESA'] = MPESA_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MPESA_PAYMENT_METHOD_NAME) {
                $value = 'mpesa';
            }

            return $value;
        }, 2, 2);
        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MPESA_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 2, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == MPESA_PAYMENT_METHOD_NAME) {
                $data = MpesaPaymentService::class;
            }

            return $data;
        }, 2, 2);
    }

    public function addPaymentSettings(?string $settings): string
    {
        // Assets::addScriptsDirectly('platform/plugins/mpesa/resources/js/sample.js');
        Assets::addScriptsDirectly('vendor/core/plugins/mpesa/js/sample.js');
        Assets::addScriptsDirectly('vendor/core/plugins/mpesa/js/hello.js');
        // $countries = Location::getAvailableCountries();
        return $settings . view('plugins/mpesa::settings')->render();
    }

    public function registerMpesaMethod(?string $html, array $data): string
    {
        PaymentMethods::method(MPESA_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/mpesa::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithMpesa(array $data, Request $request): array
    {
        if ($request->input('payment_method') == MPESA_PAYMENT_METHOD_NAME) {
            $currentCurrency = get_application_currency();
            $currencyModel = $currentCurrency->replicate();
            $mpesaService = $this->app->make(MpesaPaymentService::class);
            $supportedCurrencies = $mpesaService->supportedCurrencyCodes();
            $currency = strtoupper($currentCurrency->title);
            $notSupportCurrency = false;
            if (! in_array($currency, $supportedCurrencies)) {
                $notSupportCurrency = true;
                if (! $currencyModel->where('title', 'KES')->exists()) {
                    $data['error'] = true;
                    $data['message'] = __(":name doesn't support :currency. List of currencies supported by :name: :currencies.", [
                        'name' => 'mpesa',
                        'currency' => $currency,
                        'currencies' => implode(', ', $supportedCurrencies),
                    ]);
                    return $data;
                }
            }
            $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);
            $token = $paymentData['checkout_token'];
            if ($notSupportCurrency) {
                $kesCurrency = $currencyModel->where('title', 'KES')->first();
                $paymentData['currency'] = 'KES';
                if ($currentCurrency->is_default) {
                    $paymentData['amount'] = $paymentData['amount'] * $kesCurrency->exchange_rate;
                } else {
                    $paymentData['amount'] = format_price($paymentData['amount'], $currentCurrency, true);
                }
            }
            if (isset($paymentData['return_url'])) {
                $urlParts = parse_url($paymentData['return_url']);
                $paymentData['return_url'] = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'];
            }
            $msmssm = $paymentData['return_url'];
            $paymentData['callback_url'] = route('payments.mpesa.callback');
            Log::info(message: 'Payment Data:', context: $paymentData);
            $checkoutUrl = $mpesaService->createStkPushRequest($paymentData);
            Log::info(message: 'Lets see @checkoutUrl:', context: $checkoutUrl);
            if (isset($checkoutUrl['success']) && $checkoutUrl['success']) {
                // $data['checkoutUrl'] = $checkoutUrl['return_url'];
                // $data['checkoutUrl'] = $msmssm;
                $data['checkoutUrl'] = route('check.payment.status', ['token'=> $token]);
                Log::info(message: 'Joshua Njovu');
            } else {
                $data['error'] = true;
                $data['message'] = $checkoutUrl['message'] ?? __('Something went wrong. Please try again later.');
            }
            return $data;
        }
        return $data;
    }
}
