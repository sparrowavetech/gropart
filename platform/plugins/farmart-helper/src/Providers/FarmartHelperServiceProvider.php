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
