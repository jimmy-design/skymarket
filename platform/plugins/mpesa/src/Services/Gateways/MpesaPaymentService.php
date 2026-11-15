<?php

namespace Njovu\Mpesa\Services\Gateways;

use Botble\Ecommerce\Models\Order;
use Botble\Payment\Models\Payment;
use Botble\Payment\Enums\PaymentStatusEnum;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Botble\Ecommerce\Events\OrderPlacedEvent;

class MpesaPaymentService
{
    protected $businessShortcode;
    protected $tillNumber;
    protected $passkey;
    protected $consumerKey;
    protected $consumerSecret;
    protected $mode; // 'sandbox' or 'live'


    protected $errorCodes = [
        '1037' => [
            'description' => 'DS timeout user cannot be reached',
            'cause' => 'Applies to Lipa Na M-PESA API. It means that the STK Push Prompt never got to the user.',
            'solution' => 'Update SIM card via *234*1*6# or Upgrade SIM card and perform a SIM swap/replacement at Safaricom Agent shop. Make sure the target SIM card\'s mobile phone is online.'
        ],
        '1025' => [
            'description' => 'An error occurred while sending a push request',
            'cause' => 'System error or USSD message from M-PESA is too long (more than 182 characters)',
            'solution' => 'Retry the request, and make sure your system is working as expected. Ensure the messaging is less than 182 characters.'
        ],
        '9999' => [
            'description' => 'An error occurred while sending a push request.',
            'cause' => 'Unknown',
            'solution' => 'Retry the request'
        ],
        '1032' => [
            'description' => 'The request was canceled by the user',
            'cause' => 'STK Prompt timed out or user canceled the request on their phone.',
            'solution' => 'Inform the user that they did not respond or just cancel the transaction, then try again.'
        ],
        '1' => [
            'description' => 'The balance is insufficient for the transaction.',
            'cause' => 'Subscriber has declined using Overdraft or has insufficient funds on M-PESA.',
            'solution' => 'Advise customer to deposit funds on their M-PESA or use Overdraft(Fuliza) when prompted'
        ],
        '2001' => [
            'description' => 'The initiator information is invalid.',
            'cause' => 'The user initiating the push has given invalid password input or entered the wrong pin to validate the STKPUSH request.',
            'solution' => 'Use correct user credentials. Subscriber to key in the correct M-PESA pin'
        ],
        '1019' => [
            'description' => 'Transaction has expired',
            'cause' => 'The transaction has taken a long time before being processed within the allowable time',
            'solution' => 'Retry the transaction'
        ],
        '1001' => [
            'description' => 'Unable to lock subscriber, a transaction is already in process for the current subscriber',
            'cause' => 'Duplicated MSISDN, MSISDN has an existing USSD session, conflicting of sessions, ongoing USSD Session, or supplementary services are barred for user.',
            'solution' => 'Close the session before initiating another push. User to try again between 2-3 minutes. Make sure you send one push request to a user at a time. User to contact Safaricom for unbarring.'
        ]
    ];

    public function __construct($regionId = null)
    {
        if ($regionId !== null) {
            // Get region-specific settings from the database
            $regionSettings = DB::table('mpesa_region_settings')
                ->where('region_id', $regionId)
                ->first();

            // If we have region-specific settings, use them
            if ($regionSettings) {
                $this->businessShortcode = $regionSettings->business_shortcode;
                $this->tillNumber = $regionSettings->business_shortcode;
                $this->passkey = $regionSettings->passkey;
                $this->consumerKey = $regionSettings->consumer_key;
                $this->consumerSecret = $regionSettings->consumer_secret;

                // Convert payment_mode ('Sandbox'/'Production') to mode ('sandbox'/'live')
                $this->mode = strtolower($regionSettings->payment_mode);

                Log::info("Using region-specific M-Pesa settings for region {$regionId}. Mode: {$this->mode}");
                return;
            }
        }

        // Fall back to global settings
        $this->businessShortcode = setting('mpesa_business_shortcode', '137146');
        $this->tillNumber = setting('mpesa_till_number', $this->businessShortcode);
        $this->passkey = setting('mpesa_passkey');
        $this->consumerKey = setting('mpesa_consumer_key');
        $this->consumerSecret = setting('mpesa_consumer_secret');
        $this->mode = env('MPESA_MODE', setting('mpesa_mode') == 1 ? 'live' : 'sandbox');
        Log::info("Using global M-Pesa settings. Mode: {$this->mode}");
    }

    protected function getRegionIdFromPaymentData(array $paymentData)
    {
        // Extract state/region name from address
        $state = $paymentData['address']['state'] ?? null;

        if (!$state) {
            return null;
        }

        // Lookup region ID from a regions table or mapping
        // This is a simplified example - real implementation would depend on your regions data structure
        $regionMapping = [
            'Nakuru' => 31,
            'Nairobi City' => 30,
            'Machakos' => 22,
            'Kirinyaga' => 15,
            'Kajiado' => 10,
        ];

        return $regionMapping[$state] ?? null;
    }

    protected function generatePassword($timestamp)
    {
        $stringToEncode = $this->businessShortcode . $this->passkey . $timestamp;
        return base64_encode($stringToEncode);
    }

    protected function standardizePhoneNumber($phone)
    {
        $trimmedPhone = trim($phone);
        $cleanedPhone = preg_replace('/\D/', '', $trimmedPhone);

        if (strpos($cleanedPhone, '0') === 0) {
            $cleanedPhone = '254' . substr($cleanedPhone, 1);
        }

        if (strpos($cleanedPhone, '254') === 0) {
            return $cleanedPhone;
        }

        return '254' . $cleanedPhone;
    }

    protected function getAccessToken()
    {
        $client = new Client();
        $url = $this->mode === 'live'
            ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        try {
            Log::info("M-Pesa Auth: Using consumer credentials", [
                'consumerKeyValue' => substr($this->consumerKey, 0, 7) . '...',
                'mode' => $this->mode,
            ]);

            $credentials = base64_encode("{$this->consumerKey}:{$this->consumerSecret}");
            $response = $client->request('GET', $url, [
                'headers' => ['Authorization' => 'Basic ' . $credentials],
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $body = json_decode($response->getBody(), true);

            if (!isset($body['access_token'])) {
                Log::error('M-Pesa access token not found in response:', $body);
                throw new Exception('Access token not found in response');
            }

            return $body['access_token'];
        } catch (RequestException $e) {
            $response = $e->hasResponse() ? $e->getResponse() : null;
            $responseBody = $response ? (string) $response->getBody() : null;

            Log::error('Error creating M-Pesa STK push request', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'response_status' => $response ? $response->getStatusCode() : null,
                'response_body' => $responseBody ? json_decode($responseBody, true) : null,
                'request' => $e->getRequest() ? [
                    'method' => $e->getRequest()->getMethod(),
                    'url' => (string) $e->getRequest()->getUri(),
                ] : null
            ]);
            throw new Exception('Unable to get M-Pesa access token');
        } catch (Exception $e) {
            Log::error('Unexpected error getting M-Pesa access token: ' . $e->getMessage());
            throw new Exception('Unable to get M-Pesa access token');
        }
    }

    public function createStkPushRequest(array $paymentData)
    {
        $regionId = $this->getRegionIdFromPaymentData($paymentData);

        if ($regionId) {
            $this->__construct($regionId);
        }

        if (!isset($paymentData['amount'], $paymentData['address']['phone'], $paymentData['order_id'], $paymentData['description'])) {
            Log::error('Missing required fields in payment data', $paymentData);
            return [
                'success' => false,
                'message' => 'Missing required fields in payment data',
            ];
        }

        $timestamp = now()->format('YmdHis');
        $password = $this->generatePassword($timestamp);

        try {
            $accessToken = $this->getAccessToken();
        } catch (Exception $e) {
            Log::error('Failed to get M-Pesa access token: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get M-Pesa access token. Please try again later.',
            ];
        }

        $url = $this->mode === 'live'
            ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $roundedAmount = $this->roundUpAmount($paymentData['amount']);
        $phonenumber = $this->standardizePhoneNumber($paymentData['address']['phone']);

        $payload = [
            'BusinessShortCode' => $this->businessShortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerBuyGoodsOnline',
            'Amount' => $roundedAmount,
            'PartyA' => $phonenumber,
            'PartyB' => $this->tillNumber,
            'PhoneNumber' => $phonenumber,
            'CallBackURL' => route('payments.mpesa.callback'),
            'AccountReference' => $paymentData['order_id'],
            'TransactionDesc' => $paymentData['description'],
        ];

        Log::info('M-Pesa STK Push Payload: ', $payload);
        Log::info('M-Pesa STK Push URL: ' . $url);

        $client = new Client();

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['ResponseCode']) && $result['ResponseCode'] !== '0') {
                return $this->handleError($result['ResponseCode']);
            }

            DB::transaction(function () use ($paymentData, $result, $phonenumber) {
                $orderId = is_array($paymentData['order_id']) ? $paymentData['order_id'][0] : $paymentData['order_id'];
                $order = Order::findOrFail($orderId);
                $order->checkout_request_id = $result['CheckoutRequestID'];
                $order->save();

                Payment::create([
                    'order_id' => $order->id,
                    'currency' => 'KES',
                    'amount' => $paymentData['amount'],
                    'payment_channel' => 'mpesa',
                    'status' => PaymentStatusEnum::PENDING,
                    'charge_id' => $result['CheckoutRequestID'],
                    'description' => 'M-Pesa payment for order #' . $order->id,
                    'customer_id' => $order->user_id,
                    'metadata' => json_encode([
                        'checkout_request_id' => $result['CheckoutRequestID'],
                        'amount' => $paymentData['amount'],
                        'phone_number' => $phonenumber,
                    ]),
                ]);

                // Dispatch OrderPlacedEvent
                event(new OrderPlacedEvent($order));
            });

            Log::info('STK Push Request Successful:', $result);

            // Return the result without redirecting the user
            return [
                'success' => true,
                'message' => 'STK Push request accepted for processing. Waiting for payment confirmation.',
                'data' => $result,
            ];
        } catch (Exception $e) {
            Log::error('Error creating M-Pesa STK push request: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An unexpected error occurred while processing the payment. Please try again later.',
                'data' => $e->getMessage(),
            ];
        }
    }

    public function createPackageStkPushRequest(
        float|int $amount,
        string $phone,
        string $reference,
        string $description,
        string $callbackUrl
    ): array {
        try {
            $accessToken = $this->getAccessToken();
            $timestamp = now()->format('YmdHis');
            $phone = $this->standardizePhoneNumber($phone);
            $url = $this->mode === 'live'
                ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
                : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

            $response = (new Client())->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'BusinessShortCode' => $this->businessShortcode,
                    'Password' => $this->generatePassword($timestamp),
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerBuyGoodsOnline',
                    'Amount' => $this->roundUpAmount($amount),
                    'PartyA' => $phone,
                    'PartyB' => $this->tillNumber,
                    'PhoneNumber' => $phone,
                    'CallBackURL' => $callbackUrl,
                    'AccountReference' => $reference,
                    'TransactionDesc' => $description,
                ],
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $result = json_decode($response->getBody(), true);

            if (($result['ResponseCode'] ?? null) !== '0') {
                return [
                    'success' => false,
                    'message' => $result['errorMessage'] ?? $result['ResponseDescription'] ?? 'M-Pesa rejected the request.',
                    'data' => $result,
                ];
            }

            return [
                'success' => true,
                'message' => 'STK Push sent. Enter your M-Pesa PIN on your phone.',
                'data' => $result,
                'phone' => $phone,
            ];
        } catch (Exception $exception) {
            Log::error('Package M-Pesa STK Push failed', [
                'reference' => $reference,
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Unable to send the M-Pesa request. Please try again.',
            ];
        }
    }

    public function queryStkPushStatus(string $checkoutRequestId): array
    {
        try {
            $timestamp = now()->format('YmdHis');
            $url = $this->mode === 'live'
                ? 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query'
                : 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';

            $response = (new Client())->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'BusinessShortCode' => $this->businessShortcode,
                    'Password' => $this->generatePassword($timestamp),
                    'Timestamp' => $timestamp,
                    'CheckoutRequestID' => $checkoutRequestId,
                ],
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            return json_decode($response->getBody(), true) ?: [];
        } catch (RequestException $exception) {
            $body = $exception->hasResponse() ? (string) $exception->getResponse()->getBody() : null;

            return [
                'error' => true,
                'message' => $exception->getMessage(),
                'response' => $body ? json_decode($body, true) : null,
            ];
        }
    }


    protected function roundUpAmount($amount)
    {
        return (int) ceil($amount);
    }

    protected function handleError($errorCode)
    {
        $errorInfo = $this->errorCodes[$errorCode] ?? [
            'description' => 'Unknown error',
            'cause' => 'Unspecified',
            'solution' => 'Please contact support for assistance.',
        ];

        Log::error("M-Pesa Error [Code: $errorCode]: " . $errorInfo['description'], [
            'cause' => $errorInfo['cause'],
            'solution' => $errorInfo['solution'],
        ]);

        return [
            'error' => true,
            'code' => $errorCode,
            'message' => $errorInfo['description'],
            'cause' => $errorInfo['cause'],
            'solution' => $errorInfo['solution'],
        ];
    }

    public function getPaymentStatus($request)
    {
        $resultCode = $request->input('ResultCode');
        if ($resultCode == 0) {
            return PaymentStatusEnum::COMPLETED;
        } elseif ($resultCode > 0) {
            $this->handleError($resultCode);
            return PaymentStatusEnum::FAILED;
        } else {
            return PaymentStatusEnum::PENDING;
        }
    }

    public function afterPayment($request)
    {
        $paymentStatus = $this->getPaymentStatus($request);
        switch ($paymentStatus) {
            case PaymentStatusEnum::COMPLETED:
                $response['message'] = 'Payment Success';
                break;
            case PaymentStatusEnum::CANCELED:
                $response['message'] = 'Payment Failed';
                break;
            case PaymentStatusEnum::PENDING:
                $response['message'] = 'Payment Pending';
                break;
        }

        return $response;
    }

    public function supportedCurrencyCodes(): array
    {
        return ['KES'];
    }

    public function storedData(array $data): void
    {
        $chargeId = $data['orderId'];
        $order = Order::find($data['extraData']);

        if ($order !== NULL) {
            $customer = $order->user;
            do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                'amount' => $data['amount'],
                'currency' => 'KES',
                'charge_id' => $chargeId,
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'customer_type' => get_class($customer),
                'payment_channel' => MPESA_PAYMENT_METHOD_NAME,
                'status' => $data['resultCode'] == '0' ? PaymentStatusEnum::COMPLETED : PaymentStatusEnum::PENDING,
            ]);
        }
    }
}
