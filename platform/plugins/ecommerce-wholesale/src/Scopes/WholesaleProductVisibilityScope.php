<?php

namespace Botble\EcommerceWholesale\Scopes;

use Botble\EcommerceWholesale\Enums\ProductVisibilityEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WholesaleProductVisibilityScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (is_in_admin() || app()->runningInConsole()) {
            return;
        }

        $customer = auth('customer')->user();
        $isWholesale = $customer && WholesaleHelper::isWholesaleCustomer($customer);
        $groupIds = $isWholesale
            ? $customer->wholesaleGroups()->pluck('ws_customer_groups.id')->all()
            : [];

        $builder->where(function (Builder $query) use ($isWholesale, $groupIds): void {
            $query->whereNotExists(function ($subQuery): void {
                $subQuery->selectRaw('1')
                    ->from('ws_product_visibility')
                    ->whereColumn('ws_product_visibility.product_id', 'ec_products.id')
                    ->where('visibility_type', '!=', ProductVisibilityEnum::PRODUCT_PUBLIC);
            });

            if ($isWholesale) {
                $query->orWhereExists(function ($subQuery): void {
                    $subQuery->selectRaw('1')
                        ->from('ws_product_visibility')
                        ->whereColumn('ws_product_visibility.product_id', 'ec_products.id')
                        ->where('visibility_type', ProductVisibilityEnum::WHOLESALE_ONLY);
                });
            }

            if (! empty($groupIds)) {
                $query->orWhereExists(function ($subQuery) use ($groupIds): void {
                    $subQuery->selectRaw('1')
                        ->from('ws_product_visibility')
                        ->join(
                            'ws_product_group_access',
                            'ws_product_visibility.product_id',
                            '=',
                            'ws_product_group_access.product_id'
                        )
                        ->whereColumn('ws_product_visibility.product_id', 'ec_products.id')
                        ->where('visibility_type', ProductVisibilityEnum::SPECIFIC_GROUPS)
                        ->whereIn('ws_product_group_access.customer_group_id', $groupIds);
                });
            }
        });
    }
}
