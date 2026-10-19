<?php

namespace SparroWave\FarmartHelper\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Router;
use SparroWave\FarmartHelper\Http\Middleware\SanitizePhoneRequestMiddleware;

class FarmartHelperServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->register(HookServiceProvider::class);
        $this->app->register(CheckoutIntegrityServiceProvider::class);
        $this->app->register(RazorpayCheckoutServiceProvider::class);
        $this->app->register(OrderWeightDisplayServiceProvider::class);
        $this->app->register(ProductCardServiceProvider::class);
        $this->app->register(SidebarAdsServiceProvider::class);
        $this->app->register(ProductDescriptionServiceProvider::class);
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/farmart-helper')
            ->loadAndPublishTranslations()
            ->loadAndPublishViews();

        /** @var Router $router */
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', SanitizePhoneRequestMiddleware::class);
    }
}
