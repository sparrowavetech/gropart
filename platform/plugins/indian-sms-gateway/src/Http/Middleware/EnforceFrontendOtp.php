<?php

namespace Ashikul\IndiaSmsGateway\Http\Middleware;

use Ashikul\IndiaSmsGateway\Services\OtpService;
use Ashikul\IndiaSmsGateway\Services\PhoneNormalizer;
use Ashikul\IndiaSmsGateway\Services\RequestPhoneResolver;
use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnforceFrontendOtp
{
    public function __construct(
        private SettingsRepository $settings,
        private RequestPhoneResolver $phoneResolver,
        private PhoneNormalizer $phones,
        private OtpService $otp,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') || ! $this->settings->bool('otp_enabled', true)) {
            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();
        $path = trim($request->path(), '/');

        if ($this->isRegistrationRequest($routeName, $path) && $this->settings->bool('registration_otp')) {
            // Botble's JS validation posts individual fields to the same registration
            // endpoint. Those background requests must reach Botble untouched; OTP is
            // enforced only when the complete registration form is submitted.
            if ($this->isRemoteRegistrationValidation($request)) {
                return $next($request);
            }

            return $this->verifyRequest($request, $next, 'registration', 'india_sms_otp_token');
        }

        if ($this->isCheckoutRequest($request, $routeName, $path) && $this->settings->bool('checkout_otp')) {
            return $this->verifyCheckout($request, $next);
        }

        return $next($request);
    }

    private function verifyCheckout(Request $request, Closure $next): Response
    {
        $phone = $this->phoneResolver->resolve($request);

        if (! $phone) {
            return $next($request);
        }

        try {
            $phone = $this->phones->normalize($phone);
        } catch (Throwable $exception) {
            return $this->failure($request, $exception->getMessage());
        }

        $minimum = (float) $this->settings->get('checkout_min_total', 0);
        $total = $this->resolveOrderTotal($request);

        if ($minimum > 0 && $total !== null && $total < $minimum) {
            return $next($request);
        }

        if ($this->settings->bool('skip_verified_checkout', true)
            && $this->authenticatedCustomerOwnsVerifiedPhone($request, $phone)) {
            return $next($request);
        }

        return $this->verifyRequest($request, $next, 'checkout', 'india_sms_checkout_token', $phone);
    }

    private function verifyRequest(
        Request $request,
        Closure $next,
        string $purpose,
        string $tokenField,
        ?string $resolvedPhone = null,
    ): Response {
        $phone = $resolvedPhone ?: $this->phoneResolver->resolve($request);

        if (! $phone) {
            return $this->failure($request, 'A mobile number is required for OTP verification.');
        }

        try {
            $phone = $this->phones->normalize($phone);
        } catch (Throwable $exception) {
            return $this->failure($request, $exception->getMessage());
        }

        $token = (string) $request->input($tokenField, '');
        $sessionVerification = $request->session()->get('india_sms_verified.' . $purpose, []);

        if ($token === '' && is_array($sessionVerification)) {
            $sessionPhoneHash = (string) ($sessionVerification['phone_hash'] ?? '');

            if ($sessionPhoneHash === hash('sha256', $phone)) {
                $token = (string) ($sessionVerification['token'] ?? '');
            }
        }

        // The registration/checkout transaction can still fail validation after
        // this middleware. Keep the token reusable until the customer/order is
        // actually created; the corresponding event listener consumes it.
        if (! $this->otp->validateToken($phone, $token, $purpose, false)) {
            return $this->failure($request, 'Please verify your mobile number with OTP before continuing.');
        }

        return $next($request);
    }

    private function isRemoteRegistrationValidation(Request $request): bool
    {
        // Botble JS Validation may mark a request explicitly.
        foreach ([
            'X-Js-Validation',
            'X-Remote-Validation',
            'X-Validate-Only',
        ] as $header) {
            if ($request->headers->has($header)) {
                return true;
            }
        }

        if ($request->boolean('_validate_only')
            || $request->boolean('validate_only')
            || $request->has('_js_validation')) {
            return true;
        }

        // Remote validators usually send one field (for example email) to the
        // registration URL and expect Botble's normal JSON validation response.
        // A real registration contains credentials plus identity/contact data.
        $hasPassword = $request->filled('password')
            || $request->filled('password_confirmation')
            || $request->filled('password_confirmation');

        $hasIdentity = $request->filled('name')
            || $request->filled('first_name')
            || $request->filled('last_name')
            || $request->filled('email');

        $hasPhone = $this->phoneResolver->resolve($request) !== null;

        if ($request->ajax() || $request->expectsJson()) {
            if (! $hasPassword || (! $hasIdentity && ! $hasPhone)) {
                return true;
            }

            $businessFields = array_diff(array_keys($request->except([
                '_token', '_method', 'locale', 'language', 'url', 'current_url',
            ])), [
                'email', 'phone', 'mobile', 'contact_number', 'name', 'first_name',
                'last_name', 'password', 'password_confirmation',
            ]);

            // A tiny AJAX payload is validation, not account creation.
            if (count($businessFields) === 0 && ! $hasPassword) {
                return true;
            }
        }

        return false;
    }

    private function isRegistrationRequest(string $routeName, string $path): bool
    {
        return str_contains($routeName, 'customer.register')
            || str_contains($routeName, 'customer.create')
            || preg_match('#(^|/)customer/register/?$#', $path) === 1
            || preg_match('#(^|/)register/?$#', $path) === 1;
    }

    private function isCheckoutRequest(Request $request, string $routeName, string $path): bool
    {
        $routeName = strtolower($routeName);
        $path = strtolower($path);

        if (! str_contains($routeName, 'checkout') && ! str_contains($path, 'checkout')) {
            return false;
        }

        // Checkout pages make several background POST requests while the user
        // changes an address, coupon, shipping method, or payment option. OTP
        // must only guard the final place-order request, not those AJAX calls.
        foreach ([
            'coupon',
            'shipping-method',
            'shipping_method',
            'available-shipping',
            'payment-method',
            'payment_method',
            'address/save',
            'address/update',
            'calculate',
            'refresh',
            'tax',
            'cart/update',
        ] as $backgroundFragment) {
            if (str_contains($routeName, $backgroundFragment) || str_contains($path, $backgroundFragment)) {
                return false;
            }
        }

        foreach ([
            'checkout.process',
            'checkout.store',
            'checkout.complete',
            'checkout.submit',
            'checkout.place-order',
            'checkout.order',
        ] as $finalRouteFragment) {
            if (str_contains($routeName, $finalRouteFragment)) {
                return true;
            }
        }

        if (preg_match('#(^|/)checkout/(process|store|complete|submit|place-order|order)/?$#', $path) === 1) {
            return true;
        }

        // Custom Botble themes sometimes post the final checkout form to the
        // bare /checkout URL. Requiring both a payment field and a phone keeps
        // this fallback narrow enough to avoid the usual AJAX update calls.
        return $request->hasAny(['payment_method', 'payment_channel'])
            && $this->phoneResolver->resolve($request) !== null;
    }

    private function resolveOrderTotal(Request $request): ?float
    {
        foreach (['amount', 'total', 'order_total', 'checkout_total'] as $key) {
            $value = $request->input($key);

            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    private function authenticatedCustomerOwnsVerifiedPhone(Request $request, string $phone): bool
    {
        try {
            $customer = $request->user('customer');

            if (! $customer) {
                return false;
            }

            $customerPhone = data_get($customer, 'phone');

            if (! is_string($customerPhone) || $this->phones->normalize($customerPhone) !== $phone) {
                return false;
            }

            if (data_get($customer, 'phone_verified_at')) {
                return true;
            }

            return $this->otp->recentlyVerified($phone, 'registration')
                || $this->otp->recentlyVerified($phone, 'checkout');
        } catch (Throwable) {
            return false;
        }
    }

    private function failure(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error' => true,
                'message' => $message,
                'errors' => ['phone' => [$message]],
            ], 422);
        }

        return redirect()->back()->withInput()->withErrors(['phone' => $message]);
    }
}
