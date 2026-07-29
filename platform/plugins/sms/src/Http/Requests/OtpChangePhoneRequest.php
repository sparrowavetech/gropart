<?php

namespace Botble\Sms\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Botble\Ecommerce\Models\Customer;

class OtpChangePhoneRequest extends Request
{
    public function rules(): array
    {
        return [
            'customer_id' => 'required|numeric',
            'phone' => [
                'required',
                'string',
                ...explode('|', BaseHelper::getPhoneValidationRule()),
                Rule::unique((new Customer())->getTable(), 'phone'),
                'min:10',
                'max:10',
            ],
        ];
    }
}
