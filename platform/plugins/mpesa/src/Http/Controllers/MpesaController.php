<?php

namespace Njovu\Mpesa\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Models\Payment;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Payment\Supports\PaymentHelper;
use Illuminate\Routing\Controller;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Njovu\Mpesa\Http\Requests\MpesaPaymentCallbackRequest;
use Njovu\Mpesa\Services\Gateways\MpesaPaymentService;

class MpesaController extends Controller
{
    /**
     * Handle M-Pesa callback.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleCallback(Request $request)
    {
        // Log the incoming callback request for debugging
        Log::info('Callback request received', $request->all());

        // Decode the JSON request data
        $requestData = json_decode($request->getContent(), true);

        // Validate the callback data
        if (!isset($requestData['Body']['stkCallback'])) {
            Log::error('Invalid Callback Data', $requestData);
            return response()->json([
                'ResultCode' => 2,
                'ResultDesc' => 'Invalid callback data',
            ]);
        }

        // Extract the STK callback data
        $stkCallback = $requestData['Body']['stkCallback'];
        $resultCode = $stkCallback['ResultCode'];
        $checkoutRequestID = $stkCallback['CheckoutRequestID'];
        $resultDesc = $stkCallback['ResultDesc'];
        $callbackMetadata = $stkCallback['CallbackMetadata']['Item'] ?? [];

        // Extract transaction details from the callback metadata
        $transactionDetails = [];
        foreach ($callbackMetadata as $item) {
            $transactionDetails[$item['Name']] = $item['Value'] ?? null;
        }

        // Required transaction details
        $amount = $transactionDetails['Amount'] ?? 0; // Default to 0 if null
        $mpesaReceiptNumber = $transactionDetails['MpesaReceiptNumber'] ?? null;
        $transactionDate = $transactionDetails['TransactionDate'] ?? null;
        $phoneNumber = $transactionDetails['PhoneNumber'] ?? null;

        // Find the order using the checkout request ID
        $order = Order::where('checkout_request_id', $checkoutRequestID)->first();

        if (!$order) {
            Log::error('Order not found for CheckoutRequestID:', ['CheckoutRequestID' => $checkoutRequestID]);
            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Order not found',
            ]);
        }

        DB::beginTransaction();
        try {
            Log::info('Starting payment processing', [
                'CheckoutRequestID' => $checkoutRequestID,
                'OrderID' => $order->id
            ]);

            // Update or Create Payment Record
            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'currency' => 'KES',
                    'amount' => $amount,
                    'payment_channel' => 'mpesa',
                    'charge_id' => $mpesaReceiptNumber,
                    'status' => $resultCode == 0 ? PaymentStatusEnum::COMPLETED : OrderStatusEnum::CANCELED,
                    'description' => 'M-Pesa payment for order #' . $order->id,
                    'customer_id' => $order->user_id,
                    'metadata' => json_encode([
                        'mpesa_receipt_number' => $mpesaReceiptNumber,
                        'transaction_date' => $transactionDate,
                        'phone_number' => $phoneNumber,
                        'result_code' => $resultCode,
                        'result_desc' => $resultDesc
                    ]),
                ]
            );

            Log::info('Payment record created/updated', [
                'PaymentID' => $payment->id,
                'OrderID' => $order->id
            ]);

            // Update Order Status
            $order->payment_id = $payment->id;
            $order->payment_reference = $mpesaReceiptNumber;
            $order->status = $resultCode == 0 ? 'completed' : 'canceled';
            $order->save();

            Log::info('Order status updated', [
                'OrderID' => $order->id,
                'Status' => $order->status
            ]);

            DB::commit();

            // Only dispatch OrderCompletedEvent for successful payments
            if ($resultCode == 0) {
                try {
                    event(new OrderCompletedEvent($order));
                    Log::info('OrderCompletedEvent dispatched', [
                        'OrderID' => $order->id
                    ]);
                } catch (\Exception $eventError) {
                    Log::error('Error dispatching OrderCompletedEvent:', [
                        'error' => $eventError->getMessage(),
                        'trace' => $eventError->getTraceAsString()
                    ]);
                }
            }

            if ($resultCode == 0) {
                return response()->json([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Payment processed successfully',
                    'redirect_url' => url("/checkout/{$order->token}/success")
                ]);
            } else {
                return response()->json([
                    'ResultCode' => 1,
                    'ResultDesc' => 'Payment failed',
                    'redirect_url' => url("/checkout/{$order->token}/failed"),
                    'next_url' => url("/checkout/{$order->token}/failed"),
                    'data' => route('public.checkout.success', ['token' => $order->token])
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment callback:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'OrderID' => $order->id ?? null
            ]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Error processing payment callback',
                'redirect_url' => url("/checkout/{$order->token}/failed")
            ]);
        }
    }

    public function postCheckout(Request $request)
    {
        try {
            // Add initial delay to prevent rapid polling
            sleep(2);

            Log::info('Incoming request data:', $request->all());
            $request->validate(['token' => 'required|string']);

            Log::info('Fetching order for token:', ['token' => $request->token]);
            $order = Order::where('token', $request->token)->first();

            if (!$order) {
                Log::error('Order not found for token:', ['token' => $request->token]);
                // Add delay before error response
                sleep(1);
                return (new BaseHttpResponse())
                    ->setError()
                    ->setMessage(__('Order not found!'))
                    ->setData(['token' => $request->token])
                    ->setNextUrl(route('public.checkout.error', ['token' => $request->token]));
            }

            Log::info('Order status:', [
                'OrderID' => $order->id,
                'Status' => $order->status,
            ]);

            // Extract status value using the same logic
            $statusValue = null;
            if (is_object($order->status) && method_exists($order->status, 'getValue')) {
                $statusValue = $order->status->getValue();
            } elseif (is_object($order->status) && property_exists($order->status, 'value')) {
                $statusValue = $order->status->value;
            } else {
                $statusValue = $order->status;
            }
            Log::info('Status field value:', ['value' => $statusValue]);

            // Add status-specific delays
            switch ($statusValue) {
                case 'completed':
                    sleep(3); // Longer delay for completed status
                    Log::info('Order is completed.');
                    return (new BaseHttpResponse())
                        ->setMessage(__('Checkout successfully!'))
                        ->setData([
                            'status' => 'completed',
                            'token' => $request->token
                        ])
                        ->setNextUrl(PaymentHelper::getRedirectURL($request->token));

                case 'canceled':
                    sleep(2); // Medium delay for canceled status
                    Log::info('Order is failed.');
                    return (new BaseHttpResponse())
                        ->setError()
                        ->setMessage(__('Payment failed'))
                        ->setData([
                            'status' => 'canceled',
                            'token' => $request->token
                        ])
                        ->setNextUrl(PaymentHelper::getCancelURL());

                default:
                    sleep(1); // Short delay for pending status
                    Log::info('Order is pending.');
                    return (new BaseHttpResponse())
                        ->setMessage(__('Payment is being processed'))
                        ->setData([
                            'status' => 'pending',
                            'token' => $request->token
                        ])
                        ->setNextUrl(route('check.payment.status', ['token' => $request->token]));
            }

        } catch (\Exception $e) {
            Log::error('Error in postCheckout:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            sleep(1); // Add delay for error response
            return (new BaseHttpResponse())
                ->setError()
                ->setMessage(__('Internal server error'))
                ->setNextUrl(PaymentHelper::getCancelURL());
        }
    }
    public function showWaitingPage($token)
    {
        return view('plugins/mpesa::waiting', ['token' => $token]);
    }

    public function failedCall(
        BaseHttpResponse $response
    ) {
        return $response
            ->setError()
            ->setNextUrl(PaymentHelper::getCancelURL())
            ->withInput()
            ->setMessage(__('Payment failed!'));
    }
}
