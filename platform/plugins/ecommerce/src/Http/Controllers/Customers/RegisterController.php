<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use BaseHelper;
use Botble\ACL\Traits\RegistersUsers;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Repositories\Interfaces\CustomerInterface;
use Carbon\Carbon;
use EcommerceHelper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use SeoHelper;
use Theme;
use URL;
use Botble\Sms\Supports\SmsHandler;
use Botble\Sms\Enums\SmsEnum;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected string $redirectTo = '/';

    protected CustomerInterface $customerRepository;

    public function __construct(CustomerInterface $customerRepository)
    {
        $this->middleware('customer.guest');
        $this->customerRepository = $customerRepository;
    }

    public function showRegistrationForm()
    {
        SeoHelper::setTitle(__('Register'));

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Register'), route('customer.register'));

        if (! session()->has('url.intended')) {
            if (! in_array(url()->previous(), [route('customer.login'), route('customer.register')])) {
                session(['url.intended' => url()->previous()]);
            }
        }

        return Theme::scope('ecommerce.customers.register', [], 'plugins/ecommerce::themes.customers.register')
            ->render();
    }

    public function register(Request $request, BaseHttpResponse $response)
    {
        $this->validator($request->input())->validate();

        do_action('customer_register_validation', $request);

        $customer = $this->create($request->input());

        event(new Registered($customer));

        if (is_plugin_active('sms') && setting('sms_otp_enabled')) {
            $otp = mt_rand(000000, 999999);
            $sms = new  SmsHandler;
            $customer->otp  = $otp;
            $this->customerRepository->createOrUpdate($customer);
            $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($sms->templateEnabled(SmsEnum::OTP())) {
                $sms->setVariableValues([
                    'customer_name' => $customer->name,
                    'otp' => $otp,
                ]);
                $sms->sendUsingTemplate(
                    SmsEnum::OTP(),
                    $customer->phone
                );
            }

            return  $this->registered($request, $customer)
                ?: $response
                ->setNextUrl(route('customer.otp', $customer->id))
                ->setMessage(__('We have sent you an OTP to verify your mobile. Please check and confirm your mobile No!'));
        } else if (EcommerceHelper::isEnableEmailVerification()) {
            return $this->registered($request, $customer)
                ?: $response
                ->setNextUrl(route('customer.login'))
                ->setMessage(__('We have sent you an email to verify your email. Please check and confirm your email address!'));
        }

        $customer->confirmed_at = Carbon::now();
        $this->customerRepository->createOrUpdate($customer);
        $this->guard()->login($customer);

        return $response->setNextUrl($this->redirectPath())->setMessage(__('Registered successfully!'));
    }

    protected function validator(array $data)
    {
        $rules = [
            'name' => 'required|max:255',
            'phone' => 'required|' . BaseHelper::getPhoneValidationRule(),
            'email' => 'required|email|max:255|unique:ec_customers',
            'password' => 'required|min:6|confirmed',
        ];

        if (is_plugin_active('captcha') && setting('enable_captcha') && get_ecommerce_setting(
            'enable_recaptcha_in_register_page',
            0
        )) {
            $rules += ['g-recaptcha-response' => 'required|captcha'];
        }

        if (request()->has('agree_terms_and_policy')) {
            $rules['agree_terms_and_policy'] = 'accepted:1';
        }

        $attributes = [
            'name' => __('Name'),
            'email' => __('Email'),
            'phone' => __('Phone'),
            'password' => __('Password'),
            'g-recaptcha-response' => __('Captcha'),
            'agree_terms_and_policy' => __('Term and Policy'),
        ];

        return Validator::make($data, apply_filters('ecommerce_customer_registration_form_validation_rules', $rules), [
            'g-recaptcha-response.required' => __('Captcha Verification Failed!'),
            'g-recaptcha-response.captcha' => __('Captcha Verification Failed!'),
        ], $attributes);
    }

    protected function create(array $data)
    {
        return $this->customerRepository->create([
            'name' => BaseHelper::clean($data['name']),
            'email' => BaseHelper::clean($data['email']),
            'phone' => BaseHelper::clean($data['phone']),
            'password' => Hash::make($data['password']),
        ]);
    }

    protected function guard()
    {
        return auth('customer');
    }

    public function confirm(int $id, Request $request, BaseHttpResponse $response, CustomerInterface $customerRepository)
    {
        if (! URL::hasValidSignature($request)) {
            abort(404);
        }

        $customer = $customerRepository->findOrFail($id);

        $customer->confirmed_at = Carbon::now();
        $this->customerRepository->createOrUpdate($customer);

        $this->guard()->login($customer);

        return $response
            ->setNextUrl(route('customer.overview'))
            ->setMessage(__('You successfully confirmed your email address.'));
    }

    public function resendConfirmation(
        Request $request,
        CustomerInterface $customerRepository,
        BaseHttpResponse $response
    ) {
        $customer = $customerRepository->getFirstBy(['email' => $request->input('email')]);

        if (! $customer) {
            return $response
                ->setError()
                ->setMessage(__('Cannot find this customer!'));
        }

        $customer->sendEmailVerificationNotification();

        return $response
            ->setMessage(__('We sent you another confirmation email. You should receive it shortly.'));
    }

    public function getVerify()
    {
        return view('plugins/ecommerce::themes.customers.verify');
    }
}
