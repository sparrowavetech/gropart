<?php

namespace SparroWave\ProductFreeShipping\Providers;

use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
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
                    ->withRoute('ecommerce.settings.product-free-shipping.index')
            );
        });

        // Automatically inject universal CSS on frontend and checkout across ANY active theme
        add_filter([THEME_FRONT_HEADER, 'ecommerce_checkout_header'], function (?string $html): ?string {
            if (! ProductFreeShippingHelper::isEnabled()) {
                return $html;
            }

            $cssUrl = asset('vendor/core/plugins/product-free-shipping/css/product-free-shipping.css');

            return ($html ?? '') . "\n" . '<link rel="stylesheet" href="' . $cssUrl . '?v=' . get_cms_version() . '">';
        }, 125);
    }
}
