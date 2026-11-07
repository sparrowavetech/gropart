<?php

namespace SparroWave\FarmartHelper\Providers;

use Botble\Base\Facades\AdminHelper;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class OrderCreateHelperServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        // Exclude external / affiliate products from admin order create search
        Product::addGlobalScope('farmart_exclude_external_from_order_create', function (Builder $query): void {
            $req = request();
            $isOrderCreateSearch = $req && (
                $req->routeIs('products.get-all-products-and-variations')
                || (method_exists($req, 'is') && $req->is('*products/get-all-products-and-variations*'))
            );

            if ($isOrderCreateSearch) {
                $query->where(function (Builder $q): void {
                    $q->whereNull('ec_products.external_url')
                      ->orWhere('ec_products.external_url', '');
                });
            }
        });

        // Inject active coupon codes and modal helpers into Order Create page
        add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function (?string $html): ?string {
            if (! AdminHelper::isInAdmin()) {
                return $html;
            }

            $req = request();
            $isOrderCreatePage = $req && (
                $req->routeIs('orders.create')
                || (method_exists($req, 'is') && ($req->is('*ecommerce/orders/create*') || $req->is('*orders/create*')))
            );

            if (! $isOrderCreatePage) {
                return $html;
            }

            $activeCoupons = [];
            try {
                $activeCoupons = Discount::query()
                    ->whereNotNull('code')
                    ->where('code', '!=', '')
                    ->active()
                    ->available()
                    ->select(['id', 'code', 'title', 'value', 'type_option'])
                    ->latest()
                    ->get()
                    ->map(function ($item) {
                        $discountLabel = $item->type_option === 'percentage'
                            ? $item->value . '% off'
                            : format_price($item->value) . ' off';

                        return [
                            'code' => $item->code,
                            'title' => $item->title ?: $item->code,
                            'value' => $item->value,
                            'type' => $item->type_option,
                            'label' => $item->code . ($item->title ? ' (' . $item->title . ' - ' . $discountLabel . ')' : ' (' . $discountLabel . ')'),
                        ];
                    })
                    ->values()
                    ->toArray();
            } catch (\Throwable $e) {
                // Ignore query exceptions gracefully
            }

            $couponsJson = json_encode($activeCoupons);
            $jsUrl = asset('vendor/core/plugins/farmart-helper/js/admin-order-create-helper.js');

            $injected = <<<HTML
<style id="farmart-order-create-modal-styles">
    #add-customer .modal-dialog,
    #edit-address .modal-dialog {
        max-width: 760px !important;
        width: 90% !important;
    }
    .farmart-phone-input-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        width: 100% !important;
    }
    .farmart-phone-input-group .farmart-phone-country-select {
        width: 125px !important;
        min-width: 125px !important;
        max-width: 125px !important;
        flex: 0 0 125px !important;
        padding-left: 0.5rem !important;
        padding-right: 1.5rem !important;
        font-size: 0.875rem !important;
        cursor: pointer;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    .farmart-phone-input-group input.form-control,
    .farmart-phone-input-group input {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
</style>
<script>
    window.__activeDiscountCoupons = {$couponsJson};
</script>
<script src="{$jsUrl}?v={$this->getAssetVersion()}"></script>
HTML;

            return ($html ?? '') . "\n" . $injected;
        }, 135);
    }

    protected function getAssetVersion(): string
    {
        $path = public_path('vendor/core/plugins/farmart-helper/js/admin-order-create-helper.js');

        return file_exists($path) ? (string) filemtime($path) : (function_exists('get_cms_version') ? get_cms_version() : '1.0.0');
    }
}

