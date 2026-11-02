<?php

namespace Botble\Sms\Http\Requests;

use Botble\Support\Http\Requests\Request;

class OtpVerifyRequest extends Request
{
    public function rules(): array
    {
        return [
            'otp'         => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'otp.required' => trans('plugins/sms::sms.otp_six_digits_required'),
            'otp.string' => trans('plugins/sms::sms.otp_six_digits_required'),
            'otp.regex' => trans('plugins/sms::sms.otp_six_digits_required'),
        ];
    }
}
