<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Services\CustomerResolver;
use Ashikul\IndiaSmsGateway\Services\OtpService;
use Ashikul\IndiaSmsGateway\Services\PhoneNormalizer;
use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Throwable;

class FrontendOtpController extends Controller
{
    public function request(
        Request $request,
        OtpService $otp,
        CustomerResolver $customers,
        PhoneNormalizer $phones,
        SettingsRepository $settings,
    ): JsonResponse {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'purpose' => ['required', Rule::in(['registration', 'login', 'password_reset', 'checkout'])],
            'customer_name' => ['nullable', 'string', 'max:150'],
        ]);

        $purpose = $data['purpose'];

        if (! $this->purposeEnabled($purpose, $settings)) {
            return response()->json(['success' => false, 'message' => 'This OTP feature is disabled.'], 422);
        }

        try {
            $phone = $phones->normalize($data['phone']);

            if ($purpose === 'registration' && $customers->phoneExists($phone)) {
                return response()->json(['success' => false, 'message' => 'An account already exists with this mobile number.'], 422);
            }

            if (in_array($purpose, ['login', 'password_reset'], true) && ! $customers->phoneExists($phone)) {
                return response()->json(['success' => false, 'message' => 'No account was found with this mobile number.'], 422);
            }

            $record = $otp->request($phone, $purpose, $request->ip() ?: 'unknown', [
                'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
                'customer_name' => trim((string) ($data['customer_name'] ?? '')),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully.',
                'request_id' => $record->uuid,
                'expires_at' => $record->expires_at->toIso8601String(),
                'resend_at' => $record->resend_available_at?->toIso8601String(),
            ]);
        } catch (Throwable $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function verify(Request $request, OtpService $otp, PhoneNormalizer $phones): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'code' => ['required', 'string', 'min:4', 'max:8'],
            'purpose' => ['required', Rule::in(['registration', 'login', 'password_reset', 'checkout'])],
            'customer_name' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $phone = $phones->normalize($data['phone']);
            $token = $otp->verifyAndIssueToken($phone, $data['code'], $data['purpose']);

            if (! $token) {
                return response()->json(['success' => false, 'message' => 'The OTP is invalid, expired, or has reached its attempt limit.'], 422);
            }

            $request->session()->put('india_sms_verified.' . $data['purpose'], [
                'phone_hash' => hash('sha256', $phone),
                'token' => $token,
                'verified_at' => now()->timestamp,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mobile number verified successfully.',
                'verification_token' => $token,
            ]);
        } catch (Throwable $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function login(
        Request $request,
        OtpService $otp,
        CustomerResolver $customers,
        PhoneNormalizer $phones,
    ): JsonResponse {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'verification_token' => ['required', 'string', 'max:200'],
            'remember' => ['nullable', 'boolean'],
        ]);

        try {
            $phone = $phones->normalize($data['phone']);

            if (! $otp->validateToken($phone, $data['verification_token'], 'login', true)) {
                return response()->json(['success' => false, 'message' => 'OTP verification has expired. Please try again.'], 422);
            }

            $customer = $customers->findByPhone($phone);

            if (! $customer) {
                return response()->json(['success' => false, 'message' => 'Customer account was not found.'], 422);
            }

            auth('customer')->login($customer, (bool) ($data['remember'] ?? false));
            $otp->rememberVerified($phone, 'login', $customer);
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => $this->customerDashboardUrl(),
            ]);
        } catch (Throwable $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function resetPassword(
        Request $request,
        OtpService $otp,
        CustomerResolver $customers,
        PhoneNormalizer $phones,
    ): JsonResponse {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'verification_token' => ['required', 'string', 'max:200'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        try {
            $phone = $phones->normalize($data['phone']);

            if (! $otp->validateToken($phone, $data['verification_token'], 'password_reset', true)) {
                return response()->json(['success' => false, 'message' => 'OTP verification has expired. Please try again.'], 422);
            }

            $customer = $customers->findByPhone($phone);

            if (! $customer) {
                return response()->json(['success' => false, 'message' => 'Customer account was not found.'], 422);
            }

            $customer->forceFill(['password' => Hash::make($data['password'])])->save();
            $otp->rememberVerified($phone, 'password_reset', $customer);

            return response()->json([
                'success' => true,
                'message' => 'Your password has been changed successfully.',
                'redirect' => $this->customerLoginUrl(),
            ]);
        } catch (Throwable $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    private function purposeEnabled(string $purpose, SettingsRepository $settings): bool
    {
        return $settings->bool('otp_enabled', true) && match ($purpose) {
            'registration' => $settings->bool('registration_otp'),
            'login' => $settings->bool('login_otp'),
            'password_reset' => $settings->bool('password_reset_otp'),
            'checkout' => $settings->bool('checkout_otp'),
            default => false,
        };
    }

    private function customerDashboardUrl(): string
    {
        foreach (['public.customer.overview', 'customer.overview'] as $routeName) {
            if (app('router')->has($routeName)) {
                return route($routeName);
            }
        }

        return url('/customer/overview');
    }

    private function customerLoginUrl(): string
    {
        foreach (['public.customer.login', 'customer.login'] as $routeName) {
            if (app('router')->has($routeName)) {
                return route($routeName);
            }
        }

        return url('/customer/login');
    }
}
