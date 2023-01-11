<?php

namespace Botble\Ecommerce\Http\Requests;

use EcommerceHelper;
use Botble\Support\Http\Requests\Request;

class OTPRequest extends Request
{
    public function rules(): array
    {
        return [
            'otp' => 'required|numeric|min:6',
        ];
    }
}
