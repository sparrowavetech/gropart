<?php

namespace Botble\Sms\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Sms\Enums\SmsEnum;
use Botble\Sms\Supports\SmsHandler;
use Botble\Sms\Supports\OtpChallenge;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Botble\Sms\Supports\SmsGatewayLog;
use Illuminate\Validation\ValidationException;

class LoginOtpController extends BaseController
{
    public function showRequestForm()
    {
        abort_unless(setting('sms_login_otp_enabled'), 404);

        SeoHelper::setTitle(trans('plugins/sms::sms.login_otp_title'));
        Theme::breadcrumb()->add(trans('plugins/sms::sms.home'), route('public.index'))->add(trans('plugins/sms::sms.login_otp_title'), route('customer.login.otp'));

        return Theme::of('plugins/sms::themes.customers.login-otp')->render();
    }

    public function sendOtp(Request $request)
    {
        abort_unless(setting('sms_login_otp_enabled'), 404);

        $request->validate([
            'country_code' => ['nullable', 'string', 'max:5'],
            'phone' => ['nullable', 'string', 'max:20'],
            'phone_display' => ['nullable', 'string', 'max:20'],
        ]);

        $phoneInput = (string) ($request->input('phone') ?: $request->input('phone_display'));

        if ($phoneInput === '') {
            throw ValidationException::withMessages([
                'phone' => trans('plugins/sms::sms.mobile_required'),
            ]);
        }

        $countryCode = (string) $request->input('country_code', setting('sms_default_country_code', ''));
        $phone = $this->normalizePhone($phoneInput, $countryCode);
        $customer = $this->findCustomerByPhone($phone, $countryCode);

        if (! $customer || $customer->status->getValue() !== CustomerStatusEnum::ACTIVATED) {
            throw ValidationException::withMessages([
                'phone' => trans('plugins/sms::sms.account_not_found'),
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        $challenge = app(OtpChallenge::class);
        $previous = session('sms.otp.login');
        $challenge->begin('login', $customer, $otp);
        try {
            $sms = new SmsHandler();
            $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            $sms->setVariableValues([
                'customer_name' => $customer->name,
                'customer_phone' => $phone,
                'otp' => $otp,
            ]);

            if (! $sms->templateEnabled(SmsEnum::OTP()) || ! $sms->sendUsingTemplate(SmsEnum::OTP(), $phone)) {
                $challenge->restore('login', $previous);
                throw ValidationException::withMessages(['phone' => trans('plugins/sms::sms.otp_send_failed')]);
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $challenge->restore('login', $previous);
            SmsGatewayLog::error('Failed to send login OTP.');
            throw ValidationException::withMessages(['phone' => trans('plugins/sms::sms.otp_send_failed')]);
        }

        return redirect()
            ->route('customer.login.otp.verify')
            ->with('success_msg', trans('plugins/sms::sms.login_otp_sent'));
    }

    public function showVerifyForm()
    {
        abort_unless(setting('sms_login_otp_enabled'), 404);

        $customer = Customer::query()->find(app(OtpChallenge::class)->customerId('login'));
        abort_unless($customer && app(OtpChallenge::class)->belongs('login', $customer), 404);

        SeoHelper::setTitle(trans('plugins/sms::sms.verify_login_otp'));
        Theme::breadcrumb()->add(trans('plugins/sms::sms.home'), route('public.index'))->add(trans('plugins/sms::sms.verify_login_otp'), route('customer.login.otp.verify'));

        return Theme::of('plugins/sms::themes.customers.login-otp-verify', compact('customer'))->render();
    }

    public function verify(Request $request)
    {
        abort_unless(setting('sms_login_otp_enabled'), 404);

        $request->validate([
            'otp' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ], [
            'otp.required' => trans('plugins/sms::sms.otp_six_digits_required'),
            'otp.string' => trans('plugins/sms::sms.otp_six_digits_required'),
            'otp.regex' => trans('plugins/sms::sms.otp_six_digits_required'),
        ]);

        $customer = Customer::query()->find(app(OtpChallenge::class)->customerId('login'));

        if (! $customer || $customer->status->getValue() !== CustomerStatusEnum::ACTIVATED
            || ! app(OtpChallenge::class)->verify('login', $customer, (string) $request->input('otp'))) {
            throw ValidationException::withMessages([
                'confirmation' => trans('plugins/sms::sms.otp_verify_error'),
            ]);
        }

        $customer->otp = null;
        if (! \Botble\Ecommerce\Facades\EcommerceHelper::isEnableEmailVerification()) {
            $customer->confirmed_at = $customer->confirmed_at ?: Carbon::now();
        }
        $customer->sms_otp_verified_at = Carbon::now();
        $customer->save();

        if (\Botble\Ecommerce\Facades\EcommerceHelper::isEnableEmailVerification() && ! $customer->confirmed_at) {
            return redirect()->route('customer.login')->with(
                'auth_warning_message',
                trans('plugins/sms::sms.otp_verified_check_email')
            );
        }

        auth('customer')->login($customer);

        return redirect()->intended(route('customer.overview'));
    }

    private function normalizePhone(string $phone, string $countryCode): string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?: $phone;
        $countryCode = preg_replace('/\D+/', '', $countryCode) ?: '';

        if ($countryCode === '') {
            return $phone;
        }

        if (str_starts_with($phone, $countryCode)) {
            return $phone;
        }

        return $countryCode . ltrim($phone, '0');
    }

    private function findCustomerByPhone(string $phone, string $countryCode): ?Customer
    {
        $phone = preg_replace('/\D+/', '', $phone) ?: $phone;
        $countryCode = preg_replace('/\D+/', '', $countryCode) ?: '';
        $fullPhone = $countryCode !== '' && ! str_starts_with($phone, $countryCode)
            ? $countryCode . ltrim($phone, '0')
            : $phone;

        // Registration stores international values such as +91..., while form input may omit +.
        // Use Ecommerce's phone matching rule and reject ambiguous suffix matches.
        $matches = Customer::query()->wherePhone($fullPhone)->limit(2)->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }
}
