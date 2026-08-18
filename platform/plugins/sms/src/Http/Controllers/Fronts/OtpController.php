<?php

namespace Botble\Sms\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Customer;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Botble\Sms\Supports\SmsHandler;
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
    public function otp($customer_id)
    {
        SeoHelper::setTitle(__('OTP Verification'));
        $customer = Customer::query()->find($customer_id);

        if (!$customer) {
            abort(404, 'Customer not found.');
        }

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('OTP Verify'), route('customer.register'));

        if (!session()->has('url.intended')) {
            if (!in_array(url()->previous(), [route('customer.login'), route('customer.register')])) {
                session(['url.intended' => url()->previous()]);
            }
        }

        return Theme::scope('ecommerce.customers.otp', compact('customer_id', 'customer'), 'plugins/sms::themes.customers.otp')
            ->render();
    }

    /**
     * Verify the customer OTP.
     */
    public function verifyotp(OtpVerifyRequest $request, BaseHttpResponse $response)
    {
        $customer = Customer::query()->find($request->customer_id);

        if ($customer && $customer->otp == $request->otp) {
            $customer->confirmed_at = Carbon::now();
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
                    \Illuminate\Support\Facades\Log::error('Failed to send welcome SMS: ' . $e->getMessage());
                }
            }

            return $response
                ->setNextUrl(route('customer.login'))
                ->setMessage(trans('plugins/sms::sms.otp_verify_success'));
        } else {
            throw ValidationException::withMessages([
                'confirmation' => trans('plugins/sms::sms.otp_verify_error'),
            ]);
        }
    }

    /**
     * Resend verification OTP code.
     */
    public function resend(BaseHttpResponse $response, $id)
    {
        $customer = Customer::query()->find($id);

        if (!$customer) {
            abort(404, 'Customer not found.');
        }

        if (is_plugin_active('sms') && setting('sms_registration_otp_enabled', setting('sms_otp_enabled'))) {
            $otp = mt_rand(100000, 999999);
            $customer->otp = $otp;
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
                    $sms->sendUsingTemplate(SmsEnum::OTP(), $customer->phone);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send resend OTP SMS: ' . $e->getMessage());
            }

            return $response
                ->setNextUrl(route('customer.otp', $customer->id))
                ->setMessage(__('We have resent you a new OTP.'));
        }

        return $response->setMessage(__('SMS OTP is not enabled.'));
    }

    /**
     * Change phone number and send a new verification OTP.
     */
    public function changePhone(OtpChangePhoneRequest $request, BaseHttpResponse $response)
    {
        $customer = Customer::query()->find($request->customer_id);

        if (!$customer) {
            abort(404, 'Customer not found.');
        }

        $customer->phone = $request->phone;
        
        if (is_plugin_active('sms') && setting('sms_registration_otp_enabled', setting('sms_otp_enabled'))) {
            $otp = mt_rand(100000, 999999);
            $customer->otp = $otp;
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
                    $sms->sendUsingTemplate(SmsEnum::OTP(), $customer->phone);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send new phone OTP SMS: ' . $e->getMessage());
            }

            return $response
                ->setNextUrl(route('customer.otp', $customer->id))
                ->setMessage(__('Phone number changed and new OTP sent.'));
        }

        $customer->save();
        return $response->setMessage(__('Phone number updated successfully.'));
    }
}
