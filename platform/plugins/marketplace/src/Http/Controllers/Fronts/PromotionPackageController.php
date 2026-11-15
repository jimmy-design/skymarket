<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Njovu\Mpesa\Services\Gateways\MpesaPaymentService;

class PromotionPackageController extends BaseController
{
    public function index()
    {
        SeoHelper::setTitle(__('Vendor Promotion Packages'))
            ->setDescription(__('Choose how long you want your products promoted across the marketplace.'));

        Theme::breadcrumb()->add(__('Promotion Packages'), route('public.vendor-promotion-packages'));

        $packages = config('plugins.marketplace.promotion-packages.packages', []);
        $periods = config('plugins.marketplace.promotion-packages.periods', []);
        $customer = auth('customer')->user();
        $isVendor = (bool) $customer?->is_vendor;
        $actionUrl = $isVendor
            ? route('marketplace.vendor.dashboard')
            : ($customer
                ? route('marketplace.vendor.become-vendor')
                : route('customer.login'));

        return Theme::scope(
            'marketplace.promotion-packages',
            compact('packages', 'periods', 'customer', 'isVendor', 'actionUrl'),
            MarketplaceHelper::viewPath('promotion-packages', false)
        )->render();
    }

    public function checkout(string $package)
    {
        $selectedPackage = collect(config('plugins.marketplace.promotion-packages.packages', []))
            ->firstWhere('slug', $package);

        abort_unless($selectedPackage, 404);

        $periods = config('plugins.marketplace.promotion-packages.periods', []);
        $customer = auth('customer')->user();
        $phone = $customer->phone ?: $customer->store?->phone;
        $mpesaReady = setting('payment_mpesa_status') == 1
            && setting('mpesa_business_shortcode')
            && setting('mpesa_passkey')
            && setting('mpesa_consumer_key')
            && setting('mpesa_consumer_secret');

        SeoHelper::setTitle(__('Pay for :package', ['package' => $selectedPackage['name']]))
            ->setDescription(__('Confirm your vendor phone number and package period for M-Pesa payment.'));

        Theme::breadcrumb()
            ->add(__('Promotion Packages'), route('public.vendor-promotion-packages'))
            ->add(__('Checkout'));

        return Theme::scope(
            'marketplace.promotion-package-checkout',
            compact('selectedPackage', 'periods', 'customer', 'phone', 'mpesaReady'),
            MarketplaceHelper::viewPath('promotion-package-checkout', false)
        )->render();
    }

    public function pay(string $package, MpesaPaymentService $mpesa): JsonResponse
    {
        $selectedPackage = collect(config('plugins.marketplace.promotion-packages.packages', []))
            ->firstWhere('slug', $package);

        abort_unless($selectedPackage, 404);

        $amount = (int) ($selectedPackage['base_price'] ?? 0);
        $customer = auth('customer')->user();
        $phone = $customer->phone ?: $customer->store?->phone;

        if (
            setting('payment_mpesa_status') != 1
            || ! setting('mpesa_business_shortcode')
            || ! setting('mpesa_passkey')
            || ! setting('mpesa_consumer_key')
            || ! setting('mpesa_consumer_secret')
        ) {
            return response()->json(['message' => __('M-Pesa is not fully configured.')], 422);
        }

        if ($amount < 1) {
            return response()->json(['message' => __('This package does not require an M-Pesa payment.')], 422);
        }

        if (! $phone || ! preg_match('/^(?:\+?254|0)?[17]\d{8}$/', preg_replace('/[\s-]+/', '', $phone))) {
            return response()->json(['message' => __('Add a valid Kenyan phone number to your vendor account first.')], 422);
        }

        $reference = 'SKY' . Str::upper(Str::random(9));
        $paymentId = DB::table('mp_promotion_package_payments')->insertGetId([
            'customer_id' => $customer->getKey(),
            'package_slug' => $selectedPackage['slug'],
            'package_name' => $selectedPackage['name'],
            'amount' => $amount,
            'currency' => 'KES',
            'phone' => $phone,
            'reference' => $reference,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $mpesa->createPackageStkPushRequest(
            $amount,
            $phone,
            $reference,
            'Payment for ' . $selectedPackage['name'],
            route('public.vendor-promotion-packages.callback')
        );

        if (! $result['success']) {
            DB::table('mp_promotion_package_payments')->where('id', $paymentId)->update([
                'status' => 'failed',
                'result_description' => $result['message'],
                'metadata' => json_encode($result['data'] ?? []),
                'updated_at' => now(),
            ]);

            return response()->json(['message' => $result['message']], 422);
        }

        DB::table('mp_promotion_package_payments')->where('id', $paymentId)->update([
            'merchant_request_id' => $result['data']['MerchantRequestID'] ?? null,
            'checkout_request_id' => $result['data']['CheckoutRequestID'] ?? null,
            'metadata' => json_encode($result['data']),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => __($result['message']),
            'reference' => $reference,
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        $callback = $request->input('Body.stkCallback');

        if (! is_array($callback) || empty($callback['CheckoutRequestID'])) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback payload'], 400);
        }

        $payment = DB::table('mp_promotion_package_payments')
            ->where('checkout_request_id', $callback['CheckoutRequestID'])
            ->first();

        if (! $payment) {
            Log::warning('Package M-Pesa callback transaction not found', [
                'checkout_request_id' => $callback['CheckoutRequestID'],
            ]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Transaction not found'], 404);
        }

        if ($payment->status === 'completed') {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback already processed']);
        }

        $details = collect($callback['CallbackMetadata']['Item'] ?? [])
            ->mapWithKeys(fn (array $item) => [$item['Name'] => $item['Value'] ?? null]);
        $paidAmount = (float) ($details['Amount'] ?? 0);
        $successful = (int) ($callback['ResultCode'] ?? 1) === 0
            && $paidAmount >= (float) $payment->amount;

        DB::table('mp_promotion_package_payments')->where('id', $payment->id)->update([
            'status' => $successful ? 'completed' : 'failed',
            'receipt_number' => $details['MpesaReceiptNumber'] ?? null,
            'result_description' => $callback['ResultDesc'] ?? null,
            'metadata' => json_encode($callback),
            'paid_at' => $successful ? now() : null,
            'updated_at' => now(),
        ]);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
    }
}
