<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemHealthService
{
    public function __construct(private SettingsRepository $settings, private GatewayRegistry $gateways)
    {
    }

    public function checks(): array
    {
        $database = true;

        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $database = false;
        }

        $default = (string) $this->settings->get('default_gateway', 'msg91');
        $queueEnabled = $this->settings->bool('queue_enabled');
        $queueReady = ! $queueEnabled || (
            $this->settings->bool('queue_worker_confirmed')
            && (string) config('queue.default', 'sync') !== 'sync'
        );
        $otpRouteReady = app('router')->has('india-sms.frontend.otp.request')
            && app('router')->has('india-sms.frontend.otp.verify');
        $otpFeatures = array_filter([
            $this->settings->bool('registration_otp') ? 'Registration' : null,
            $this->settings->bool('login_otp') ? 'Login' : null,
            $this->settings->bool('password_reset_otp') ? 'Password reset' : null,
            $this->settings->bool('checkout_otp') ? 'Checkout' : null,
        ]);
        $ecommerceReady = class_exists('Botble\\Ecommerce\\Models\\Order')
            && class_exists('Botble\\Ecommerce\\Models\\Customer');
        $requiredTables = [
            'india_sms_logs',
            'india_sms_templates',
            'india_sms_otps',
            'india_sms_verified_phones',
        ];
        $missingTables = collect($requiredTables)
            ->reject(fn (string $table): bool => Schema::hasTable($table))
            ->values()
            ->all();
        $tablesReady = $missingTables === [];
        $adminPhones = trim((string) $this->settings->get('admin_phone_numbers', ''));

        return [
            $this->check('sms-service', 'SMS service', $this->settings->bool('enabled'), $this->settings->bool('enabled') ? 'Enabled' : 'Disabled'),
            $this->check('default-gateway', 'Default gateway', $this->gateways->enabled($default) && $this->gateways->configured($default), $default),
            $this->check('database-tables', 'Plugin database tables', $tablesReady, $tablesReady ? 'Migrations ready' : 'Run pending migrations', ['missing_tables' => $missingTables]),
            $this->check('frontend-otp-routes', 'Frontend OTP routes', $otpRouteReady, $otpFeatures !== [] ? implode(', ', $otpFeatures) : 'No OTP integration enabled'),
            $this->check('ecommerce-integration', 'Ecommerce integration', $ecommerceReady, $ecommerceReady ? 'Order/customer models found' : 'Ecommerce plugin unavailable'),
            $this->check('admin-new-order-recipient', 'Admin new-order recipient', ! $this->settings->bool('admin_new_order_sms', true) || $adminPhones !== '', $adminPhones !== '' ? 'Configured' : 'No admin number saved'),
            $this->check('database-connection', 'Database', $database, $database ? 'Connected' : 'Connection failed'),
            $this->check('curl-extension', 'cURL', extension_loaded('curl'), extension_loaded('curl') ? 'Available' : 'Missing'),
            $this->check('openssl-extension', 'OpenSSL', extension_loaded('openssl'), extension_loaded('openssl') ? 'Available' : 'Missing'),
            $this->check('queue-mode', 'Queue mode', $queueReady, $queueEnabled ? (string) config('queue.default', 'sync') : 'Synchronous sending'),
        ];
    }

    public function diagnostic(string $key): ?array
    {
        $check = collect($this->checks())->firstWhere('key', $key);

        if (! is_array($check)) {
            return null;
        }

        $solutions = [
            'sms-service' => [
                'summary' => 'The master SMS service switch is disabled, so no gateway, OTP or order notification can send messages.',
                'steps' => [
                    'Open Indian SMS → Settings.',
                    'Enable “India SMS service”.',
                    'Save the settings and return to Overview.',
                ],
                'action_label' => 'Open SMS settings',
                'action_route' => 'india-sms.settings',
            ],
            'default-gateway' => [
                'summary' => 'The selected default gateway is disabled or does not have all required credentials.',
                'steps' => [
                    'Open Indian SMS → Gateways.',
                    'Configure the selected gateway and enable it.',
                    'Send a test SMS successfully.',
                    'Select that gateway as the default in Settings.',
                ],
                'action_label' => 'Open gateways',
                'action_route' => 'india-sms.gateways.index',
            ],
            'database-tables' => [
                'summary' => 'One or more plugin tables are missing. OTP, templates, logs and verification records may fail until migrations are completed.',
                'steps' => [
                    'Back up the database.',
                    'Run the pending Laravel migrations from your hosting terminal or SSH.',
                    'Clear the application cache.',
                    'Reload this diagnostic page.',
                ],
                'commands' => [
                    'php artisan migrate --force',
                    'php artisan optimize:clear',
                ],
                'action_label' => 'Back to overview',
                'action_route' => 'india-sms.index',
            ],
            'frontend-otp-routes' => [
                'summary' => 'The public OTP request or verification route is not registered. Frontend registration, login, password reset or checkout OTP cannot complete.',
                'steps' => [
                    'Clear route and application caches.',
                    'Deactivate and reactivate the plugin if routes are still missing.',
                    'Confirm that your active theme and Botble Theme plugin are enabled.',
                ],
                'commands' => [
                    'php artisan route:clear',
                    'php artisan optimize:clear',
                ],
                'action_label' => 'Open OTP settings',
                'action_route' => 'india-sms.settings',
            ],
            'ecommerce-integration' => [
                'summary' => 'Botble Ecommerce order/customer models were not found, so order and customer notification hooks cannot be registered.',
                'steps' => [
                    'Confirm the Ecommerce plugin is installed and activated.',
                    'Clear cache after activating Ecommerce.',
                    'Reload the India SMS Overview page.',
                ],
                'action_label' => 'Back to overview',
                'action_route' => 'india-sms.index',
            ],
            'admin-new-order-recipient' => [
                'summary' => 'Admin new-order SMS is enabled, but no admin mobile number is saved.',
                'steps' => [
                    'Open Indian SMS → Settings.',
                    'Find the “Admin SMS recipient” card.',
                    'Enter one or more India mobile numbers in “Admin mobile number(s)”.',
                    'Save the settings. Multiple numbers may be separated by comma, space, semicolon or new line.',
                ],
                'action_label' => 'Add admin number',
                'action_route' => 'india-sms.settings',
                'action_fragment' => 'admin-sms-recipient',
            ],
            'database-connection' => [
                'summary' => 'Laravel could not establish a database connection. The plugin cannot read settings or store SMS and OTP logs.',
                'steps' => [
                    'Check DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME and DB_PASSWORD in the site environment file.',
                    'Confirm the database server is online and accepts connections from this hosting account.',
                    'Clear configuration cache after changing environment values.',
                ],
                'commands' => ['php artisan config:clear'],
                'action_label' => 'Back to overview',
                'action_route' => 'india-sms.index',
            ],
            'curl-extension' => [
                'summary' => 'The PHP cURL extension is unavailable. Most SMS gateway HTTP requests require it.',
                'steps' => [
                    'Enable the cURL extension for the PHP version used by the website.',
                    'Restart PHP-FPM or ask the hosting provider to enable php-curl.',
                    'Reload this diagnostic page.',
                ],
                'action_label' => 'Back to overview',
                'action_route' => 'india-sms.index',
            ],
            'openssl-extension' => [
                'summary' => 'The PHP OpenSSL extension is unavailable. Secure HTTPS requests and encrypted gateway credentials may not work.',
                'steps' => [
                    'Enable OpenSSL for the active PHP version.',
                    'Restart PHP-FPM or contact the hosting provider.',
                    'Reload this diagnostic page.',
                ],
                'action_label' => 'Back to overview',
                'action_route' => 'india-sms.index',
            ],
            'queue-mode' => [
                'summary' => 'Queued sending is enabled, but the queue connection is synchronous or a continuously running worker has not been confirmed.',
                'steps' => [
                    'Set QUEUE_CONNECTION to database, redis or another asynchronous driver.',
                    'Run the queue table migration when using the database driver.',
                    'Start a persistent worker through Supervisor, systemd or your hosting cron/worker manager.',
                    'Enable “A queue worker is continuously running” in India SMS settings only after the worker is active.',
                ],
                'commands' => [
                    'php artisan queue:table',
                    'php artisan migrate --force',
                    'php artisan queue:work --queue=sms --tries=3',
                ],
                'action_label' => 'Open queue settings',
                'action_route' => 'india-sms.settings',
            ],
        ];

        return array_merge($check, $solutions[$key] ?? [
            'summary' => 'Review this system check and update the related plugin or server configuration.',
            'steps' => ['Return to the plugin overview after making the required change.'],
            'action_label' => 'Back to overview',
            'action_route' => 'india-sms.index',
        ]);
    }

    private function check(string $key, string $label, bool $ok, string $detail, array $meta = []): array
    {
        return array_merge([
            'key' => $key,
            'label' => $label,
            'ok' => $ok,
            'detail' => $detail,
        ], $meta);
    }
}
