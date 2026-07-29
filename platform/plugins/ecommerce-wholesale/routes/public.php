<?php

use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Services\PricingRuleService;
use Botble\EcommerceWholesale\Services\ProductWholesaleBoxRenderer;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Theme::registerRoutes(function (): void {
    Route::group([
        'prefix' => 'wholesale',
        'as' => 'public.wholesale.',
    ], function (): void {
        Route::get('tiers/{productId}', function (int|string $productId) {
            if (! WholesaleHelper::isEnabled()) {
                return response()->json(['tiers' => []]);
            }

            $customer = auth('customer')->user();
            $isGuestEnabled = WholesaleHelper::isEnabledForGuests();

            if (! $customer && ! $isGuestEnabled) {
                return response()->json(['tiers' => []]);
            }

            if ($customer && ! WholesaleHelper::isWholesaleCustomer($customer) && ! $isGuestEnabled) {
                return response()->json(['tiers' => []]);
            }

            $product = Product::query()->find($productId);

            if (! $product) {
                return response()->json(['tiers' => []]);
            }

            $originalProduct = $product->is_variation ? $product->original_product : $product;

            $groupIds = [];

            if ($customer && WholesaleHelper::isWholesaleCustomer($customer)) {
                $groupIds = $customer->wholesaleGroups()
                    ->where('status', CustomerGroupStatusEnum::PUBLISHED)
                    ->pluck('ws_customer_groups.id')
                    ->all();
            } elseif ($isGuestEnabled) {
                $defaultGroupId = WholesaleHelper::getDefaultGroupId();

                if ($defaultGroupId) {
                    $groupIds = [$defaultGroupId];
                } else {
                    $firstGroup = CustomerGroup::query()
                        ->where('status', CustomerGroupStatusEnum::PUBLISHED)
                        ->orderBy('priority')
                        ->first();

                    $groupIds = $firstGroup ? [$firstGroup->id] : [];
                }
            }

            if (empty($groupIds) && ! $isGuestEnabled) {
                return response()->json(['tiers' => []]);
            }

            $service = app(PricingRuleService::class);
            $basePrice = $product->isOnSale() ? $product->front_sale_price : $product->price;
            $pricingTiers = $service->getTieredPricesForCustomer($originalProduct, $groupIds, $basePrice);

            $tiers = $pricingTiers->map(fn ($tier) => [
                'min' => $tier['min_qty'],
                'max' => $tier['max_qty'] ?? 999999,
                'price' => $tier['price'],
                'formattedPrice' => format_price($tier['price']),
                'savings' => $basePrice - $tier['price'],
                'formattedSavings' => format_price($basePrice - $tier['price']),
                'formattedBasePrice' => format_price($basePrice),
            ])->values();

            return response()->json(['tiers' => $tiers]);
        })->name('tiers')->wherePrimaryKey('productId');

        // Returns the fully rendered wholesale box HTML for a product. Used by the
        // theme-independent fallback loader when the product page template does not
        // call the `ecommerce_after_product_description` hook. Output is per-viewer
        // (guest/customer/group), so it must never be cached.
        Route::get('box/{productId}', function (int|string $productId) {
            $product = Product::query()->find($productId);

            $html = $product
                ? app(ProductWholesaleBoxRenderer::class)->render($product)
                : '';

            return response()
                ->json(['html' => $html])
                ->header('Cache-Control', 'no-store, private');
        })->name('box')->wherePrimaryKey('productId');
    });
});
