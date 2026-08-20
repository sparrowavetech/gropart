<?php

namespace SparroWave\ProductFreeShipping\Supports;

use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Arr;

class ProductFreeShippingHelper
{
    public static function isEnabled(): bool
    {
        return (bool) setting('product_free_shipping_enabled', true);
    }

    public static function isProductFreeShipping(mixed $product): bool
    {
        if (! self::isEnabled() || ! $product) {
            return false;
        }

        if (is_numeric($product)) {
            $p = Product::find((int) $product);
            return $p ? (bool) $p->product_free_shipping : false;
        }

        if (is_array($product)) {
            if (isset($product['product_free_shipping'])) {
                return (bool) $product['product_free_shipping'];
            }

            $productId = Arr::get($product, 'id') ?: Arr::get($product, 'product_id');
            if ($productId) {
                $p = Product::find($productId);
                return $p ? self::isProductFreeShipping($p) : false;
            }

            return false;
        }

        if (is_object($product)) {
            if (isset($product->product_free_shipping)) {
                return (bool) $product->product_free_shipping;
            }

            if (isset($product->id)) {
                $p = Product::find($product->id);
                return $p ? (bool) $p->product_free_shipping : false;
            }
        }

        return false;
    }

    public static function getBadgeText(): string
    {
        return (string) setting('product_free_shipping_badge_text', 'Free Delivery');
    }

    public static function getFreeShippingMethodTitle(): string
    {
        return (string) setting('product_free_shipping_method_title', 'Free Delivery');
    }

    public static function shouldHideOtherShippingMethods(): bool
    {
        return (bool) setting('product_free_shipping_hide_other_methods', true);
    }

    public static function extractItems(mixed $products = [], mixed $shippingData = []): array
    {
        $items = [];

        if (is_array($products)) {
            if (isset($products['items']) && is_array($products['items'])) {
                $items = $products['items'];
            } elseif (isset($products['products']) && is_array($products['products'])) {
                $items = $products['products'];
            } else {
                $items = $products;
            }
        } elseif (is_array($shippingData)) {
            if (isset($shippingData['items']) && is_array($shippingData['items'])) {
                $items = $shippingData['items'];
            } elseif (isset($shippingData['products']) && is_array($shippingData['products'])) {
                $items = $shippingData['products'];
            } else {
                $items = $shippingData;
            }
        }

        if (empty($items) && is_plugin_active('ecommerce')) {
            $cartContent = Cart::instance('cart')->content();
            if ($cartContent && $cartContent->isNotEmpty()) {
                $items = $cartContent->toArray();
            }
        }

        return $items;
    }

    public static function calculateBillableWeight(array $items): float
    {
        $billableWeight = 0.0;

        foreach ($items as $key => $item) {
            if (! is_array($item) && ! is_object($item)) {
                continue;
            }

            $productId = Arr::get($item, 'id') ?: Arr::get($item, 'product_id') ?: (is_numeric($key) ? (int) $key : null);
            $isFree = (bool) Arr::get($item, 'product_free_shipping', false);

            if (! $isFree && $productId) {
                $product = Product::find($productId);
                if ($product && self::isProductFreeShipping($product)) {
                    $isFree = true;
                }
            }

            if (! $isFree) {
                $weight = (float) Arr::get($item, 'weight', 0);
                $qty = (int) Arr::get($item, 'qty', 1);
                $billableWeight += ($weight * $qty);
            }
        }

        return max(0.0, $billableWeight);
    }

    public static function isAllItemsFreeShipping(array $items): bool
    {
        if (empty($items)) {
            return false;
        }

        foreach ($items as $key => $item) {
            if (! is_array($item) && ! is_object($item)) {
                continue;
            }

            $productId = Arr::get($item, 'id') ?: Arr::get($item, 'product_id') ?: (is_numeric($key) ? (int) $key : null);
            $isFree = (bool) Arr::get($item, 'product_free_shipping', false);

            if (! $isFree && $productId) {
                $product = Product::find($productId);
                if ($product && self::isProductFreeShipping($product)) {
                    $isFree = true;
                }
            }

            if (! $isFree) {
                return false;
            }
        }

        return true;
    }

    public static function calculateShippingRates(mixed $rates = [], mixed $products = [], mixed $shippingData = []): array
    {
        if (! self::isEnabled()) {
            return is_array($rates) ? $rates : [];
        }

        $rates = is_array($rates) ? $rates : [];
        $items = self::extractItems($products, $shippingData);

        if (self::isAllItemsFreeShipping($items)) {
            $freeRate = [
                'name' => self::getFreeShippingMethodTitle(),
                'price' => 0.0,
                'disabled' => false,
            ];

            if (self::shouldHideOtherShippingMethods()) {
                return [
                    ShippingMethodEnum::DEFAULT => [
                        'free_shipping' => $freeRate,
                    ],
                ];
            }

            $rates[ShippingMethodEnum::DEFAULT]['free_shipping'] = $freeRate;
        }

        return $rates;
    }
}
