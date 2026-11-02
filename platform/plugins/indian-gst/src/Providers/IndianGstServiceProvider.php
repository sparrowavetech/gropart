<?php

namespace SparroWave\IndianGst\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Events\ProductVariationCreated;
use Botble\Ecommerce\PanelSections\SettingEcommercePanelSection;
use Illuminate\Support\Facades\Event;
use SparroWave\IndianGst\Hooks\IndianGstInvoiceListener;
use SparroWave\IndianGst\Hooks\MarketplaceGstHookListener;
use SparroWave\IndianGst\Hooks\ProductHsnHookListener;
use SparroWave\IndianGst\Supports\IndianGstHelper;

class IndianGstServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->register(InclusiveTaxServiceProvider::class);
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/indian-gst')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web'])
            ->loadMigrations()
            ->publishAssets();

        $this->app->booted(function () {
            // Auto-deploy Indian GST Invoice Template if not present on fresh installation
            if (IndianGstHelper::isEnabled()) {
                $destination = storage_path('app/templates/ecommerce/invoice.tpl');
                $source = plugin_path('indian-gst/resources/templates/invoice.tpl');
                if (! \Illuminate\Support\Facades\File::exists($destination) && \Illuminate\Support\Facades\File::exists($source)) {
                    \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($destination));
                    \Illuminate\Support\Facades\File::copy($source, $destination);
                }
            }

            if (is_in_admin(true)) {
                Assets::addScriptsDirectly('vendor/core/plugins/indian-gst/js/product-hsn.js');
            }

            // HSN Code Form & Persistence Hooks
            add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [ProductHsnHookListener::class, 'saveProductHsnCode'], 121, 3);
            add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [ProductHsnHookListener::class, 'saveProductHsnCode'], 121, 3);
            add_filter('ecommerce_product_variation_form_start', [ProductHsnHookListener::class, 'renderHsnField'], 120, 2);

            Event::listen(ProductVariationCreated::class, [ProductHsnHookListener::class, 'saveVariationHsnCode']);

            // Marketplace Store GSTIN & Vendor Managed Shipping
            if (is_plugin_active('marketplace')) {
                add_filter(BASE_FILTER_BEFORE_RENDER_FORM, [MarketplaceGstHookListener::class, 'addStoreGstFields'], 999, 2);
                add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [MarketplaceGstHookListener::class, 'saveStoreGstData'], 125, 3);
                add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [MarketplaceGstHookListener::class, 'saveStoreGstData'], 125, 3);
            }

            // Dynamic Invoice Variables (IGST vs CGST+SGST, Seller GSTIN, HSN)
            add_filter('ecommerce_invoice_variables', [IndianGstInvoiceListener::class, 'handleInvoiceVariables'], 120, 2);

            // Render through the Ecommerce item hook so every theme can place it.
            add_filter('ecommerce_cart_after_item_content', function (?string $html, $cartItem): ?string {
                if (! IndianGstHelper::isEnabled() || ! \Botble\Ecommerce\Facades\EcommerceHelper::isTaxEnabled()) {
                    return $html;
                }
                $taxRate = (float) ($cartItem->taxRate ?? 0);
                if ($taxRate <= 0) {
                    return $html;
                }
                $label = IndianGstHelper::getTaxSlabTitle($taxRate);
                return ($html ?? '') . '<small class="ec-checkout-item-tax d-block">' . e($label) . '</small>';
            }, 120, 2);

            // Admin Main Dashboard Menu
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-indian-gst',
                'priority' => 205,
                'parent_id' => null,
                'name' => trans('plugins/indian-gst::indian-gst.name'),
                'icon' => 'ti ti-receipt-tax',
                'url' => fn () => route('indian-gst.slabs.index'),
                'permissions' => ['indian-gst.slabs.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-indian-gst-slabs',
                'priority' => 1,
                'parent_id' => 'cms-plugins-indian-gst',
                'name' => trans('plugins/indian-gst::indian-gst.slabs.title'),
                'icon' => 'ti ti-category',
                'url' => fn () => route('indian-gst.slabs.index'),
                'permissions' => ['indian-gst.slabs.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-indian-gst-settings',
                'priority' => 2,
                'parent_id' => 'cms-plugins-indian-gst',
                'name' => trans('plugins/indian-gst::indian-gst.settings.menu'),
                'icon' => 'ti ti-settings',
                'url' => fn () => route('ecommerce.settings.indian-gst.index'),
                'permissions' => ['ecommerce.settings.indian-gst'],
            ]);

            // Admin Settings Page registration
            PanelSectionManager::default()->beforeRendering(function () {
                PanelSectionManager::registerItem(
                    SettingEcommercePanelSection::class,
                    fn () => PanelSectionItem::make('ecommerce.settings.indian-gst')
                        ->setTitle(trans('plugins/indian-gst::indian-gst.settings.title'))
                        ->withIcon('ti ti-receipt-tax')
                        ->withDescription(trans('plugins/indian-gst::indian-gst.settings.description'))
                        ->withPriority(150)
                        ->withRoute('ecommerce.settings.indian-gst.index')
                );
            });

        });
    }
}
