<?php

namespace SparroWave\Shipmozo\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Events\OrderConfirmedEvent;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use SparroWave\Shipmozo\Http\Middleware\WebhookMiddleware;
use SparroWave\Shipmozo\Listeners\OrderConfirmedListener;

class ShipmozoServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this->setNamespace('plugins/shipmozo')->loadHelpers();
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes()
            ->loadMigrations()
            ->publishAssets();

        if (setting('shipping_shipmozo_status') == 1) {
            $this->app->bind(
                \Botble\Ecommerce\Http\Controllers\Fronts\PublicUpdateCheckoutController::class,
                \SparroWave\Shipmozo\Http\Controllers\UpdateCheckoutController::class
            );
            $this->app->bind(
                \Botble\Ecommerce\Services\HandleShippingFeeService::class,
                \SparroWave\Shipmozo\Services\CheckoutShippingFeeService::class
            );
            $this->app['view']->composer('plugins/ecommerce::orders.partials.shipping-option', function ($view): void {
                if (($view->getData()['shippingKey'] ?? null) === SHIPMOZO_SHIPPING_METHOD_NAME) {
                    $view->setPath(plugin_path('shipmozo/resources/views/overrides/shipping-option.blade.php'));
                }
            });
        }

        // Stored order weight already uses the store unit. The core view converts it
        // to grams but labels it with the store unit (0.84 kg becomes '840 kilograms').
        // Scope this presentation override to Shipmozo; API payloads stay in grams.
        $this->app['view']->composer('plugins/ecommerce::orders.edit.order-info', function ($view): void {
            $order = $view->getData()['order'] ?? null;
            if ($order instanceof \Botble\Ecommerce\Models\Order
                && $order->shipping_method->getValue() === SHIPMOZO_SHIPPING_METHOD_NAME) {
                $view->setPath(plugin_path('shipmozo/resources/views/overrides/order-info.blade.php'));
            }
        });

        $this->app['events']->listen(
            OrderConfirmedEvent::class,
            OrderConfirmedListener::class
        );

        $this->app['events']->listen(RouteMatched::class, function (RouteMatched $event): void {
            $this->app['router']->aliasMiddleware('shipmozo.webhook', WebhookMiddleware::class);

            if ($event->route->getName() === 'public.checkout.information') {
                $event->route->middleware(\SparroWave\Shipmozo\Http\Middleware\CheckoutAssets::class);
            }

            if (in_array($event->route->getName(), ['public.orders.tracking', 'customer.orders.view'], true)) {
                $trackingParams = $event->route->getName() === 'customer.orders.view'
                    ? ['customer_order_id' => $event->route->parameter('id')]
                    : [
                        'order_code' => request()->input('order_id'),
                        'email' => request()->input('email'),
                        'phone' => request()->input('phone'),
                    ];

                Theme::asset()
                    ->container('footer')
                    ->writeContent('shipmozo-tracking', view('plugins/shipmozo::tracking-scripts', compact('trackingParams'))->render());
            }

            DashboardMenu::registerItem([
                'id' => 'cms-plugins-shipmozo-ndr',
                'priority' => 10,
                'parent_id' => 'cms-plugins-ecommerce',
                'name' => 'ShipMozo NDRs',
                'icon' => 'ti ti-truck-return',
                'url' => route('shipmozo.ndr.index'),
                'permissions' => ['orders.index'],
            ]);
        });

        $config = $this->app['config'];
        if (! $config->has('logging.channels.shipmozo')) {
            $config->set([
                'logging.channels.shipmozo' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/shipmozo.log'),
                ],
            ]);
        }

        $this->app->register(HookServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);
    }
}
