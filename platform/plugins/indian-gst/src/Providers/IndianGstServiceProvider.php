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
use SparroWave\IndianGst\Hooks\IndianGstShippingListener;
use SparroWave\IndianGst\Hooks\MarketplaceGstHookListener;
use SparroWave\IndianGst\Hooks\ProductHsnHookListener;
use SparroWave\IndianGst\Supports\IndianGstHelper;

class IndianGstServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

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

            // Checkout Item Tax Label (Positioned right below price)
            add_filter('ecommerce_cart_after_item_content', function (?string $html, $cartItem): ?string {
                if (! \Botble\Ecommerce\Facades\EcommerceHelper::isTaxEnabled()) {
                    return $html;
                }

                $taxRate = $cartItem->taxRate ?? 0;
                if ($taxRate < 0) {
                    return $html;
                }

                $label = \SparroWave\IndianGst\Supports\IndianGstHelper::getTaxSlabTitle($taxRate);

                return ($html ?? '') . '<span class="ec-checkout-tax-badge-data d-none" data-tax-label="' . e($label) . '"></span>';
            }, 120, 2);

            // Checkout CSS: Hide default left-side item tax text
            add_filter('ecommerce_checkout_header', function (?string $html): string {
                return ($html ?? '') . '<style>.ec-checkout-item-tax { display: none !important; }</style>';
            });

            // Checkout Footer Script: Reposition item tax badge under price & update summary tax label
            $checkoutScriptCallback = function (?string $html): string {
                $cartTaxClasses = \SparroWave\IndianGst\Supports\IndianGstHelper::getFormattedCartTaxClassesName();

                $script = '<script>
                    (function () {
                        const currentTaxSummary = ' . json_encode($cartTaxClasses ? ('(' . $cartTaxClasses . ')') : '') . ';

                        function formatCheckoutGstUI() {
                            // 1. Reposition tax badge under each product price in the right column
                            document.querySelectorAll(".cart-item, .checkout-products-list .row, .order-item-list tr, .ec-checkout-product-row, .checkout-products-item, [class*=\"checkout-product\"]").forEach(function (row) {
                                const badgeData = row.querySelector(".ec-checkout-tax-badge-data");
                                const priceCol = row.querySelector(".col-auto.text-end, .text-end");
                                if (badgeData && priceCol) {
                                    const label = badgeData.getAttribute("data-tax-label");
                                    if (label && !priceCol.querySelector(".ec-checkout-item-tax-badge")) {
                                        const p = document.createElement("p");
                                        p.className = "mb-0 ec-checkout-item-tax-badge text-end mt-1";
                                        p.innerHTML = "<small class=\"text-muted d-inline-block px-1 rounded bg-light border\" style=\"font-size: 11px; font-weight: 500;\">" + label + "</small>";
                                        priceCol.appendChild(p);
                                    }
                                }
                            });

                            // 2. Format Subtotal summary tax line (e.g. Tax: ₹ 917.15 (GST@18%))
                            const taxSmallEls = document.querySelectorAll(".ec-checkout-tax-row small, .tax-price-text small");
                            taxSmallEls.forEach(function (el) {
                                if (currentTaxSummary) {
                                    el.textContent = currentTaxSummary;
                                } else {
                                    let txt = el.textContent || "";
                                    txt = txt.replace(/([a-zA-Z0-9@\s]+%\s*)-\s*\d+(\.\d+)?%/g, "$1");
                                    txt = txt.replace(/-\s*(\d+(\.\d+)?%)/g, "$1");
                                    el.textContent = txt;
                                }
                            });
                        }

                        if (document.readyState === "loading") {
                            document.addEventListener("DOMContentLoaded", formatCheckoutGstUI);
                        } else {
                            formatCheckoutGstUI();
                        }
                        window.addEventListener("load", formatCheckoutGstUI);
                        document.addEventListener("payment-form-reloaded", formatCheckoutGstUI);
                        document.addEventListener("checkout-order-updated", formatCheckoutGstUI);
                        if (window.jQuery) {
                            window.jQuery(document).ajaxComplete(function() {
                                setTimeout(formatCheckoutGstUI, 50);
                            });
                        }
                        setInterval(formatCheckoutGstUI, 400);
                    })();
                </script>';

                return ($html ?? '') . $script;
            };

            add_filter('ecommerce_checkout_footer', $checkoutScriptCallback);
            add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function ($html) use ($checkoutScriptCallback) {
                if (! request()->routeIs('public.checkout*') && ! request()->is('checkout*')) {
                    return $html;
                }
                return $checkoutScriptCallback($html);
            });

            // Shipping Router
            add_filter('handle_shipping_fee', [IndianGstShippingListener::class, 'checkVendorShipping'], 120, 2);

            // Admin Main Dashboard Menu
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-indian-gst',
                'priority' => 205,
                'parent_id' => null,
                'name' => 'Indian GST System',
                'icon' => 'ti ti-receipt-tax',
                'url' => fn () => route('indian-gst.slabs.index'),
                'permissions' => ['ecommerce.settings.indian-gst'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-indian-gst-slabs',
                'priority' => 1,
                'parent_id' => 'cms-plugins-indian-gst',
                'name' => 'Tax Slabs & Products',
                'icon' => 'ti ti-category',
                'url' => fn () => route('indian-gst.slabs.index'),
                'permissions' => ['ecommerce.settings.indian-gst'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-indian-gst-settings',
                'priority' => 2,
                'parent_id' => 'cms-plugins-indian-gst',
                'name' => 'GST Settings',
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

            // Inject Tax Slab Products badge on Taxes Settings page without modifying core blade files
            add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function ($html) {
                if (! request()->routeIs('tax.*') && ! request()->routeIs('ecommerce.settings.taxes*') && ! request()->is('admin/ecommerce/taxes*') && ! request()->is('admin/ecommerce/settings/taxes*')) {
                    return $html;
                }

                $taxStats = \DB::table('ec_tax_products')
                    ->select('tax_id', \DB::raw('count(*) as count'))
                    ->groupBy('tax_id')
                    ->pluck('count', 'tax_id')
                    ->toArray();

                $script = '<script>
                    (function () {
                        const taxStats = ' . json_encode($taxStats) . ';
                        function injectTaxBadges() {
                            const containers = document.querySelectorAll("[data-tax-id], .tax-card");
                            containers.forEach(function (el) {
                                let taxId = el.getAttribute("data-tax-id");
                                if (!taxId) {
                                    const editBtn = el.querySelector(".btn-edit-tax, [href*=\"taxes/edit\"], [href*=\"taxes/\"]");
                                    if (editBtn) {
                                        const href = editBtn.getAttribute("href") || "";
                                        const match = href.match(/taxes\/edit\/(\d+)/) || href.match(/taxes\/(\d+)/);
                                        if (match) taxId = match[1];
                                    }
                                }
                                if (!taxId) return;

                                const count = taxStats[taxId] || 0;
                                const headerWrap = el.querySelector(".card-header .d-flex") || el.querySelector(".card-header") || el.querySelector("h4")?.parentElement;
                                if (headerWrap && !el.querySelector(".tax-products-badge")) {
                                    const badge = document.createElement("a");
                                    badge.className = "badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none py-1 px-2 tax-products-badge";
                                    badge.href = "/admin/indian-gst/slabs/" + taxId + "/products";
                                    badge.title = "Click to view mapped products in Indian GST System";
                                    badge.innerHTML = "<i class=\"ti ti-package me-1\"></i> " + count + " products";
                                    
                                    const titleEl = headerWrap.querySelector(".card-title, h4");
                                    if (titleEl && titleEl.nextSibling) {
                                        headerWrap.insertBefore(badge, titleEl.nextSibling);
                                    } else {
                                        headerWrap.appendChild(badge);
                                    }
                                }
                            });
                        }

                        if (document.readyState === "loading") {
                            document.addEventListener("DOMContentLoaded", injectTaxBadges);
                        } else {
                            injectTaxBadges();
                        }
                        window.addEventListener("load", injectTaxBadges);
                        setTimeout(injectTaxBadges, 300);
                        setTimeout(injectTaxBadges, 1000);
                    })();
                </script>';

                return $html . $script;
            });
        });
    }
}
