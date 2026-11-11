<?php

namespace SparroWave\RazorpayEasyEmi\Http\Requests;

use Botble\Support\Http\Requests\Request;

class VendorEmiRequestSubmissionRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:ec_products,id'],
            'requested_scheme' => ['required', 'string', 'in:standard,no_cost,low_cost'],
            'subvention_willingness' => ['nullable', 'string', 'in:seller,platform'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

