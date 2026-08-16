<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class InstallationHeartbeatService
{
    private const INTERVAL_HOURS = 12;

    public function sendIfDue(): void
    {
        try {
            $last = (string) setting('india_sms_license_heartbeat_at', '');
            if ($last !== '' && strtotime($last) > time() - self::INTERVAL_HOURS * 3600) {
                return;
            }
            $instanceId = (string) setting('india_sms_license_instance_id', '');
            if ($instanceId === '') {
                $instanceId = (string) Str::uuid();
                setting()->set('india_sms_license_instance_id', $instanceId);
                setting()->save();
            }
            $base = rtrim((string) config('india-sms-license.base_url', config('india-sms-license.server_url', 'https://license.ashikul.info/api/v1')), '/');
            $payload = [
                'product_id' => (string) config('india-sms-license.product_id'),
                'product_secret' => (string) config('india-sms-license.product_secret'),
                'domain' => preg_replace('/^www\./', '', strtolower((string) request()->getHost())),
                'instance_id' => $instanceId,
                'plugin_version' => '1.4.3',
                'platform' => 'Botble CMS',
                'cms' => 'Botble CMS',
                'php_version' => PHP_VERSION,
                'site_url' => url('/'),
                'site_name' => (string) config('app.name', request()->getHost()),
                'product_name' => 'Indian SMS Gateway for Botble',
            ];
            $response = Http::acceptJson()->asJson()->timeout(8)->post($base . '/heartbeat', $payload);
            if (! $response->successful() || $response->json('success') !== true) {
                $response = Http::acceptJson()->asForm()->timeout(8)->post($base . '/index.php?action=heartbeat', $payload);
            }
            if ($response->successful() && $response->json('success') === true) {
                setting()->set('india_sms_license_heartbeat_at', now()->toDateTimeString());
                setting()->save();
            }
        } catch (Throwable $e) {
            // Telemetry must never interrupt the host website.
        }
    }
}
