<?php

namespace Botble\Sms\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Botble\Sms\Supports\SmsHandler;
use Botble\Sms\Supports\SmsGatewayLog;
use Botble\Sms\Supports\OtpChallenge;
use Botble\Sms\Enums\SmsEnum;
use Botble\Sms\Http\Requests\OtpVerifyRequest;
use Botble\Sms\Http\Requests\OtpChangePhoneRequest;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class OtpController extends BaseController
{
    /**
     * Show OTP verification screen.
     */
    public function otp()
    {
        $customer = Customer::query()->find(app(OtpChallenge::class)->customerId('registration'));

        if (!$customer) {
            abort(404, 'Customer not found.');
        }
        abort_unless(app(OtpChallenge::class)->belongs('registration', $customer), 404);
        SeoHelper::setTitle(trans('plugins/sms::sms.otp_page_title'));

        Theme::breadcrumb()->add(trans('plugins/sms::sms.home'), route('public.index'))->add(trans('plugins/sms::sms.otp_verify_breadcrumb'), route('customer.register'));

        if (!session()->has('url.intended')) {
            if (!in_array(url()->previous(), [route('customer.login'), route('customer.register')])) {
                session(['url.intended' => url()->previous()]);
            }
        }

        return Theme::of('plugins/sms::themes.customers.otp', compact('customer'))
            ->render();
    }

    /**
     * Verify the customer OTP.
     */
    public function verifyotp(OtpVerifyRequest $request, BaseHttpResponse $response)
    {
        $customer = Customer::query()->find(app(OtpChallenge::class)->customerId('registration'));

        if ($customer
            && $customer->status->getValue() === CustomerStatusEnum::ACTIVATED
            && app(OtpChallenge::class)->verify('registration', $customer, (string) $request->otp)) {
            if (! \Botble\Ecommerce\Facades\EcommerceHelper::isEnableEmailVerification()) {
                $customer->confirmed_at = Carbon::now();
            }
            $customer->sms_otp_verified_at = Carbon::now();
            $customer->otp = null; // Clear OTP upon successful verification
            $customer->save();

            if (is_plugin_active('sms') && setting('sms_registration_otp_enabled', setting('sms_otp_enabled'))) {
                try {
                    $sms = new SmsHandler();
                    $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
                    if ($sms->templateEnabled(SmsEnum::WELCOME())) {
                        $sms->setVariableValues([
                            'customer_name' => $customer->name,
                            'customer_phone' => $customer->phone,
                            'site_title'    => setting('admin_title') ?: config('app.name'),
                        ]);
                        $sms->sendUsingTemplate(SmsEnum::WELCOME(), $customer->phone);
                    }
                } catch (\Exception $e) {
                    SmsGatewayLog::error('Failed to send welcome SMS.');
                }
            }

            if (! \Botble\Ecommerce\Facades\EcommerceHelper::isEnableEmailVerification() || $customer->confirmed_at) {
                session()->forget('sms.registration.email_pending_customer_id');
                auth('customer')->login($customer);
                $request->session()->regenerate();

                return $response
                    ->setNextUrl(route('customer.overview'))
                    ->setMessage(trans('plugins/sms::sms.otp_verify_success'));
            }

            session(['sms.registration.email_pending_customer_id' => $customer->getKey()]);

            return $response
                ->setNextUrl(route('customer.register.email-pending'))
                ->setMessage(trans('plugins/sms::sms.otp_verified_check_email'));
        } else {
            throw ValidationException::withMessages([
                'confirmation' => trans('plugins/sms::sms.otp_verify_error'),
            ]);
        }
    }

    /**
     * Resend verification OTP code.
     */
    public function resend(BaseHttpResponse $response)
    {
        $customer = Customer::query()->find(app(OtpChallenge::class)->customerId('registration'));

        if (!$customer) {
            abort(404, 'Customer not found.');
        }
        abort_unless(app(OtpChallenge::class)->belongs('registration', $customer), 404);

        if (is_plugin_active('sms') && setting('sms_registration_otp_enabled', setting('sms_otp_enabled'))) {
            $otp = (string) random_int(100000, 999999);
            $challenge = app(OtpChallenge::class);
            $previous = session('sms.otp.registration');
            $challenge->begin('registration', $customer, $otp);
            try {
                $sms = new SmsHandler();
                $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
                if ($sms->templateEnabled(SmsEnum::OTP())) {
                    $sms->setVariableValues([
                        'customer_name' => $customer->name,
                        'customer_phone' => $customer->phone,
                        'otp'           => $otp,
                    ]);
                    $sent = $sms->sendUsingTemplate(SmsEnum::OTP(), $customer->phone);
                } else {
                    $sent = false;
                }
            } catch (\Exception $e) {
                SmsGatewayLog::error('Failed to send resend OTP SMS.');
                $sent = false;
            }
            if (! $sent) {
                $challenge->restore('registration', $previous);
            }

            return $response
                ->setNextUrl(route('customer.otp'))
                ->setError(! $sent)
                ->setMessage($sent ? trans('plugins/sms::sms.resend_success') : trans('plugins/sms::sms.otp_send_failed'));
        }

        return $response->setMessage(trans('plugins/sms::sms.otp_disabled'));
    }

    /**
     * Change phone number and send a new verification OTP.
     */
    public function changePhone(OtpChangePhoneRequest $request, BaseHttpResponse $response)
    {
        $customer = Customer::query()->find(app(OtpChallenge::class)->customerId('registration'));

        if (!$customer) {
            abort(404, 'Customer not found.');
        }
        abort_unless(app(OtpChallenge::class)->belongs('registration', $customer), 404);

        if (is_plugin_active('sms') && setting('sms_registration_otp_enabled', setting('sms_otp_enabled'))) {
            $otp = (string) random_int(100000, 999999);
            $challenge = app(OtpChallenge::class);
            $previous = session('sms.otp.registration');
            $challenge->begin('registration', $customer, $otp);
            $oldPhone = $customer->phone;
            $customer->phone = $request->phone;
            $customer->save();

            try {
                $sms = new SmsHandler();
                $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
                if ($sms->templateEnabled(SmsEnum::OTP())) {
                    $sms->setVariableValues([
                        'customer_name' => $customer->name,
                        'customer_phone' => $customer->phone,
                        'otp'           => $otp,
                    ]);
                    $sent = $sms->sendUsingTemplate(SmsEnum::OTP(), $customer->phone);
                } else {
                    $sent = false;
                }
            } catch (\Exception $e) {
                SmsGatewayLog::error('Failed to send new phone OTP SMS.');
                $sent = false;
            }
            if (! $sent) {
                $customer->phone = $oldPhone;
                $customer->save();
                $challenge->restore('registration', $previous);
            }

            return $response
                ->setNextUrl(route('customer.otp'))
                ->setError(! $sent)
                ->setMessage($sent ? trans('plugins/sms::sms.phone_changed_otp_sent') : trans('plugins/sms::sms.otp_send_failed'));
        }

        abort(404);
    }
}
