<?php

namespace Botble\ProductBundles\Supports;

use Botble\Base\Facades\Html;
use Botble\ProductBundles\Models\Bundle;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductBundlesManager
{
    public function assets(): string
    {
        // Botble publishes plugin assets under: public/vendor/core/plugins/{plugin}/...
        // Using this canonical path avoids 404s on many Botble installations.
        $css = asset('vendor/core/plugins/product-bundles/css/bundles.css');
        $js = asset('vendor/core/plugins/product-bundles/js/bundles.js');

        $i18n = [
            'request_failed' => trans('plugins/product-bundles::bundles.front.request_failed'),
            'group_between' => trans('plugins/product-bundles::bundles.front.group_between'),
            'bundle_added' => trans('plugins/product-bundles::bundles.front.bundle_added'),
            'failed_to_add' => trans('plugins/product-bundles::bundles.front.failed_to_add'),
        ];

        $i18nScript = '<script>window.ProductBundlesI18n=' . json_encode($i18n, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';</script>';

        if (class_exists(Html::class)) {
            return Html::style($css) . $i18nScript . Html::script($js);
        }

        return sprintf('<link rel="stylesheet" href="%s">%s<script src="%s" defer></script>', e($css), $i18nScript, e($js));
    }

    /**
     * Ensure each bundle has a dedicated eCommerce Product so it can be purchased like a normal product.
     *
     * This method is defensive across Botble versions by only touching product columns that exist.
     */
    public function syncBundleProduct(Bundle $bundle): void
    {
        $productClass = pb_product_model_class();
        if (! $productClass || ! class_exists($productClass)) {
            return;
        }

        // Make sure bundle relations exist for price sync.
        $bundle->loadMissing(['items.product', 'groups.items.product']);

        $product = null;
        $table = (new $productClass())->getTable();

        if ($bundle->ecommerce_product_id) {
            try {
                $product = $productClass::query()->find((int) $bundle->ecommerce_product_id);
            } catch (\Throwable $e) {
                $product = null;
            }
        }

        if (! $product) {
            $product = new $productClass();
        }

        $set = function (string $column, $value) use ($product, $table) {
            try {
                if (Schema::hasColumn($table, $column)) {
                    $product->{$column} = $value;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        };

        $price = pb_bundle_total_price($bundle);

        // Core identity
        $set('name', (string) $bundle->name);
        $set('description', (string) ($bundle->description ?? ''));
        $set('content', (string) ($bundle->description ?? ''));
        $set('price', $price);

        // SKU
        $set('sku', 'BUNDLE-' . $bundle->id);

        // Slug: avoid collision with real products.
        $slug = 'bundle-' . ($bundle->slug ?: $bundle->id);
        $set('slug', Str::slug($slug));

        // Status / visibility
        // We MUST keep bundle products purchasable through the normal cart flow (public.cart.add-to-cart)
        // so checkout behaves exactly like a normal product.
        // Some stores will later exclude these from product listings via theme/query filters if needed.
        if (Schema::hasColumn($table, 'status')) {
            // Botble commonly uses: published|draft|pending
            $set('status', 'published');
        } elseif (Schema::hasColumn($table, 'status_id')) {
            // Fallback: 1 is commonly "published" in many schemas.
            $set('status_id', 1);
        }

        // Extra visibility flags (optional across Botble versions)
        if (Schema::hasColumn($table, 'is_published')) {
            $set('is_published', 1);
        }
        if (Schema::hasColumn($table, 'is_visible')) {
            $set('is_visible', 1);
        }
        if (Schema::hasColumn($table, 'is_hidden')) {
            // Keep default visible; stores can hide via theme filters if desired.
            $set('is_hidden', 0);
        }

        // Price fields that exist on many Botble versions
        if (Schema::hasColumn($table, 'sale_price')) {
            $set('sale_price', $price);
        }
        if (Schema::hasColumn($table, 'original_price')) {
            $set('original_price', $price);
        }

        $set('is_variation', 0);
        $set('with_storehouse_management', 0);

        // Image(s)
        $img = trim((string) ($bundle->image ?? ''));
        if ($img !== '') {
            $set('image', $img);

            // Many Botble versions store product images as JSON array in `images`.
            if (Schema::hasColumn($table, 'images')) {
                $set('images', json_encode([$img]));
            }
        }

        try {
            $product->save();
        } catch (\Throwable $e) {
            // If product save fails, do not block bundle saving.
            return;
        }

        try {
            if ((int) $bundle->ecommerce_product_id !== (int) $product->id) {
                $bundle->ecommerce_product_id = (int) $product->id;
                $bundle->saveQuietly();
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
