<?php

namespace Botble\ProductBundles\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\ProductBundles\Http\Requests\BundleAddToCartRequest;
use Botble\ProductBundles\Models\Bundle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BundlePublicController extends BaseController
{
    protected function productModelClass(): string
    {
        return class_exists('Botble\\Ecommerce\\Models\\Product')
            ? 'Botble\\Ecommerce\\Models\\Product'
            : 'App\\Models\\Product';
    }

    public function show(Bundle $bundle)
    {
        $bundle->load(['items.product', 'groups.items.product']);

        if (! $bundle->is_active || ! $bundle->isInDateRange()) {
            abort(404);
        }

        // Ensure fixed bundles have a purchasable product id so the normal cart flow works.
        if ((string) $bundle->type === 'fixed' && ! $bundle->ecommerce_product_id) {
            try {
                app('product-bundles')->syncBundleProduct($bundle);
                $bundle->refresh();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $productView = $this->renderBundleAsProduct($bundle);
        if ($productView) {
            return $productView;
        }

        // Try to render within the current theme if available.
        if (class_exists('Botble\\Theme\\Facades\\Theme')) {
            try {
                \Botble\Theme\Facades\Theme::setTitle($bundle->name);

                // Some Botble versions support Theme::scope($key, $data, $view)
                try {
                    return \Botble\Theme\Facades\Theme::scope(
                        'product-bundles.bundle-detail',
                        ['bundle' => $bundle],
                        'plugins/product-bundles::front.bundle-detail'
                    )->render();
                } catch (\Throwable $e) {
                    // Fallback to Theme::scope($view, $data)
                    try {
                        return \Botble\Theme\Facades\Theme::scope(
                            'plugins/product-bundles::front.bundle-detail',
                            ['bundle' => $bundle]
                        )->render();
                    } catch (\Throwable $e2) {
                        // ignore
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return view('plugins/product-bundles::front.bundle-detail', ['bundle' => $bundle]);
    }

    public function renderForProduct(int $productId)
    {
        $bundles = product_bundles_for_product($productId)
            ->filter(fn (Bundle $b) => $b->isInDateRange());

        return view('plugins/product-bundles::front.box', [
            'productId' => $productId,
            'bundles' => $bundles,
        ]);
    }

    public function addToCart(BundleAddToCartRequest $request): JsonResponse
    {
        $bundle = Bundle::query()
            ->with(['items.product', 'groups.items.product'])
            ->findOrFail((int) $request->input('bundle_id'));

        if (! $bundle->is_active || ! $bundle->isInDateRange()) {
            return response()->json([
                'error' => true,
                'message' => trans('plugins/product-bundles::bundles.front.bundle_not_available'),
            ], 422);
        }

        // Ensure the bundle has its own purchasable product.
        try {
            app('product-bundles')->syncBundleProduct($bundle);
            $bundle->refresh();
        } catch (\Throwable $e) {
            // ignore
        }

        if (! $bundle->ecommerce_product_id) {
            return response()->json([
                'error' => true,
                'message' => trans('plugins/product-bundles::bundles.front.nothing_to_add'),
            ], 422);
        }

        $qty = max(1, (int) $request->input('qty', 1));

        // Build component list (for later fulfillment / display).
        $components = [];
        $selected = [];

        $productClass = $this->productModelClass();
        if ($bundle->type === 'fixed') {
            foreach ($bundle->items as $item) {
                $components[] = [
                    'product_id' => (int) $item->product_id,
                    'variation_id' => $item->variation_id ? (int) $item->variation_id : null,
                    'quantity' => max(1, (int) $item->quantity),
                ];
            }
        } else {
            $selections = (array) $request->input('selections', []);

            foreach ($bundle->groups as $group) {
                $picked = Arr::get($selections, (string) $group->id, []);
                $picked = is_array($picked) ? array_values(array_filter(array_map('intval', $picked))) : [];

                $pickedCount = count($picked);
                if ($pickedCount < $group->choose_min || $pickedCount > $group->choose_max) {
                    return response()->json([
                        'error' => true,
                        'message' => trans('plugins/product-bundles::bundles.front.group_requires_between', [
                            'name' => $group->name,
                            'min' => $group->choose_min,
                            'max' => $group->choose_max,
                        ]),
                    ], 422);
                }

                $allowed = $group->items
                    ->map(fn ($it) => (int) ($it->variation_id ?: $it->product_id))
                    ->values()
                    ->all();

                foreach ($picked as $pid) {
                    if (! in_array($pid, $allowed, true)) {
                        return response()->json([
                            'error' => true,
                            'message' => trans('plugins/product-bundles::bundles.front.invalid_selection'),
                        ], 422);
                    }

                    $opt = $group->items->first(function ($it) use ($pid) {
                        return (int) ($it->variation_id ?: $it->product_id) === (int) $pid;
                    });

                    if ($opt) {
                        $components[] = [
                            'product_id' => (int) $opt->product_id,
                            'variation_id' => $opt->variation_id ? (int) $opt->variation_id : null,
                            'quantity' => 1,
                            'group' => (string) $group->name,
                        ];
                        $selected[] = (int) $pid;
                    }
                }
            }
        }

        if (! $components) {
            return response()->json([
                'error' => true,
                'message' => trans('plugins/product-bundles::bundles.front.nothing_to_add'),
            ], 422);
        }

        // Price for the bundle line item.
        $bundlePrice = pb_bundle_total_price($bundle);

        // For mix bundles, if pricing is based on discounting the picked items,
        // compute the exact price from selected options (unless fixed_total).
        if ($bundle->type === 'mix' && $selected && (string) $bundle->pricing_type !== 'fixed_total') {
            $bundlePrice = $this->computeMixPriceFromSelections($bundle, $selected);
        }

        $options = [
            'bundle_id' => $bundle->id,
            'bundle_name' => $bundle->name,
            'bundle_type' => $bundle->type,
            'bundle_components' => $components,
        ];

        $this->cartAdd(
            (int) $bundle->ecommerce_product_id,
            (string) $bundle->name,
            $qty,
            (float) $bundlePrice,
            $options
        );

        return response()->json([
            'error' => false,
            'message' => trans('plugins/product-bundles::bundles.front.bundle_added'),
        ]);
    }

    protected function computeMixPriceFromSelections(Bundle $bundle, array $selectedIds): float
    {
        $selectedIds = array_values(array_filter(array_map('intval', $selectedIds)));
        if (! $selectedIds) {
            return pb_bundle_total_price($bundle);
        }

        $productClass = $this->productModelClass();
        if (! $productClass) {
            return pb_bundle_total_price($bundle);
        }

        $sum = 0.0;
        foreach ($selectedIds as $pid) {
            try {
                $p = $productClass::query()->find((int) $pid);
            } catch (\Throwable $e) {
                $p = null;
            }
            if ($p) {
                $sum += $this->getUnitPrice($p);
            }
        }

        $type = (string) ($bundle->pricing_type ?? 'percent_off');
        $value = (float) ($bundle->pricing_value ?? 0);

        if ($type === 'percent_off') {
            return max(0.0, $sum * (1 - max(0.0, min(100.0, $value)) / 100));
        }
        if ($type === 'amount_off') {
            return max(0.0, $sum - max(0.0, $value));
        }

        return max(0.0, $sum);
    }

    protected function cartAdd(int $id, string $name, int $qty, float $price, array $options = []): void
    {
        $facades = [
            'Botble\\Ecommerce\\Facades\\Cart',
            'Gloudemans\\Shoppingcart\\Facades\\Cart',
            'Cart',
        ];

        foreach ($facades as $class) {
            if (! class_exists($class)) {
                continue;
            }

            try {
                $cart = $class;

                if (method_exists($class, 'instance')) {
                    $cart = $class::instance('cart');
                }

                if (is_string($cart) && method_exists($cart, 'add')) {
                    $cart::add($id, $name, $qty, $price, $options);
                    return;
                }

                if (is_object($cart) && method_exists($cart, 'add')) {
                    $cart->add($id, $name, $qty, $price, $options);
                    return;
                }
            } catch (\Throwable $e) {
                // try next facade
            }
        }

        // As a last resort, do nothing. The store can still use default add-to-cart.
    }

    protected function getUnitPrice($product): float
    {
        // Common Botble patterns: getFinalPrice(), front_sale_price, price, original_price
        foreach (['getFinalPrice', 'getPrice'] as $method) {
            if (is_object($product) && method_exists($product, $method)) {
                $v = (float) $product->{$method}();
                if ($v > 0) {
                    return $v;
                }
            }
        }

        foreach (['front_sale_price', 'sale_price', 'price', 'original_price'] as $prop) {
            if (is_object($product) && isset($product->{$prop}) && (float) $product->{$prop} > 0) {
                return (float) $product->{$prop};
            }
        }

        return 0.0;
    }

    /**
     * Allocate per-line unit prices so the cart total reflects the bundle pricing rule.
     */
    protected function allocateUnitPrices(array $lines, string $pricingType, float $pricingValue): array
    {
        $baseTotals = [];
        $sumBase = 0.0;

        foreach ($lines as $line) {
            $lineBase = max(0.0, (float) $line['base_unit']) * max(1, (int) $line['qty']);
            $baseTotals[] = $lineBase;
            $sumBase += $lineBase;
        }

        // Determine target total
        $target = $sumBase;

        if ($pricingType === 'fixed_total') {
            $target = max(0.0, $pricingValue);
        } elseif ($pricingType === 'percent_off') {
            $target = max(0.0, $sumBase * (1 - max(0.0, min(100.0, $pricingValue)) / 100));
        } elseif ($pricingType === 'amount_off') {
            $target = max(0.0, $sumBase - max(0.0, $pricingValue));
        }

        // If no base price, distribute equally
        if ($sumBase <= 0.0) {
            $perLine = $target / max(1, count($lines));
            return array_map(function ($line) use ($perLine) {
                return $perLine / max(1, (int) $line['qty']);
            }, $lines);
        }

        // Distribute proportionally, adjusting last line to avoid rounding drift.
        $allocated = [];
        $allocatedSum = 0.0;

        $count = count($lines);
        foreach ($lines as $i => $line) {
            $qty = max(1, (int) $line['qty']);

            if ($i === $count - 1) {
                $lineTotal = max(0.0, $target - $allocatedSum);
            } else {
                $share = $baseTotals[$i] / $sumBase;
                $lineTotal = $target * $share;
                $allocatedSum += $lineTotal;
            }

            $allocated[] = $lineTotal / $qty;
        }

        return $allocated;
    }

    protected function renderBundleAsProduct(Bundle $bundle): string|null
    {
        if (! $bundle->ecommerce_product_id) {
            return null;
        }

        $productClass = $this->productModelClass();
        if (! class_exists($productClass)) {
            return null;
        }

        if (! class_exists('Botble\\Slug\\Models\\Slug') || ! class_exists('Botble\\Ecommerce\\Services\\HandleFrontPages')) {
            return null;
        }

        try {
            $slug = \Botble\Slug\Models\Slug::query()
                ->where('reference_type', $productClass)
                ->where('reference_id', (int) $bundle->ecommerce_product_id)
                ->first();

            if (! $slug) {
                return null;
            }

            $result = app(\Botble\Ecommerce\Services\HandleFrontPages::class)->handle($slug);

            if (! is_array($result) || empty($result['view'])) {
                return null;
            }

            if (class_exists('Botble\\Theme\\Facades\\Theme')) {
                return \Botble\Theme\Facades\Theme::scope(
                    $result['view'],
                    $result['data'] ?? [],
                    $result['default_view'] ?? null
                )->render();
            }

            $defaultView = $result['default_view'] ?? null;
            if ($defaultView && view()->exists($defaultView)) {
                return view($defaultView, $result['data'] ?? [])->render();
            }

            if (view()->exists($result['view'])) {
                return view($result['view'], $result['data'] ?? [])->render();
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
