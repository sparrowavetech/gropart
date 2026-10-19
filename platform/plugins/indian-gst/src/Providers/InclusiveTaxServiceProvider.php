<?php
namespace SparroWave\IndianGst\Providers;

use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Controllers\OrderController;
use Botble\Ecommerce\Listeners\StoreTaxComponentsListener;
use Botble\Ecommerce\Models\InvoiceItem;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\ServiceProvider;
use SparroWave\IndianGst\Http\Controllers\InclusiveOrderController;
use SparroWave\IndianGst\Listeners\PreserveInclusiveOrderPrices;
use SparroWave\IndianGst\Listeners\StoreInclusiveTaxComponents;

class InclusiveTaxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }
        $this->app->bind(StoreTaxComponentsListener::class, StoreInclusiveTaxComponents::class);
        $this->app->bind(OrderController::class, InclusiveOrderController::class);
        if (is_plugin_active('ecommerce-wholesale')) {
            $this->app->bind(\Botble\EcommerceWholesale\Listeners\SaveOrderWholesalePrices::class, PreserveInclusiveOrderPrices::class);
        }
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }
        // Admin creation omits the flag from CartItem options. Populate only missing
        // flags; never replace a persisted checkout snapshot with today's product setting.
        add_filter('ecommerce_cart_raw_subtotal_by_items', function ($subtotal, $items) {
            $changed = false;
            foreach ($items as $item) {
                if (! $item->options->has('price_includes_tax')) {
                    $product = Product::query()->find($item->id);
                    $item->options->put('price_includes_tax', (bool) $product?->original_product?->price_includes_tax);
                    $changed = true;
                }
            }
            return $changed ? Cart::rawSubTotalByItems($items) : $subtotal;
        }, 5, 2);

        // Run after wholesale's price updates/subtotal filters. Use the final cart
        // prices, scoped to the supplied vendor slice, without repricing products.
        foreach (['ecommerce_cart_raw_subtotal', 'ecommerce_cart_raw_subtotal_by_items', 'ecommerce_cart_subtotal'] as $hook) {
            add_filter($hook, function ($subtotal, $items) {
                if (! \SparroWave\IndianGst\Supports\IndianGstHelper::isEnabled()) {
                    return $subtotal;
                }
                $net = 0.0;
                foreach ($items as $item) {
                    $gross = (float) $item->price * $item->qty + (float) $item->options->get('option_price_once', 0);
                    if (EcommerceHelper::isTaxEnabled() && $item->options->get('price_includes_tax', false) && $item->taxRate > 0) {
                        $tax = EcommerceHelper::roundPrice($gross - $gross / (1 + $item->taxRate / 100));
                        $net += EcommerceHelper::roundPrice($gross) - $tax;
                    } else {
                        $net += EcommerceHelper::roundPrice($gross);
                    }
                }
                return EcommerceHelper::roundPrice($net);
            }, PHP_INT_MAX, 2);
        }

        OrderProduct::created(function (OrderProduct $item) {
            if ($item->options['price_includes_tax'] ?? false) {
                app(StoreTaxComponentsListener::class)->handle($item);
            }
        });

        OrderProduct::saved(function (OrderProduct $item) {
            if ($item->wasChanged(['price', 'qty', 'tax_amount', 'options'])) {
                StoreInclusiveTaxComponents::reconcile($item);
            }
        });

        add_filter('ecommerce_invoice_variables', function ($variables, $invoice) {
            if (is_plugin_active('indian-gst')) {
                $variables['total_price'] = $invoice->items->sum('sub_total');
            }
            return $variables;
        }, 130, 2);

        InvoiceItem::creating(function (InvoiceItem $item) {
            if (! ($item->options['price_includes_tax'] ?? false) || ! EcommerceHelper::isTaxEnabled()) {
                return;
            }
            $gross = (float) $item->price * (float) $item->qty;
            $item->sub_total = round($gross - (float) $item->tax_amount, 2);
            $item->price = $item->qty > 0 ? $item->sub_total / $item->qty : 0;
            $item->amount = round($gross - (float) $item->discount_amount, 2);
        });
    }
}
