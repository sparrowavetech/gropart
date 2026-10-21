<?php

namespace SparroWave\FarmartHelper\Providers;

use Illuminate\Support\ServiceProvider;

class SidebarAdsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! is_plugin_active('ads')) {
            return;
        }

        // Child widgets now own their templates; retain the parent compatibility adapter.
        foreach (['frontend', 'backend'] as $template) {
            $this->app['view']->composer([
                "theme.farmart::/../widgets/ads/templates.{$template}",
                "theme.farmart::widgets.ads.templates.{$template}",
            ], function ($view) use ($template): void {
                $view->setPath(plugin_path("farmart-helper/resources/views/widgets/ads/{$template}.blade.php"));
            });
        }
    }
}
