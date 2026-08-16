<?php

namespace SparroWave\AdvancedCod\Providers;

use SparroWave\AdvancedCod\Hooks\AdvancedCodCheckoutListener;
use SparroWave\AdvancedCod\Hooks\AdvancedCodHookListener;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;

class AdvancedCodServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/advanced-cod')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'api'])
            ->loadMigrations();

        $this->app->booted(function () {
            add_action(BASE_ACTION_META_BOXES, [AdvancedCodHookListener::class, 'addProductMetaBox'], 120, 2);
            add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [AdvancedCodHookListener::class, 'saveProductCodEligibility'], 120, 3);
            add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [AdvancedCodHookListener::class, 'saveProductCodEligibility'], 120, 3);

            add_filter('ecommerce_before_product_description', [AdvancedCodHookListener::class, 'addCodLabelToProductPage'], 120, 2);
            add_filter('ecommerce_product_detail_after_cart_actions', [AdvancedCodHookListener::class, 'addCodLabelToProductPage'], 120, 2);
            
            // Register COD settings field
            add_filter(PAYMENT_METHOD_SETTINGS_CONTENT, [AdvancedCodHookListener::class, 'addCodSettings'], 120, 2);

            if (is_plugin_active('ecommerce')) {
                // UI: Conflict Notice (replaces checkout button area)
                add_filter('ecommerce_checkout_form_after', [AdvancedCodCheckoutListener::class, 'renderCartConflictNotice'], 120);
                
                // UI: Product Labels (Badge)
                add_filter('ecommerce_cart_after_item_content', [AdvancedCodCheckoutListener::class, 'addCodLabelToCheckoutItem'], 120, 2);

                // UI: Prepayment Breakdown (inside payment method description)
                add_filter('payment_method_display_body', [AdvancedCodCheckoutListener::class, 'renderCodPrepaymentBreakdown'], 120, 3);
                
                // Logic: Partial Payment Handling
                add_filter(FILTER_ECOMMERCE_PROCESS_PAYMENT, [AdvancedCodCheckoutListener::class, 'handleAdvancedCodPrepayment'], 5, 2);
                add_filter(PAYMENT_FILTER_PAYMENT_DATA, [AdvancedCodCheckoutListener::class, 'adjustPaymentDataAmount'], 125, 2);
                add_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [AdvancedCodCheckoutListener::class, 'handlePaymentSuccess'], 200);
                
                // Logic: Hide COD if not eligible (Gatekeeper)
                add_filter('payment_methods_excluded', [AdvancedCodCheckoutListener::class, 'filterPaymentMethods'], 120);
            }
        });
    }
}
