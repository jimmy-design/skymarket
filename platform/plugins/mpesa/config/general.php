<?php

return [
    'sandbox' => env('MPESA_DOMAIN_API_SANDBOX', 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'),
    'production' => env('MPESA_DOMAIN_API_PRODUCTION', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'),
];
