<?php

namespace Botble\Sms\Http\Controllers\Fronts;

use Botble\Ecommerce\Http\Controllers\Customers\RegisterController as BaseRegisterController;
use Botble\Ecommerce\Http\Requests\RegisterRequest;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Events\CustomerEmailVerified;
use Botble\Ecommerce\Models\Customer;
use Botble\Sms\Supports\RegistrationOtpSender;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

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

        $otpRequired = is_plugin_active('sms') && setting('sms_registration_otp_enabled', setting('sms_otp_enabled'));
        if ($otpRequired) {
            $customer->sms_otp_required_at = Carbon::now();
            $customer->save();
        }

        event(new Registered($customer));

        if ($otpRequired) {
            $sent = app(RegistrationOtpSender::class)->send($customer);

            $this->registered($request, $customer);

            return $this
                ->httpResponse()
                ->setNextUrl(route('customer.otp'))
                ->setError(! $sent)
                ->setMessage($sent ? trans('plugins/sms::sms.registration_otp_sent') : trans('plugins/sms::sms.otp_send_failed'));
        }

        if (
            EcommerceHelper::isEnableEmailVerification() &&
            (! EcommerceHelper::isLoginUsingPhone() || get_ecommerce_setting('keep_email_field_in_registration_form', true))
        ) {
            $this->registered($request, $customer);

            session()->flash('ecommerce_customer_registered', true);

            $message = trans('plugins/sms::sms.verify_email_sent');

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
            ->setMessage(trans('plugins/sms::sms.registered_successfully'));
    }

    public function confirm(int|string $id, Request $request)
    {
        if (! URL::hasValidSignature($request)) {
            return parent::confirm($id, $request);
        }

        $customer = Customer::query()->findOrFail($id);

        if ($customer->sms_otp_required_at && ! $customer->sms_otp_verified_at) {
            if (! $customer->confirmed_at) {
                $customer->confirmed_at = Carbon::now();
                $customer->save();
                CustomerEmailVerified::dispatch($customer);
            }

            $sent = app(RegistrationOtpSender::class)->send($customer);

            return redirect()->route('customer.otp')->with(
                $sent ? 'success_msg' : 'error_msg',
                trans($sent ? 'plugins/sms::sms.registration_otp_sent' : 'plugins/sms::sms.otp_send_failed')
            );
        }

        session()->forget('sms.registration.email_pending_customer_id');

        return parent::confirm($id, $request);
    }

    public function showEmailPending()
    {
        $customerId = session('sms.registration.email_pending_customer_id');
        abort_unless($customerId && Customer::query()
            ->whereKey($customerId)
            ->whereNotNull('sms_otp_verified_at')
            ->whereNull('confirmed_at')
            ->exists(), 404);

        SeoHelper::setTitle(trans('plugins/sms::sms.email_pending_title'));

        return Theme::of('plugins/sms::themes.customers.email-verification-pending')->render();
    }
}
