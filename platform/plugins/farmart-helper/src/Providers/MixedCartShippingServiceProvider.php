<?php

namespace SparroWave\FarmartHelper\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Arr;
use SparroWave\ProductFreeShipping\Supports\ProductFreeShippingHelper;

class MixedCartShippingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        add_filter('ecommerce_shipping_data', [$this, 'adjustShippingDataForMixedFreeShipping'], 110, 2);
    }

    /**
     * In a mixed cart package (where some products have Free Shipping enabled and others
     * have Paid Shipping), free shipping products must NOT add dead weight or volumetric
     * dimensions to the package's billable shipping fee calculation.
     *
     * Only the paid products should determine the package shipping rate slab.
     */
    public function adjustShippingDataForMixedFreeShipping(array $data, mixed $products = []): array
    {
        if (! class_exists(ProductFreeShippingHelper::class) || ! ProductFreeShippingHelper::isEnabled()) {
            return $data;
        }

        $items = Arr::get($data, 'items', []);
        if (empty($items) || ! is_array($items)) {
            return $data;
        }

        $productMap = [];
        if ($products instanceof \Illuminate\Support\Collection || is_array($products)) {
            foreach ($products as $p) {
                if ($p instanceof Product) {
                    $productMap[$p->getKey()] = $p;
                    if (isset($p->cartItem?->id)) {
                        $productMap[$p->cartItem->id] = $p;
                    }
                }
            }
        }

        $freeItems = [];
        $paidItems = [];
        $paidWeight = 0.0;

        foreach ($items as $key => $item) {
            $isFree = false;

            if (isset($item['product_free_shipping'])) {
                $isFree = (bool) $item['product_free_shipping'];
            }

            if (! $isFree && isset($productMap[$key])) {
                $isFree = ProductFreeShippingHelper::isProductFreeShipping($productMap[$key]);
            }

            if (! $isFree) {
                $productId = Arr::get($item, 'id') ?: Arr::get($item, 'product_id') ?: (is_numeric($key) ? (int) $key : null);
                if ($productId) {
                    $isFree = (bool) apply_filters('ecommerce_product_has_free_shipping', false, ['id' => $productId] + (array) $item);
                    if (! $isFree) {
                        $p = Product::query()->find($productId);
                        if ($p) {
                            $isFree = ProductFreeShippingHelper::isProductFreeShipping($p);
                        }
                    }
                }
            }

            if ($isFree) {
                $freeItems[$key] = $item;
            } else {
                $paidItems[$key] = $item;
                $qty = max(1, (int) Arr::get($item, 'qty', 1));
                $w = (float) Arr::get($item, 'weight', 0);
                $paidWeight += ($w * $qty);
            }
        }

        // Only adjust if this is a mixed package (both free shipping items and paid shipping items exist)
        if (empty($freeItems) || empty($paidItems)) {
            return $data;
        }

        $data['all_items'] = $data['items'];
        $data['all_weight'] = $data['weight'];
        $data['items'] = $paidItems;
        $data['weight'] = EcommerceHelper::validateOrderWeight($paidWeight);

        return $data;
    }
}

