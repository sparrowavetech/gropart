<?php

namespace Ashikul\IndiaSmsGateway\Providers;

use Ashikul\IndiaSmsGateway\Http\Middleware\EnforceFrontendOtp;
use Ashikul\IndiaSmsGateway\Http\Middleware\InjectFrontendOtpAssets;
use Ashikul\IndiaSmsGateway\Services\LicenseManager;
use Ashikul\IndiaSmsGateway\Services\InstallationHeartbeatService;
use Ashikul\IndiaSmsGateway\Listeners\HandleCustomerRegistered;
use Ashikul\IndiaSmsGateway\Services\CustomerResolver;
use Ashikul\IndiaSmsGateway\Services\DatabaseInstaller;
use Ashikul\IndiaSmsGateway\Services\EcommerceNotificationService;
use Ashikul\IndiaSmsGateway\Services\GatewayRegistry;
use Ashikul\IndiaSmsGateway\Services\OtpService;
use Ashikul\IndiaSmsGateway\Services\PhoneNormalizer;
use Ashikul\IndiaSmsGateway\Services\RequestPhoneResolver;
use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Ashikul\IndiaSmsGateway\Services\SmsManager;
use Ashikul\IndiaSmsGateway\Services\TemplateInstaller;
use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Botble\Base\Supports\ServiceProvider;
use Throwable;

class IndiaSmsGatewayServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        Helper::autoload(__DIR__ . '/../../helpers');

        $this->mergeConfigFrom(__DIR__ . '/../../config/india-sms.php', 'india-sms');
        $this->mergeConfigFrom(__DIR__ . '/../../config/license.php', 'india-sms-license');

        $this->app->singleton(SettingsRepository::class);
        $this->app->singleton(DatabaseInstaller::class);
        $this->app->singleton(LicenseManager::class);
        $this->app->singleton(InstallationHeartbeatService::class);
        $this->app->singleton(GatewayRegistry::class);
        $this->app->singleton(SmsManager::class);
        $this->app->singleton(OtpService::class);
        $this->app->singleton(CustomerResolver::class);
        $this->app->singleton(RequestPhoneResolver::class);
        $this->app->singleton(EcommerceNotificationService::class);
        $this->app->singleton(TemplateInstaller::class);
        $this->app->alias(SmsManager::class, 'india-sms');
        $this->app->register(EventServiceProvider::class);
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/india-sms-gateway')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'api']);

        Event::listen(RouteMatched::class, function (): void {
            $this->registerDashboardMenu();
        });

        // The public route group is assembled before the application "booted"
        // callbacks run. Register this filter immediately, otherwise the OTP
        // gate can be missing from registration and checkout routes.
        $this->registerPublicOtpMiddleware();

        $this->app->booted(function (): void {
            $this->app->terminating(function (): void {
                $this->app->make(InstallationHeartbeatService::class)->sendIfDue();
            });
            try {
                $this->app->make(DatabaseInstaller::class)->ensure();
            } catch (Throwable) {
            }

            $this->registerFrontendAssets();
            $this->registerEcommerceHooks();

            try {
                $this->app->make(TemplateInstaller::class)->ensureDefaults();
            } catch (Throwable) {
            }
        });
    }

    private function registerDashboardMenu(): void
    {
        $menu = dashboard_menu();

        // Always display the complete Indian SMS menu. License enforcement is
        // handled by route middleware, so the plugin never appears as a
        // license-only product in the Botble dashboard.
        $menu->registerItem([
            'id' => 'cms-plugins-india-sms',
            'priority' => 60,
            'parent_id' => null,
            'name' => 'plugins/india-sms-gateway::india-sms.menu.root',
            'icon' => 'ti ti-message-2',
            'url' => route('india-sms.index'),
            'permissions' => ['india-sms.index'],
        ]);

        $items = [
            ['id' => 'cms-plugins-india-sms-overview', 'priority' => 1, 'name' => 'plugins/india-sms-gateway::india-sms.menu.overview', 'route' => 'india-sms.index', 'permission' => 'india-sms.index'],
            ['id' => 'cms-plugins-india-sms-logs', 'priority' => 2, 'name' => 'plugins/india-sms-gateway::india-sms.menu.logs', 'route' => 'india-sms.logs.index', 'permission' => 'india-sms.logs.index'],
            ['id' => 'cms-plugins-india-sms-templates', 'priority' => 3, 'name' => 'plugins/india-sms-gateway::india-sms.menu.templates', 'route' => 'india-sms.templates.index', 'permission' => 'india-sms.templates.index'],
            ['id' => 'cms-plugins-india-sms-otp', 'priority' => 4, 'name' => 'plugins/india-sms-gateway::india-sms.menu.otp', 'route' => 'india-sms.otps.index', 'permission' => 'india-sms.otps.index'],
            ['id' => 'cms-plugins-india-sms-gateways', 'priority' => 5, 'name' => 'plugins/india-sms-gateway::india-sms.menu.gateways', 'route' => 'india-sms.gateways.index', 'permission' => 'india-sms.gateways.index'],
            ['id' => 'cms-plugins-india-sms-admin', 'priority' => 6, 'name' => 'Admin SMS', 'route' => 'india-sms.admin-notifications', 'permission' => 'india-sms.settings'],
            ['id' => 'cms-plugins-india-sms-settings', 'priority' => 7, 'name' => 'plugins/india-sms-gateway::india-sms.menu.settings', 'route' => 'india-sms.settings', 'permission' => 'india-sms.settings'],
            ['id' => 'cms-plugins-india-sms-activation', 'priority' => 8, 'name' => 'License', 'route' => 'india-sms.activation.index', 'permission' => 'india-sms.settings'],
        ];

        foreach ($items as $item) {
            $menu->registerItem([
                'id' => $item['id'],
                'priority' => $item['priority'],
                'parent_id' => 'cms-plugins-india-sms',
                'name' => $item['name'],
                'icon' => null,
                'url' => route($item['route']),
                'permissions' => [$item['permission']],
            ]);
        }
    }

    private function registerPublicOtpMiddleware(): void
    {
        // Register at Laravel's web middleware-group level so custom Botble
        // themes cannot bypass registration/checkout OTP enforcement.
        try {
            $router = $this->app['router'];
            $router->pushMiddlewareToGroup('web', EnforceFrontendOtp::class);
            $router->pushMiddlewareToGroup('web', InjectFrontendOtpAssets::class);
        } catch (Throwable) {
        }

        if (defined('BASE_FILTER_GROUP_PUBLIC_ROUTE')) {
            add_filter(BASE_FILTER_GROUP_PUBLIC_ROUTE, function (array $data): array {
                $data['middleware'] ??= [];

                foreach ([EnforceFrontendOtp::class, InjectFrontendOtpAssets::class] as $middleware) {
                    if (! in_array($middleware, $data['middleware'], true)) {
                        $data['middleware'][] = $middleware;
                    }
                }

                return $data;
            }, 999, 1);
        }
    }

    private function registerFrontendAssets(): void
    {
        if (! defined('THEME_FRONT_FOOTER')) {
            return;
        }

        add_filter(THEME_FRONT_FOOTER, function (?string $html): string {
            static $rendered = false;

            if ($rendered) {
                return (string) $html;
            }

            $settings = $this->app->make(SettingsRepository::class);

            if (! $settings->bool('otp_enabled', true)) {
                return (string) $html;
            }

            $rendered = true;

            return (string) $html . view('plugins/india-sms-gateway::frontend.bootstrap', [
                'config' => [
                    'registration' => $settings->bool('registration_otp'),
                    'login' => $settings->bool('login_otp'),
                    'passwordReset' => $settings->bool('password_reset_otp'),
                    'checkout' => $settings->bool('checkout_otp'),
                    'codeLength' => (int) $settings->get('otp_length', 6),
                    'resendCooldown' => (int) $settings->get('otp_resend_cooldown', 60),
                    'requestUrl' => route('india-sms.frontend.otp.request'),
                    'verifyUrl' => route('india-sms.frontend.otp.verify'),
                    'loginUrl' => route('india-sms.frontend.otp.login'),
                    'resetUrl' => route('india-sms.frontend.otp.reset-password'),
                    'checkoutMinimum' => (float) $settings->get('checkout_min_total', 0),
                ],
            ])->render();
        }, 99, 1);
    }

    private function registerEcommerceHooks(): void
    {
        if (! class_exists('Botble\\Ecommerce\\Models\\Order')) {
            return;
        }

        $orderClass = 'Botble\\Ecommerce\\Models\\Order';

        $orderClass::updated(function (Model $order): void {
            $this->app->make(EcommerceNotificationService::class)->orderStatusChanged($order);
        });

        // Official Botble order-created hook. This normally runs after the
        // checkout service has prepared the order and its address data.
        if (function_exists('add_action')) {
            add_action('ecommerce_create_order_from_data', function (mixed $order): void {
                $this->app->make(EcommerceNotificationService::class)->orderCreated($order);
            }, 20, 1);
        }

        // Fallback for custom checkout implementations that do not fire the
        // standard hook. Running at request termination gives related address
        // records time to be stored. The notification service deduplicates it.
        $orderClass::created(function (Model $order): void {
            $orderId = $order->getKey();
            $modelClass = $order::class;

            $this->app->terminating(function () use ($modelClass, $orderId): void {
                try {
                    $freshOrder = $modelClass::query()->find($orderId);

                    if ($freshOrder instanceof Model) {
                        $this->app->make(EcommerceNotificationService::class)->orderCreated($freshOrder);
                    }
                } catch (Throwable) {
                }
            });
        });

        // Registration fallback for themes/controllers that create the
        // ecommerce customer directly without dispatching Registered.
        if (class_exists('Botble\Ecommerce\Models\Customer')) {
            $customerClass = 'Botble\Ecommerce\Models\Customer';
            $customerClass::created(function (Model $customer): void {
                $this->app->make(HandleCustomerRegistered::class)->finalize($customer);
            });
        }

        // Domain events are the primary integrations. These model observers
        // cover custom admin/theme flows that update payment/shipping models
        // without dispatching the standard Botble events.
        if (class_exists('Botble\Payment\Models\Payment')) {
            $paymentClass = 'Botble\Payment\Models\Payment';
            $paymentClass::updated(function (Model $payment): void {
                if (! $payment->wasChanged('status')) {
                    return;
                }

                $status = $this->enumValue($payment->getAttribute('status'));

                if (! in_array(strtolower($status), ['approved', 'completed', 'complete', 'paid'], true)) {
                    return;
                }

                try {
                    $order = data_get($payment, 'order');

                    if (! $order instanceof Model && data_get($payment, 'order_id')) {
                        $order = $orderClass::query()->find(data_get($payment, 'order_id'));
                    }

                    if ($order instanceof Model) {
                        $this->app->make(EcommerceNotificationService::class)->paymentConfirmed($order);
                    }
                } catch (Throwable) {
                }
            });
        }

        if (class_exists('Botble\Ecommerce\Models\Shipment')) {
            $shipmentClass = 'Botble\Ecommerce\Models\Shipment';
            $shipmentClass::updated(function (Model $shipment): void {
                if (! $shipment->wasChanged('status')) {
                    return;
                }

                try {
                    $order = data_get($shipment, 'order');

                    if (! $order instanceof Model && data_get($shipment, 'order_id')) {
                        $order = $orderClass::query()->find(data_get($shipment, 'order_id'));
                    }

                    if ($order instanceof Model) {
                        $this->app->make(EcommerceNotificationService::class)->shippingStatusChanged($order, $shipment);
                    }
                } catch (Throwable) {
                }
            });
        }
    }

    private function enumValue(mixed $value): string
    {
        if (is_object($value)) {
            if (method_exists($value, 'getValue')) {
                return (string) $value->getValue();
            }

            if (property_exists($value, 'value')) {
                return (string) $value->value;
            }

            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
        }

        return is_scalar($value) ? (string) $value : '';
    }
}
