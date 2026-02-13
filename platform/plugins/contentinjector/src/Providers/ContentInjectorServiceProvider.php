<?php

namespace Botble\ContentInjector\Providers;
use Botble\ContentInjector\Http\Middleware\ContentInjectorMiddleware;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\ContentInjector\Models\ContentInjector;

class ContentInjectorServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {

        // register middleware except admin and api
        $this->app['router']->pushMiddlewareToGroup('web', ContentInjectorMiddleware::class);

        
        




        $this
            ->setNamespace('plugins/contentinjector')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadMigrations();
            
            if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
                \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(ContentInjector::class, [
                    'name',
                ]);
            }
            
            DashboardMenu::default()->beforeRetrieving(function () {
                DashboardMenu::registerItem([
                    'id' => 'cms-plugins-contentinjector',
                    'priority' => 5,
                    'parent_id' => null,
                    'name' => 'plugins/contentinjector::contentinjector.name',
                    'icon' => 'ti ti-book',
                    'url' => route('contentinjector.index'),
                    'permissions' => ['contentinjector.index'],
                ]);
            });

    }
}
