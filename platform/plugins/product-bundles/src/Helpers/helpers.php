<?php

use Botble\ProductBundles\Models\Bundle;

if (! function_exists('product_bundles_for_product')) {
    /**
     * Get active bundles attached to a product.
     */
    function product_bundles_for_product(int $productId)
    {
        return Bundle::query()
            ->where('is_active', 1)
            ->whereHas('products', fn ($q) => $q->where('product_id', $productId))
            ->with(['items', 'groups.items'])
            ->orderBy('id', 'desc')
            ->get();
    }
}

if (! function_exists('pb_bundle_url')) {
    function pb_bundle_url(Bundle $bundle): string
    {
        try {
            // Public route binds by slug: {bundle:slug}
            return route('product-bundles.show', $bundle->slug);
        } catch (Throwable $e) {
            return '/bundles/' . urlencode((string) $bundle->slug);
        }
    }
}

if (! function_exists('pb_image_url')) {
    function pb_image_url(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            // Try Botble default image
            if (class_exists('RvMedia')) {
                try {
                    return RvMedia::getDefaultImage();
                } catch (Throwable $e) {
                    // ignore
                }
            }
            return '';
        }

        if (class_exists('RvMedia')) {
            try {
                return RvMedia::getImageUrl($path);
            } catch (Throwable $e) {
                // ignore
            }
        }

        return $path;
    }
}

if (! function_exists('pb_format_price')) {
    function pb_format_price(float $amount): string
    {
        if (function_exists('format_price')) {
            try {
                return format_price($amount);
            } catch (Throwable $e) {
                // ignore
            }
        }

        return number_format($amount, 0, '.', ',');
    }
}

if (! function_exists('pb_guess_product_price')) {
    /**
     * Best-effort product price resolver across Botble eCommerce versions.
     * Returns a numeric price for display/estimation purposes.
     */
    function pb_guess_product_price($product): float
    {
        if (! $product) {
            return 0.0;
        }

        // Prefer original product for variations when available.
        try {
            if (isset($product->original_product) && $product->original_product) {
                $product = $product->original_product;
            }
        } catch (Throwable $e) {
            // ignore
        }

        $candidates = [
            'front_sale_price',
            'final_price',
            'sale_price',
            'price',
            'original_price',
        ];

        foreach ($candidates as $prop) {
            try {
                if (isset($product->{$prop}) && is_numeric($product->{$prop})) {
                    $val = (float) $product->{$prop};
                    if ($val > 0) {
                        return $val;
                    }
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        $methods = [
            'getFinalPrice',
            'getPrice',
        ];

        foreach ($methods as $m) {
            try {
                if (method_exists($product, $m)) {
                    $val = (float) $product->{$m}();
                    if ($val > 0) {
                        return $val;
                    }
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        return 0.0;
    }
}

if (! function_exists('pb_product_model_class')) {
    /**
     * Resolve the Product model class used by the current installation.
     */
    function pb_product_model_class(): ?string
    {
        if (class_exists('Botble\\Ecommerce\\Models\\Product')) {
            return 'Botble\\Ecommerce\\Models\\Product';
        }

        if (class_exists('App\\Models\\Product')) {
            return 'App\\Models\\Product';
        }

        return null;
    }
}

if (! function_exists('pb_bundle_total_price')) {
    /**
     * Compute the bundle total price (single line item) based on pricing rules.
     * - fixed_total: pricing_value is the bundle total
     * - percent_off: discount percent from the sum of items
     * - amount_off: discount amount from the sum of items
     */
    function pb_bundle_total_price(Bundle $bundle): float
    {
        $bundle->loadMissing(['items.product', 'groups.items.product']);

        $productClass = pb_product_model_class();

        $sumBase = 0.0;

        if ($bundle->type === 'fixed') {
            foreach ($bundle->items as $it) {
                $p = null;

                if ($productClass && $it->variation_id) {
                    try {
                        $p = $productClass::query()->find((int) $it->variation_id);
                    } catch (Throwable $e) {
                        $p = null;
                    }
                }

                $p = $p ?: $it->product;
                $sumBase += pb_guess_product_price($p) * max(1, (int) $it->quantity);
            }
        } else {
            // For mix bundles, we cannot know the exact selection here.
            // Use a conservative estimate: cheapest min selections per group.
            foreach ($bundle->groups as $g) {
                $min = max(0, (int) $g->choose_min);
                if ($min <= 0) {
                    continue;
                }

                $prices = $g->items->map(function ($opt) use ($productClass) {
                    $p = null;
                    if ($productClass && $opt->variation_id) {
                        try {
                            $p = $productClass::query()->find((int) $opt->variation_id);
                        } catch (Throwable $e) {
                            $p = null;
                        }
                    }
                    $p = $p ?: $opt->product;
                    return pb_guess_product_price($p);
                })->filter(fn ($v) => is_numeric($v) && $v >= 0)->sort()->values();

                $sumBase += $prices->take($min)->sum();
            }
        }

        $type = (string) ($bundle->pricing_type ?? 'percent_off');
        $value = (float) ($bundle->pricing_value ?? 0);

        if ($type === 'fixed_total') {
            return max(0.0, $value);
        }

        $target = $sumBase;

        if ($type === 'percent_off') {
            $target = max(0.0, $sumBase * (1 - max(0.0, min(100.0, $value)) / 100));
        } elseif ($type === 'amount_off') {
            $target = max(0.0, $sumBase - max(0.0, $value));
        }

        return max(0.0, $target);
    }
}
