<?php

namespace SparroWave\FarmartHelper\Providers;

use Illuminate\Support\ServiceProvider;
use Botble\Theme\Facades\Theme;

class ProductCardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        foreach (['farmart', 'farmart-child'] as $theme) {
            $this->app['view']->composer("theme.{$theme}::partials.ecommerce.product-cart-form", function ($view) use ($theme): void {
                $view->setPath(plugin_path("farmart-helper/resources/views/product-cards/{$theme}.blade.php"));
            });
        }

        add_filter('farmart_helper_product_card_extra_html', function ($html, $product): string {
            $html = (string) apply_filters(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, $html, $product);
            if (is_plugin_active('fob-request-quote')) {
                // Remove only the quote plugin's exact markup in card context.
                $button = view('plugins/fob-request-quote::button', compact('product'))->render();
                $html = str_replace($button, '', $html);
            }
            return $html;
        }, 20, 2);

        $modalRendered = false;
        // A future child-theme update may include the modal itself.
        $this->app['view']->composer([
            'theme.farmart::partials.ecommerce.quick-shop-modal',
            'theme.farmart-child::partials.ecommerce.quick-shop-modal',
        ], function () use (&$modalRendered): void {
            $modalRendered = true;
        });

        add_filter(THEME_FRONT_FOOTER, function (?string $html) use (&$modalRendered): string {
            if ($modalRendered || Theme::getThemeName() !== 'farmart-child') {
                return (string) $html;
            }

            $modalRendered = true;

            return $html . Theme::partial('ecommerce.quick-shop-modal');
        }, 20);
    }
}
