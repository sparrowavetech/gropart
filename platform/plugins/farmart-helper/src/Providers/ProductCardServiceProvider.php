<?php

namespace SparroWave\FarmartHelper\Providers;

use Illuminate\Support\ServiceProvider;

class ProductCardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        // Farmart Child owns its template; retain parent-theme compatibility.
        $this->app['view']->composer('theme.farmart::partials.ecommerce.product-cart-form', function ($view): void {
            $view->setPath(plugin_path('farmart-helper/resources/views/product-cards/farmart.blade.php'));
        });

        $this->app['view']->composer('plugins/fob-request-quote::button', function ($view): void {
            if (request()->attributes->get('farmart_helper_card_context', false)) {
                $view->setPath(plugin_path('farmart-helper/resources/views/product-cards/quote-placeholder.blade.php'));
            }
        });
        add_filter('farmart_helper_product_card_extra_html', function ($html, $product): string {
            $previous = request()->attributes->get('farmart_helper_card_context', false);
            request()->attributes->set('farmart_helper_card_context', true);
            try {
                return (string) apply_filters(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, $html, $product);
            } finally {
                request()->attributes->set('farmart_helper_card_context', $previous);
            }
        }, 20, 2);
    }
}
