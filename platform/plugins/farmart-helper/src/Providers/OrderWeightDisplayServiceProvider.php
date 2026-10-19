<?php
namespace SparroWave\FarmartHelper\Providers;

use Illuminate\Support\ServiceProvider;

class OrderWeightDisplayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }
        // Both controllers supply weight in the configured store unit, not grams.
        // The order-info partial is shared by admin and the active vendor dashboard.
        foreach ([
            'plugins/ecommerce::orders.edit.order-info' => 'order-info.blade.php',
            'plugins/ecommerce::orders.view-incomplete-order' => 'incomplete.blade.php',
        ] as $view => $file) {
            $this->app['view']->composer($view, function ($view) use ($file): void {
                $view->setPath(plugin_path('farmart-helper/resources/views/orders/'.$file));
            });
        }
    }
}
