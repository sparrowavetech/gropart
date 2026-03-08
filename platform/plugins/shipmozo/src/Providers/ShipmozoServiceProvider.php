<?php

namespace SparroWave\Shipmozo\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use SparroWave\Shipmozo\Http\Middleware\WebhookMiddleware;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

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
            ->loadAndPublishConfigurations(['general'])
            ->loadMigrations()
            ->publishAssets();

        $this->app['events']->listen(
            \Botble\Ecommerce\Events\OrderConfirmedEvent::class,
            \SparroWave\Shipmozo\Listeners\OrderConfirmedListener::class
        );

        $this->app['events']->listen(RouteMatched::class, function (\Illuminate\Routing\Events\RouteMatched $event): void {
            $this->app['router']->aliasMiddleware('shipmozo.webhook', WebhookMiddleware::class);

            if (in_array($event->route->getName(), ['public.orders.tracking', 'customer.orders.view'])) {
                \Botble\Theme\Facades\Theme::asset()
                    ->container('footer')
                    ->writeContent('shipmozo-tracking', view('plugins/shipmozo::tracking-scripts')->render());
            }

            \Botble\Base\Facades\DashboardMenu::registerItem([
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
