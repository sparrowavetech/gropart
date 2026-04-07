<?php

namespace SparroWave\IndianGst\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;

class IndianGstServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/indian-gst')
            ->loadAndPublishTranslations()
            ->loadAndPublishViews();

        $this->app->booted(function () {
             config(['plugins.ecommerce.general.invoice_template' => 'plugins/indian-gst::invoices.template']);
             
             // Checkout/Cart modifications
             add_filter('ecommerce_cart_after_tax_total_label', [\SparroWave\IndianGst\Hooks\IndianGstCheckoutListener::class, 'modifyCheckoutTaxLabel'], 120, 2);

             // Override "amount.blade.php" by prepending namespace
             $this->app['view']->prependNamespace('plugins/ecommerce', plugin_path('indian-gst/resources/views/overrides'));
        });
    }
}
