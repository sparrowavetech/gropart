<?php

namespace Botble\Sms\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Sms\Enums\SmsEnum;
use Botble\Sms\Supports\SmsHandler;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginOtpController extends BaseController
{
    public function showRequestForm()
    {
        abort_unless(setting('sms_login_otp_enabled'), 404);

        SeoHelper::setTitle(__('Login with OTP'));
        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Login with OTP'), route('customer.login.otp'));

        return Theme::scope('ecommerce.customers.login-otp', [], 'plugins/sms::themes.customers.login-otp')->render();
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
                'phone' => __('Please enter your mobile number.'),
            ]);
        }

        $phone = $this->normalizePhone($phoneInput, (string) $request->input('country_code', '91'));
        $customer = $this->findCustomerByPhone($phone);

        if (! $customer || $customer->status->getValue() !== CustomerStatusEnum::ACTIVATED) {
            throw ValidationException::withMessages([
                'phone' => __('No active customer account was found for this phone number.'),
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        $customer->otp = $otp;
        $customer->save();

        try {
            $sms = new SmsHandler();
            $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            $sms->setVariableValues([
                'customer_name' => $customer->name,
                'customer_phone' => $phone,
                'otp' => $otp,
            ]);

            $sms->sendUsingTemplate(SmsEnum::OTP(), $phone);
        } catch (\Throwable $exception) {
            Log::error('Failed to send login OTP: ' . $exception->getMessage());
        }

        return redirect()
            ->route('customer.login.otp.verify', $customer->getKey())
            ->with('success_msg', __('We have sent you an OTP to login.'));
    }

    public function showVerifyForm(int|string $id)
    {
        abort_unless(setting('sms_login_otp_enabled'), 404);

        $customer = Customer::query()->findOrFail($id);

        SeoHelper::setTitle(__('Verify Login OTP'));
        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Verify Login OTP'), route('customer.login.otp.verify', $customer->getKey()));

        return Theme::scope('ecommerce.customers.login-otp-verify', compact('customer'), 'plugins/sms::themes.customers.login-otp-verify')->render();
    }

    public function verify(Request $request)
    {
        abort_unless(setting('sms_login_otp_enabled'), 404);

        $request->validate([
            'customer_id' => ['required', 'numeric'],
            'otp' => ['required', 'numeric', 'digits:6'],
        ]);

        $customer = Customer::query()->find($request->input('customer_id'));

        if (! $customer || $customer->otp !== $request->input('otp')) {
            throw ValidationException::withMessages([
                'confirmation' => __('Invalid OTP. Please try again.'),
            ]);
        }

        $customer->otp = null;
        $customer->confirmed_at = $customer->confirmed_at ?: Carbon::now();
        $customer->save();

        auth('customer')->login($customer);

        return redirect()->intended(route('customer.overview'));
    }

    private function normalizePhone(string $phone, string $countryCode): string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?: $phone;
        $countryCode = preg_replace('/\D+/', '', $countryCode) ?: '91';

        if (str_starts_with($phone, $countryCode)) {
            return $phone;
        }

        return $countryCode . ltrim($phone, '0');
    }

    private function findCustomerByPhone(string $phone): ?Customer
    {
        $phone = preg_replace('/\D+/', '', $phone) ?: $phone;
        $candidates = array_filter(array_unique([
            $phone,
            strlen($phone) === 12 && str_starts_with($phone, '91') ? substr($phone, 2) : null,
            strlen($phone) === 10 ? '91' . $phone : null,
        ]));

        return Customer::query()->whereIn('phone', $candidates)->first();
    }
}
