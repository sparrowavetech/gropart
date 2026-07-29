<?php

namespace Botble\Sms\Http\Controllers\Fronts;

use Botble\Ecommerce\Http\Controllers\Customers\RegisterController as BaseRegisterController;
use Botble\Ecommerce\Http\Requests\RegisterRequest;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Sms\Supports\SmsHandler;
use Botble\Sms\Enums\SmsEnum;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;

class SmsOtpRegisterController extends BaseRegisterController
{
    /**
     * Override register function to redirect to verification screen when OTP is enabled.
     */
    public function register(RegisterRequest $request)
    {
        abort_unless(EcommerceHelper::isCustomerRegistrationEnabled(), 404);

        do_action('customer_register_validation', $request);

        $customer = $this->create($request->input());

        event(new Registered($customer));

        if (is_plugin_active('sms') && setting('sms_otp_enabled')) {
            $otp = mt_rand(100000, 999999);
            $customer->otp = $otp;
            $customer->save();
            
            try {
                $sms = new SmsHandler();
                $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
                if ($sms->templateEnabled(SmsEnum::OTP())) {
                    $sms->setVariableValues([
                        'customer_name' => $customer->name,
                        'otp'           => $otp,
                    ]);
                    $sms->sendUsingTemplate(
                        SmsEnum::OTP(),
                        $customer->phone
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send registration OTP: ' . $e->getMessage());
            }

            $this->registered($request, $customer);

            return $this
                ->httpResponse()
                ->setNextUrl(route('customer.otp', $customer->id))
                ->setMessage(__('We have sent you an OTP to verify your mobile. Please check and confirm your mobile No!'));
        }

        if (
            EcommerceHelper::isEnableEmailVerification() &&
            (! EcommerceHelper::isLoginUsingPhone() || get_ecommerce_setting('keep_email_field_in_registration_form', true))
        ) {
            $this->registered($request, $customer);

            session()->flash('ecommerce_customer_registered', true);

            $message = __('We have sent you an email to verify your email. Please check and confirm your email address!');

            return $this
                ->httpResponse()
                ->setNextUrl(route('customer.login'))
                ->with(['auth_warning_message' => $message])
                ->setMessage($message);
        }

        $customer->confirmed_at = Carbon::now();
        $customer->save();

        $this->guard()->login($customer);

        session()->flash('ecommerce_customer_registered', true);

        return $this
            ->httpResponse()
            ->setNextUrl($this->redirectPath())
            ->setMessage(__('Registered successfully!'));
    }
}
