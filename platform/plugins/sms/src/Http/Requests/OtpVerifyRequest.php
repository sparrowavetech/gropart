<?php

namespace Botble\Sms\Http\Requests;

use Botble\Support\Http\Requests\Request;

class OtpVerifyRequest extends Request
{
    public function rules(): array
    {
        return [
            'otp'         => 'required|numeric|digits:6',
            'customer_id' => 'required|numeric',
        ];
    }
}
