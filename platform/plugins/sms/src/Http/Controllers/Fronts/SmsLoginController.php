<?php

namespace Botble\Sms\Http\Controllers\Fronts;

use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Http\Controllers\Customers\LoginController;
use Botble\Ecommerce\Http\Requests\LoginRequest;
use Botble\Sms\Supports\RegistrationOtpSender;
use Illuminate\Http\Exceptions\HttpResponseException;

class SmsLoginController extends LoginController
{
    protected function attemptLogin(LoginRequest $request): bool
    {
        $credentials = $this->credentials($request);
        $customer = $this->guard()->validate($credentials)
            ? $this->guard()->getLastAttempted()
            : $this->findCustomerByPhoneWithoutCountryCode($credentials);

        if ($customer
            && $customer->status->getValue() === CustomerStatusEnum::ACTIVATED
            && $customer->sms_otp_required_at
            && ! $customer->sms_otp_verified_at) {
            $sent = app(RegistrationOtpSender::class)->send($customer);

            throw new HttpResponseException(redirect()->route('customer.otp')->with(
                $sent ? 'success_msg' : 'error_msg',
                trans($sent ? 'plugins/sms::sms.registration_otp_sent' : 'plugins/sms::sms.otp_send_failed')
            ));
        }

        return parent::attemptLogin($request);
    }
}
