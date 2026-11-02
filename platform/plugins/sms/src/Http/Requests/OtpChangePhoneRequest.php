<?php

namespace Botble\Sms\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Botble\Ecommerce\Models\Customer;
use Botble\Sms\Supports\OtpChallenge;

class OtpChangePhoneRequest extends Request
{
    public function authorize(): bool
    {
        return app(OtpChallenge::class)->customerId('registration') !== null;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                ...explode('|', BaseHelper::getPhoneValidationRule()),
                Rule::unique((new Customer())->getTable(), 'phone'),
                'max:20',
            ],
        ];
    }
}
