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

        // Widget views use a slash-based name rather than normal dotted partial names.
        $this->app['view']->composer('*', function ($view): void {
            $name = $view->name();
            foreach (['farmart', 'farmart-child'] as $theme) {
                foreach (['frontend', 'backend'] as $template) {
                    if (in_array($name, [
                        "theme.{$theme}::/../widgets/ads/templates.{$template}",
                        "theme.{$theme}::widgets.ads.templates.{$template}",
                    ], true)) {
                        $view->setPath(plugin_path("farmart-helper/resources/views/widgets/ads/{$template}.blade.php"));
                        return;
                    }
                }
            }
        });
    }
}
