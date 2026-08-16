<?php

namespace Ashikul\IndiaSmsGateway\Listeners;

use Ashikul\IndiaSmsGateway\Services\OtpService;
use Ashikul\IndiaSmsGateway\Services\PhoneNormalizer;
use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HandleCustomerRegistered
{
    public function __construct(
        private SettingsRepository $settings,
        private PhoneNormalizer $phones,
        private OtpService $otp,
    ) {
    }

    public function handle(Registered $event): void
    {
        if ($event->user instanceof Model) {
            $this->finalize($event->user);
        }
    }

    /**
     * Finalize a registration OTP after the customer record really exists.
     * This method is also called by the ecommerce Customer::created fallback
     * because some Botble themes do not dispatch Laravel's Registered event.
     */
    public function finalize(Model $customer): void
    {
        if (! $this->settings->bool('otp_enabled', true)
            || ! $this->settings->bool('registration_otp')
            || ! app()->bound('request')
            || ! request()->hasSession()) {
            return;
        }

        $rawPhone = data_get($customer, 'phone');

        if (! is_string($rawPhone) || trim($rawPhone) === '') {
            return;
        }

        try {
            $phone = $this->phones->normalize($rawPhone);
            $verification = request()->session()->get('india_sms_verified.registration', []);
            $token = is_array($verification) ? (string) ($verification['token'] ?? '') : '';
            $phoneHash = is_array($verification) ? (string) ($verification['phone_hash'] ?? '') : '';

            if ($phoneHash !== hash('sha256', $phone)
                || ! $this->otp->validateToken($phone, $token, 'registration', true)) {
                return;
            }

            $this->otp->rememberVerified($phone, 'registration', $customer);
            request()->session()->forget('india_sms_verified.registration');

            $table = $customer->getTable();

            if (Schema::hasColumn($table, 'phone_verified_at')) {
                $customer->forceFill(['phone_verified_at' => now()])->saveQuietly();
            }
        } catch (Throwable) {
            // The account has already been created. Audit persistence must not
            // turn a successful registration into an application error.
        }
    }
}
