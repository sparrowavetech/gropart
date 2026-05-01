<?php

namespace Botble\EcommerceWholesale\Services;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\ProductVisibilityEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\ProductGroupAccess;
use Botble\EcommerceWholesale\Models\ProductVisibility;
use Illuminate\Support\Facades\DB;

class ProductVisibilityService
{
    public function setVisibility(
        Product $product,
        ProductVisibilityEnum $type,
        array $groupIds = []
    ): ProductVisibility {
        return DB::transaction(function () use ($product, $type, $groupIds) {
            $visibility = ProductVisibility::query()->updateOrCreate(
                ['product_id' => $product->id],
                ['visibility_type' => $type]
            );

            ProductGroupAccess::query()->where('product_id', $product->id)->delete();

            if ($type->getValue() === ProductVisibilityEnum::SPECIFIC_GROUPS && ! empty($groupIds)) {
                $validGroupIds = CustomerGroup::query()
                    ->whereIn('id', $groupIds)
                    ->pluck('id')
                    ->all();

                foreach ($validGroupIds as $groupId) {
                    ProductGroupAccess::query()->create([
                        'product_id' => $product->id,
                        'customer_group_id' => $groupId,
                    ]);
                }
            }

            return $visibility;
        });
    }

    public function getVisibility(Product $product): ?ProductVisibility
    {
        return ProductVisibility::query()
            ->where('product_id', $product->id)
            ->with('groupAccess')
            ->first();
    }

    public function removeVisibility(Product $product): void
    {
        ProductGroupAccess::query()->where('product_id', $product->id)->delete();
        ProductVisibility::query()->where('product_id', $product->id)->delete();
    }

    public function canCustomerViewProduct(Product $product, ?Customer $customer = null): bool
    {
        $visibility = $this->getVisibility($product);

        if (! $visibility || $visibility->isPublic()) {
            return true;
        }

        if (! $customer) {
            return false;
        }

        if (! WholesaleHelper::isWholesaleCustomer($customer)) {
            return false;
        }

        if ($visibility->isWholesaleOnly()) {
            return true;
        }

        $customerGroupIds = $customer->wholesaleGroups()->pluck('ws_customer_groups.id')->all();
        $productGroupIds = $visibility->groupAccess->pluck('customer_group_id')->all();

        return ! empty(array_intersect($customerGroupIds, $productGroupIds));
    }
}
