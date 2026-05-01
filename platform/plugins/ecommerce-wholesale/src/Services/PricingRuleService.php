<?php

namespace Botble\EcommerceWholesale\Services;

use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PricingRuleService
{
    public function resolveRules(Product $product, array $groupIds = [], ?int $storeId = null): Collection
    {
        $productRules = $this->queryRulesByScope(PricingRuleScopeEnum::PRODUCT, $groupIds, $storeId)
            ->where('product_id', $product->id)
            ->get();

        if ($productRules->isNotEmpty()) {
            return $productRules;
        }

        $categoryIds = $product->categories()->pluck('ec_product_categories.id')->all();

        if (! empty($categoryIds)) {
            $allCategoryIds = $this->expandCategoryIds($categoryIds);

            $categoryRules = $this->queryRulesByScope(PricingRuleScopeEnum::CATEGORY, $groupIds, $storeId)
                ->whereIn('category_id', $allCategoryIds)
                ->get();

            if ($categoryRules->isNotEmpty()) {
                return $categoryRules;
            }
        }

        return $this->queryRulesByScope(PricingRuleScopeEnum::GLOBAL, $groupIds, $storeId)->get();
    }

    public function hasRulesForProduct(Product $product, array $groupIds = [], ?int $storeId = null): bool
    {
        return $this->resolveRules($product, $groupIds, $storeId)->isNotEmpty();
    }

    public function getTieredPricesForProduct(
        Product $product,
        ?CustomerGroup $group = null,
        ?int $storeId = null
    ): Collection {
        $groupIds = $group ? [$group->id] : [];

        $rules = $this->resolveRules($product, $groupIds, $storeId)
            ->sortBy('min_quantity');

        if ($groupIds) {
            $rules = $rules->filter(function ($rule) use ($groupIds) {
                return $rule->customer_group_id === null || in_array($rule->customer_group_id, $groupIds);
            });
        }

        $basePrice = $product->price;

        return $rules->values()->map(function ($rule) use ($basePrice) {
            return [
                'min_qty' => $rule->min_quantity,
                'max_qty' => $rule->max_quantity,
                'quantity_range' => $rule->quantity_range,
                'price' => $rule->calculateFinalPrice($basePrice),
                'discount' => $rule->discount_value,
                'type' => $rule->discount_type->getValue(),
                'savings' => $rule->calculateDiscount($basePrice),
            ];
        });
    }

    public function getRulesForProduct(int|string $productId): Collection
    {
        return GroupPricingRule::query()
            ->where('product_id', $productId)
            ->where('scope', PricingRuleScopeEnum::PRODUCT)
            ->with('customerGroup')
            ->orderBy('min_quantity')
            ->get();
    }

    public function getTieredPricesForCustomer(Product $product, array $groupIds = [], ?float $basePrice = null): Collection
    {
        $rules = $this->resolveRules($product, $groupIds)
            ->sortBy('min_quantity');

        $basePrice = $basePrice ?? ($product->isOnSale() ? $product->front_sale_price : $product->price);

        return $rules->values()->map(function ($rule) use ($basePrice) {
            return [
                'min_qty' => $rule->min_quantity,
                'max_qty' => $rule->max_quantity,
                'quantity_range' => $rule->quantity_range,
                'price' => $rule->calculateFinalPrice($basePrice),
                'discount' => $rule->discount_value,
                'type' => $rule->discount_type->getValue(),
                'savings' => $basePrice - $rule->calculateFinalPrice($basePrice),
            ];
        });
    }

    public function getBestPriceForQuantity(
        Product $product,
        int $quantity,
        array $groupIds = [],
        ?int $storeId = null,
        ?float $basePrice = null
    ): ?array {
        $rules = $this->resolveRules($product, $groupIds, $storeId);

        $applicableRules = $rules->filter(function ($rule) use ($quantity) {
            return $rule->appliesToQuantity($quantity);
        });

        $basePrice = $basePrice ?? $product->price;
        $bestRule = null;
        $bestPrice = $basePrice;

        foreach ($applicableRules as $rule) {
            $price = $rule->calculateFinalPrice($basePrice);
            if ($price < $bestPrice) {
                $bestPrice = $price;
                $bestRule = $rule;
            }
        }

        if (! $bestRule) {
            return null;
        }

        return [
            'rule' => $bestRule,
            'original_price' => $basePrice,
            'final_price' => $bestPrice,
            'discount' => $basePrice - $bestPrice,
        ];
    }

    protected function queryRulesByScope(string $scope, array $groupIds = [], ?int $storeId = null): Builder
    {
        $query = GroupPricingRule::query()
            ->where('scope', $scope)
            ->where('status', CustomerGroupStatusEnum::PUBLISHED);

        if (! empty($groupIds)) {
            $query->where(function ($q) use ($groupIds): void {
                $q->whereIn('customer_group_id', $groupIds)
                    ->orWhereNull('customer_group_id');
            });
        }

        if ($storeId) {
            $query->where(function ($q) use ($storeId): void {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $query;
    }

    protected function expandCategoryIds(array $categoryIds): array
    {
        $allIds = $categoryIds;

        $categories = ProductCategory::query()
            ->whereIn('id', $categoryIds)
            ->get();

        foreach ($categories as $category) {
            $parentIds = $category->parents->pluck('id')->all();

            foreach ($parentIds as $parentId) {
                if (! in_array($parentId, $allIds)) {
                    $allIds[] = $parentId;
                }
            }
        }

        return $allIds;
    }
}
