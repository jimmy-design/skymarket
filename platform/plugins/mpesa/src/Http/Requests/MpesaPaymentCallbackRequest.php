<?php

namespace Njovu\Mpesa\Http\Requests;

use Botble\Support\Http\Requests\Request;

class MpesaPaymentCallbackRequest extends Request
{
    /**
     * Define validation rules for the Mpesa payment callback request.
     *
     * @return array
     */
    public function rules(): array
    {
        // Define validation rules for the callback data.
        return [
            'Body.stkCallback.MerchantRequestID' => 'required|string',
            'Body.stkCallback.CheckoutRequestID' => 'required|string',
            'Body.stkCallback.ResultCode' => 'required|integer',
            'Body.stkCallback.ResultDesc' => 'required|string',
            'Body.stkCallback.CallbackMetadata.Item.*.Name' => 'sometimes|required|string',
            'Body.stkCallback.CallbackMetadata.Item.*.Value' => 'sometimes|required',
        ];
    }
}
