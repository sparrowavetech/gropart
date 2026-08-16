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

        $this->app['events']->listen(
            OrderConfirmedEvent::class,
            OrderConfirmedListener::class
        );

        $this->app['events']->listen(RouteMatched::class, function (RouteMatched $event): void {
            $this->app['router']->aliasMiddleware('shipmozo.webhook', WebhookMiddleware::class);

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
