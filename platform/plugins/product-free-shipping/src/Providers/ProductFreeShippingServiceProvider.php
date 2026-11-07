<?php

namespace SparroWave\ProductFreeShipping\Providers;

use Botble\Base\Facades\MacroableModels;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\PanelSections\SettingEcommercePanelSection;
use SparroWave\ProductFreeShipping\Supports\ProductFreeShippingHelper;

class ProductFreeShippingServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->register(HookServiceProvider::class);
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/product-free-shipping')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web'])
            ->publishAssets();

        PanelSectionManager::default()->beforeRendering(function () {
            PanelSectionManager::registerItem(
                SettingEcommercePanelSection::class,
                fn () => PanelSectionItem::make('ecommerce.settings.product-free-shipping')
                    ->setTitle(trans('plugins/product-free-shipping::product-free-shipping.name'))
                    ->withIcon('ti ti-truck-delivery')
                    ->withDescription(trans('plugins/product-free-shipping::product-free-shipping.settings.description'))
                    ->withPriority(160)
                    ->withPermission('ecommerce.settings.product-free-shipping')
                    ->withRoute('ecommerce.settings.product-free-shipping.index')
            );
        });

        $this->registerProductMacros();

        $this->app['router']->pushMiddlewareToGroup('web', \SparroWave\ProductFreeShipping\Http\Middleware\EnrichFreeShippingProductSearchMiddleware::class);

        add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function (?string $html): ?string {
            if (! \Botble\Base\Facades\AdminHelper::isInAdmin()) {
                return $html;
            }

            $assetPath = public_path('vendor/core/plugins/product-free-shipping/js/admin-product-free-shipping.js');
            $v = file_exists($assetPath) ? filemtime($assetPath) : get_cms_version();
            $jsUrl = asset('vendor/core/plugins/product-free-shipping/js/admin-product-free-shipping.js');

            return ($html ?? '') . "\n" . '<script src="' . $jsUrl . '?v=' . $v . '"></script>';
        }, 125);

        // Automatically inject universal CSS on frontend and checkout across ANY active theme
        add_filter([THEME_FRONT_HEADER, 'ecommerce_checkout_header'], function (?string $html): ?string {
            if (! ProductFreeShippingHelper::isEnabled()) {
                return $html;
            }

            $cssUrl = asset('vendor/core/plugins/product-free-shipping/css/product-free-shipping.css');

            return ($html ?? '') . "\n" . '<link rel="stylesheet" href="' . $cssUrl . '?v=' . get_cms_version() . '">';
        }, 125);
    }

    protected function registerProductMacros(): void
    {
        if (class_exists(Product::class)) {
            MacroableModels::addMacro(Product::class, 'getIsFreeShippingAttribute', function () {
                /** @var Product $this */
                return ProductFreeShippingHelper::isProductFreeShipping($this);
            });

            MacroableModels::addMacro(Product::class, 'isFreeShipping', function () {
                /** @var Product $this */
                return ProductFreeShippingHelper::isProductFreeShipping($this);
            });
        }
    }
}
