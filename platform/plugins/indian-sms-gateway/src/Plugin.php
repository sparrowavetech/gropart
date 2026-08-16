<?php

namespace Ashikul\IndiaSmsGateway;

use Ashikul\IndiaSmsGateway\Services\DatabaseInstaller;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class Plugin extends PluginOperationAbstract
{
    public static function activate(): void
    {
        try {
            Artisan::call('migrate', [
                '--path' => dirname(__DIR__) . '/database/migrations',
                '--realpath' => true,
                '--force' => true,
            ]);
        } catch (Throwable $exception) {
            Log::error('Indian SMS migrations failed during activation.', [
                'exception' => $exception,
            ]);
        }

        try {
            app(DatabaseInstaller::class)->ensure();
        } catch (Throwable $exception) {
            Log::error('Indian SMS database repair failed during activation.', [
                'exception' => $exception,
            ]);
        }

        $defaults = [
            'enabled' => '0',
            'license_key_hash' => '',
            'license_key_last4' => '',
            'license_domain' => '',
            'license_activated_at' => '',
            'license_status' => 'inactive',
            'license_last_checked_at' => '',
            'license_last_valid_at' => '',
            'license_expires_at' => '',
            'license_type' => '',
            'license_last_error' => '',
            'trial_limit' => '100',
            'trial_baseline_count' => '',
            'trial_started_at' => '',
            'trial_used' => '0',
            'trial_enabled' => '1',
            'license_instance_id' => '',
            'default_gateway' => 'msg91',
            'fallback_gateway' => '',
            'second_fallback_gateway' => '',
            'request_timeout' => '20',
            'connect_timeout' => '5',
            'retry_attempts' => '1',
            'queue_enabled' => '0',
            'queue_worker_confirmed' => '0',
            'queue_name' => 'sms',
            'max_per_recipient_hour' => '20',
            'global_per_minute' => '100',
            'otp_enabled' => '1',
            'otp_length' => '6',
            'otp_ttl' => '300',
            'otp_max_attempts' => '5',
            'otp_resend_cooldown' => '60',
            'otp_requests_phone_hour' => '5',
            'otp_requests_ip_hour' => '20',
            'verification_remember_minutes' => '1440',
            'registration_otp' => '0',
            'login_otp' => '0',
            'password_reset_otp' => '0',
            'checkout_otp' => '0',
            'checkout_min_total' => '0',
            'skip_verified_checkout' => '1',
            'ecommerce_notifications_enabled' => '1',
            'customer_new_order_sms' => '1',
            'admin_new_order_sms' => '1',
            'customer_status_sms' => '1',
            'customer_payment_sms' => '1',
            'customer_shipping_sms' => '1',
        ];

        try {
            $store = setting();

            foreach ($defaults as $key => $value) {
                $fullKey = 'india_sms_' . $key;

                if (setting($fullKey) === null) {
                    $store->set($fullKey, $value);
                }
            }

            $store->save();

            if ((int) setting('india_sms_trial_limit', 0) <= 0) {
                $store->set('india_sms_trial_limit', '100');
            }

            if (setting('india_sms_trial_baseline_count') === null || setting('india_sms_trial_baseline_count') === '') {
                $acceptedCount = 0;

                if (\Illuminate\Support\Facades\Schema::hasTable('india_sms_logs')) {
                    $acceptedCount = (int) \Illuminate\Support\Facades\DB::table('india_sms_logs')
                        ->whereIn('status', ['accepted', 'sent', 'delivered'])
                        ->count();
                }

                $store->set('india_sms_trial_baseline_count', (string) $acceptedCount);
                $store->set('india_sms_trial_started_at', now()->toDateTimeString());
                $store->set('india_sms_trial_enabled', '1');
            }

            $store->save();
        } catch (Throwable $exception) {
            Log::error('Indian SMS default settings could not be saved.', [
                'exception' => $exception,
            ]);
        }
    }

    public static function deactivate(): void
    {
    }

    public static function remove(): void
    {
        // Retain configuration and delivery history to prevent accidental loss.
    }
}
