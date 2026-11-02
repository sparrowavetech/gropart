<?php

namespace Botble\Sms\Supports;

use Botble\Ecommerce\Models\Customer;
use Botble\Sms\Enums\SmsEnum;

class RegistrationOtpSender
{
    public function send(Customer $customer): bool
    {
        $otp = (string) random_int(100000, 999999);
        app(OtpChallenge::class)->begin('registration', $customer, $otp);

        try {
            $sms = new SmsHandler();
            $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            $sms->setVariableValues([
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'otp' => $otp,
            ]);

            return $sms->templateEnabled(SmsEnum::OTP())
                && $sms->sendUsingTemplate(SmsEnum::OTP(), $customer->phone);
        } catch (\Throwable) {
            SmsGatewayLog::error('Failed to send registration OTP.');

            return false;
        }
    }
}
