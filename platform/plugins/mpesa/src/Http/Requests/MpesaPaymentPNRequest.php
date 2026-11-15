<?php

namespace Njovu\Mpesa\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MpesaPaymentPNRequest extends FormRequest
{
    /**
     * Define validation rules for the Mpesa IPN request.
     *
     * @return array
     */
    public function rules(): array
    {
        // Define validation rules for the IPN data, if needed.
        return [
            'TransactionType' => 'required|string',
            'TransID' => 'required|string',
            'TransTime' => 'required|string',
            'TransAmount' => 'required|numeric',
            'BusinessShortCode' => 'required|string',
            'BillRefNumber' => 'nullable|string',
            'InvoiceNumber' => 'nullable|string',
            'OrgAccountBalance' => 'nullable|numeric',
            'ThirdPartyTransID' => 'nullable|string',
            'MSISDN' => 'required|string',
            'FirstName' => 'nullable|string',
            'MiddleName' => 'nullable|string',
            'LastName' => 'nullable|string',
        ];
    }
}
