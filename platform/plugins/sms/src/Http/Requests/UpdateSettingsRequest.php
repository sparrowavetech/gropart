<?php

namespace Botble\Sms\Http\Requests;

use BaseHelper;
use Botble\Support\Http\Requests\Request;

class UpdateSettingsRequest extends Request
{
    public function rules(): array
    {
        return [
            'sms_base_api_url' => 'required|string',
            'sms_user' => 'required|string',
            'sms_password' => 'required|string',
            'sms_send_api_call' => 'required|string',
            'sms_sender_id' => 'nullable|string',
            'sms_channel' => 'nullable|string',
            'sms_dcs' => 'nullable|string',
            'sms_flashsms' => 'nullable|string',
            'sms_route' => 'nullable|string',
            'sms_dlt_template_id' => 'nullable|string',
            'sms_peid' => 'nullable|string',
            'sms_delivery_api_call' => 'required|string',
            'sms_balance_api_call' => 'required|string',
            'sms_login_otp_enabled' => 'nullable|boolean',
            'sms_registration_otp_enabled' => 'nullable|boolean',
        ];
    }
}
