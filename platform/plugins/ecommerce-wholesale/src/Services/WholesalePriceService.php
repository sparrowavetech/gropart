<?php

namespace Botble\EcommerceWholesale\Services;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Closure;

class WholesalePriceService
{
    public function __construct(protected PricingRuleService $pricingRuleService)
    {
    }

    public function handle(Product $product, Closure $next): Product
    {
        $customer = auth('customer')->user();

        if (! $customer || ! WholesaleHelper::isWholesaleCustomer($customer)) {
            return $next($product);
        }

        $groupIds = $customer->wholesaleGroups()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->pluck('ws_customer_groups.id')
            ->all();

        if (empty($groupIds)) {
            return $next($product);
        }

        $storeId = $product->store_id ?? null;

        $bestPrice = $this->pricingRuleService->getBestPriceForQuantity(
            $product,
            1,
            $groupIds,
            $storeId
        );

        if ($bestPrice) {
            $product->front_sale_price = $bestPrice['final_price'];

            return $next($product);
        }

        $this->applyGroupDiscount($product, $groupIds);

        return $next($product);
    }

    public function calculatePriceWithQuantity(float $basePrice, Product $product, int $quantity = 1): float
    {
        if (! WholesaleHelper::isEnabled()) {
            return $basePrice;
        }

        $groupIds = $this->getApplicableGroupIds();

        if (empty($groupIds) && ! WholesaleHelper::isEnabledForGuests()) {
            return $basePrice;
        }

        $originalProduct = $product->is_variation ? $product->original_product : $product;
        $storeId = $product->store_id ?? null;

        $bestPrice = $this->pricingRuleService->getBestPriceForQuantity(
            $originalProduct,
            $quantity,
            $groupIds,
            $storeId,
            $basePrice
        );

        if ($bestPrice) {
            return $bestPrice['final_price'];
        }

        return $this->applyGroupDiscountToPrice($basePrice, $groupIds);
    }

    public function getWholesalePrice(Product $product, int $quantity = 1, ?Customer $customer = null): ?float
    {
        if (! WholesaleHelper::isEnabled()) {
            return null;
        }

        $groupIds = $this->getApplicableGroupIds($customer);

        if (empty($groupIds) && ! WholesaleHelper::isEnabledForGuests()) {
            return null;
        }

        $originalProduct = $product->is_variation ? $product->original_product : $product;
        $storeId = $product->store_id ?? null;
        $basePrice = $product->isOnSale() ? $product->front_sale_price : $product->price;

        $bestPrice = $this->pricingRuleService->getBestPriceForQuantity(
            $originalProduct,
            $quantity,
            $groupIds,
            $storeId,
            $basePrice
        );

        if ($bestPrice) {
            return $bestPrice['final_price'];
        }

        if ($this->productHasPricingRules($originalProduct, $groupIds, $storeId)) {
            return null;
        }

        $discountedPrice = $this->applyGroupDiscountToPrice($basePrice, $groupIds);

        return $discountedPrice < $basePrice ? $discountedPrice : null;
    }

    protected function productHasPricingRules(Product $product, array $groupIds, ?int $storeId = null): bool
    {
        return $this->pricingRuleService->hasRulesForProduct($product, $groupIds, $storeId);
    }

    protected function applyGroupDiscount(Product $product, array $groupIds): void
    {
        $groups = CustomerGroup::query()
            ->whereIn('id', $groupIds)
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->orderBy('priority')
            ->get();

        if ($groups->isEmpty()) {
            return;
        }

        $basePrice = $product->front_sale_price ?: $product->price;
        $bestDiscount = 0;

        foreach ($groups as $group) {
            $discount = $group->calculateDiscount($basePrice);

            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
            }
        }

        if ($bestDiscount > 0) {
            $product->front_sale_price = max(0, $basePrice - $bestDiscount);
        }
    }

    protected function applyGroupDiscountToPrice(float $basePrice, array $groupIds): float
    {
        $groups = CustomerGroup::query()
            ->whereIn('id', $groupIds)
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->orderBy('priority')
            ->get();

        if ($groups->isEmpty()) {
            return $basePrice;
        }

        $bestDiscount = 0;

        foreach ($groups as $group) {
            $discount = $group->calculateDiscount($basePrice);

            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
            }
        }

        return max(0, $basePrice - $bestDiscount);
    }

    public function getApplicableGroupIds(?Customer $customer = null): array
    {
        $customer = $customer ?? auth('customer')->user();

        if ($customer && WholesaleHelper::isWholesaleCustomer($customer)) {
            return $customer->wholesaleGroups()
                ->where('status', CustomerGroupStatusEnum::PUBLISHED)
                ->pluck('ws_customer_groups.id')
                ->all();
        }

        if (WholesaleHelper::isEnabledForGuests()) {
            $defaultGroupId = WholesaleHelper::getDefaultGroupId();

            if ($defaultGroupId) {
                return [$defaultGroupId];
            }

            $firstGroup = CustomerGroup::query()
                ->where('status', CustomerGroupStatusEnum::PUBLISHED)
                ->orderBy('priority')
                ->first();

            return $firstGroup ? [$firstGroup->id] : [];
        }

        return [];
    }
}
