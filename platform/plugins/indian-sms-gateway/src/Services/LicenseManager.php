<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class LicenseManager
{
    public function __construct(private SettingsRepository $settings)
    {
    }

    public function isActivated(bool $forceRemote = false): bool
    {
        $key = (string) $this->settings->secret('license_key', '');
        $domain = (string) $this->settings->get('license_domain', '');
        $localStatus = (string) $this->settings->get('license_status', 'inactive');

        if ($key === '' || $domain === '' || ! hash_equals($domain, $this->currentDomain())) {
            return false;
        }

        if (! in_array($localStatus, ['active', 'grace'], true)) {
            return false;
        }

        if (! $forceRemote && ! $this->validationIsDue()) {
            return true;
        }

        $result = $this->validateRemote($key);

        if ($result['success']) {
            $this->rememberValid($result['data']);

            return true;
        }

        if ($result['reachable']) {
            $this->rememberInvalid($result['message'], $result['status']);

            return false;
        }

        if ($this->withinGracePeriod()) {
            $this->settings->set([
                'license_status' => 'grace',
                'license_last_checked_at' => now()->toDateTimeString(),
                'license_last_error' => $result['message'],
            ]);

            return true;
        }

        $this->settings->set([
            'license_status' => 'unreachable',
            'license_last_checked_at' => now()->toDateTimeString(),
            'license_last_error' => $result['message'],
        ]);

        return false;
    }

    public function canUsePlugin(bool $forceRemote = false): bool
    {
        return $this->isActivated($forceRemote) || $this->trialRemaining() > 0;
    }

    public function canSendSms(bool $forceRemote = false): bool
    {
        return $this->canUsePlugin($forceRemote);
    }

    public function trialLimit(): int
    {
        $this->ensureTrialState();

        return max(0, (int) $this->settings->get('trial_limit', 100));
    }

    public function trialUsed(): int
    {
        $this->ensureTrialState();

        if (! Schema::hasTable('india_sms_logs')) {
            return 0;
        }

        $totalAccepted = $this->acceptedSmsCount();
        $baseline = max(0, (int) $this->settings->get('trial_baseline_count', 0));

        return max(0, $totalAccepted - $baseline);
    }

    private function acceptedSmsCount(): int
    {
        if (! Schema::hasTable('india_sms_logs')) {
            return 0;
        }

        return (int) DB::table('india_sms_logs')
            ->whereIn('status', ['accepted', 'sent', 'delivered'])
            ->count();
    }

    private function ensureTrialState(): void
    {
        $limit = (int) $this->settings->get('trial_limit', 0);

        if ($limit <= 0) {
            $this->settings->set(['trial_limit' => 100]);
        }

        if ($this->settings->get('trial_baseline_count') === null) {
            $this->settings->set([
                'trial_baseline_count' => $this->acceptedSmsCount(),
                'trial_started_at' => now()->toDateTimeString(),
                'trial_enabled' => '1',
            ]);
        }
    }

    public function trialRemaining(): int
    {
        return max(0, $this->trialLimit() - $this->trialUsed());
    }

    public function trialAvailable(): bool
    {
        return ! $this->isActivated() && $this->trialRemaining() > 0;
    }

    public function trialExhausted(): bool
    {
        return ! $this->isActivated() && $this->trialLimit() > 0 && $this->trialRemaining() <= 0;
    }

    public function activate(string $key): array
    {
        $key = strtoupper(trim($key));

        if ($key === '') {
            return [false, 'Enter a license key.'];
        }

        $result = $this->request('activate', $key);

        if (! $result['success']) {
            return [false, $result['message']];
        }

        $data = $result['data'];
        $this->settings->setSecret('license_key', $key);
        $this->settings->set([
            'license_key_last4' => substr(preg_replace('/[^A-Z0-9]/', '', $key), -4),
            'license_domain' => (string) ($data['domain'] ?? $this->currentDomain()),
            'license_status' => 'active',
            'license_activated_at' => now()->toDateTimeString(),
            'license_last_checked_at' => now()->toDateTimeString(),
            'license_last_valid_at' => now()->toDateTimeString(),
            'license_expires_at' => (string) ($data['expires_at'] ?? ''),
            'license_type' => (string) ($data['license_type'] ?? ''),
            'license_last_error' => '',
        ]);

        return [true, 'Indian SMS has been activated successfully.'];
    }

    public function deactivate(): array
    {
        $key = (string) $this->settings->secret('license_key', '');
        $message = 'The local license data has been cleared.';
        $remoteReleased = false;

        if ($key !== '') {
            $result = $this->request('deactivate', $key);

            if (! $result['success']) {
                return [false, 'The domain was not released. ' . $result['message']];
            }

            $remoteReleased = true;
            $message = (string) ($result['data']['message'] ?? 'License deactivated successfully.');
        }

        $this->settings->set([
            'secret_license_key' => '',
            'license_key_hash' => '',
            'license_key_last4' => '',
            'license_domain' => '',
            'license_status' => 'inactive',
            'license_activated_at' => '',
            'license_last_checked_at' => '',
            'license_last_valid_at' => '',
            'license_expires_at' => '',
            'license_type' => '',
            'license_last_error' => '',
        ]);

        return [$remoteReleased, $message];
    }

    public function refresh(): array
    {
        $key = (string) $this->settings->secret('license_key', '');

        if ($key === '') {
            return [false, 'No license key is stored.'];
        }

        $result = $this->validateRemote($key);

        if ($result['success']) {
            $this->rememberValid($result['data']);
            return [true, 'License status refreshed successfully.'];
        }

        if ($result['reachable']) {
            $this->rememberInvalid($result['message'], $result['status']);
        }

        return [false, $result['message']];
    }

    public function currentDomain(): string
    {
        $host = '';

        if (! app()->runningInConsole()) {
            $host = request()->getHost();
        }

        if (! $host) {
            $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: '';
        }

        $host = Str::lower(trim((string) $host));
        $host = preg_replace('/^www\./', '', $host) ?: $host;

        return $host;
    }

    public function status(): array
    {
        $status = (string) $this->settings->get('license_status', 'inactive');
        $activated = $this->isActivated();

        return [
            'activated' => $activated,
            'status' => $activated ? (string) $this->settings->get('license_status', 'active') : $status,
            'domain' => (string) $this->settings->get('license_domain'),
            'last4' => (string) $this->settings->get('license_key_last4'),
            'activated_at' => (string) $this->settings->get('license_activated_at'),
            'last_checked_at' => (string) $this->settings->get('license_last_checked_at'),
            'last_valid_at' => (string) $this->settings->get('license_last_valid_at'),
            'expires_at' => (string) $this->settings->get('license_expires_at'),
            'license_type' => (string) $this->settings->get('license_type'),
            'last_error' => (string) $this->settings->get('license_last_error'),
            'current_domain' => $this->currentDomain(),
            'server_url' => (string) config('india-sms-license.base_url'),
            'product_id' => (string) config('india-sms-license.product_id'),
            'grace_active' => $activated && $this->settings->get('license_status') === 'grace',

            'trial_limit' => $this->trialLimit(),
            'trial_used' => $this->trialUsed(),
            'trial_remaining' => $this->trialRemaining(),
            'trial_exhausted' => $this->trialExhausted(),
            'trial_enabled' => ! $activated && $this->trialLimit() > 0,
            'trial_available' => ! $activated && $this->trialRemaining() > 0,
            'access_allowed' => $activated || $this->trialRemaining() > 0,
        ];
    }

    private function validateRemote(string $key): array
    {
        return $this->request('validate', $key);
    }

    private function request(string $action, string $key): array
    {
        $baseUrl = rtrim((string) config('india-sms-license.base_url'), '/');

        if ($baseUrl === '') {
            return $this->failure('License server URL is not configured.', false, 'configuration_error');
        }

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->timeout((int) config('india-sms-license.timeout', 12))
                ->connectTimeout((int) config('india-sms-license.connect_timeout', 5))
                ->post($baseUrl . '/' . $action, [
                    'product_id' => (string) config('india-sms-license.product_id'),
                    'product_secret' => (string) config('india-sms-license.product_secret'),
                    'license_key' => $key,
                    'domain' => $this->currentDomain(),
                    'instance_id' => $this->instanceId(),
                    'plugin_version' => '1.4.3',
                    'site_url' => (string) config('app.url'),
                ]);
        } catch (ConnectionException $exception) {
            return $this->failure('Could not connect to the Ashikul License server.', false, 'connection_error');
        } catch (Throwable $exception) {
            report($exception);
            return $this->failure('License validation failed because of a server connection error.', false, 'connection_error');
        }

        $data = $response->json();
        $data = is_array($data) ? $data : [];
        $success = $response->successful() && (bool) ($data['success'] ?? false);

        if ($success) {
            return [
                'success' => true,
                'reachable' => true,
                'status' => (string) ($data['status'] ?? 'active'),
                'message' => (string) ($data['message'] ?? 'License request successful.'),
                'data' => $data,
            ];
        }

        return $this->failure(
            (string) ($data['message'] ?? ('License server returned HTTP ' . $response->status() . '.')),
            true,
            (string) ($data['status'] ?? 'invalid'),
            $data
        );
    }

    private function failure(string $message, bool $reachable, string $status, array $data = []): array
    {
        return [
            'success' => false,
            'reachable' => $reachable,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
    }

    private function rememberValid(array $data): void
    {
        $this->settings->set([
            'license_status' => 'active',
            'license_domain' => (string) ($data['domain'] ?? $this->currentDomain()),
            'license_last_checked_at' => now()->toDateTimeString(),
            'license_last_valid_at' => now()->toDateTimeString(),
            'license_expires_at' => (string) ($data['expires_at'] ?? ''),
            'license_last_error' => '',
        ]);
    }

    private function rememberInvalid(string $message, string $status): void
    {
        $this->settings->set([
            'license_status' => $status ?: 'invalid',
            'license_last_checked_at' => now()->toDateTimeString(),
            'license_last_error' => $message,
        ]);
    }

    private function validationIsDue(): bool
    {
        $lastChecked = (string) $this->settings->get('license_last_checked_at', '');

        if ($lastChecked === '') {
            return true;
        }

        try {
            return now()->diffInMinutes($lastChecked) >= (int) config('india-sms-license.validation_interval_minutes', 720);
        } catch (Throwable) {
            return true;
        }
    }

    private function withinGracePeriod(): bool
    {
        $lastValid = (string) $this->settings->get('license_last_valid_at', '');

        if ($lastValid === '') {
            return false;
        }

        try {
            return now()->diffInHours($lastValid) <= (int) config('india-sms-license.offline_grace_hours', 72);
        } catch (Throwable) {
            return false;
        }
    }

    private function instanceId(): string
    {
        $instanceId = (string) $this->settings->get('license_instance_id', '');

        if ($instanceId !== '') {
            return $instanceId;
        }

        $instanceId = (string) Str::uuid();
        $this->settings->set(['license_instance_id' => $instanceId]);

        return $instanceId;
    }
}
