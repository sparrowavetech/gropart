<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Botble\Ecommerce\Models\Customer;

class ChangePhoneRequest extends Request
{
    public function rules(): array
    {
        return [
            'phone' => [
                'nullable',
                Rule::requiredIf(EcommerceHelper::isLoginUsingPhone()),
                ...explode('|', BaseHelper::getPhoneValidationRule()),
                Rule::unique((new Customer())->getTable(), 'phone'),
                'min:10',
                'max:10',
            ],
        ];
    }
}
