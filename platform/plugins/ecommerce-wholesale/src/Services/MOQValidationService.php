<?php

namespace Botble\EcommerceWholesale\Services;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Models\ProductMOQ;
use Illuminate\Support\Collection;

class MOQValidationService
{
    public function getProductMOQ(Product $product, ?Customer $customer = null): array
    {
        $moq = [
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'source' => 'default',
        ];

        if (! $customer || ! WholesaleHelper::isWholesaleCustomer($customer)) {
            return $moq;
        }

        $groupIds = $customer->wholesaleGroups()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->pluck('ws_customer_groups.id')
            ->all();

        if (empty($groupIds)) {
            return $moq;
        }

        $productMoq = ProductMOQ::query()
            ->where('product_id', $product->id)
            ->where(function ($query) use ($groupIds): void {
                $query->whereIn('customer_group_id', $groupIds)
                    ->orWhereNull('customer_group_id');
            })
            ->orderByRaw('customer_group_id IS NULL')
            ->first();

        if ($productMoq) {
            return [
                'min_quantity' => $productMoq->min_quantity,
                'quantity_increment' => $productMoq->quantity_increment,
                'source' => 'product',
            ];
        }

        $group = $customer->wholesaleGroups()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->whereNotNull('min_order_quantity')
            ->orderBy('priority')
            ->orderBy('id')
            ->first();

        if ($group && $group->min_order_quantity) {
            $moq['min_quantity'] = $group->min_order_quantity;
            $moq['source'] = 'group';
        }

        return $moq;
    }

    public function validateQuantity(Product $product, int $quantity, ?Customer $customer = null): array
    {
        $moq = $this->getProductMOQ($product, $customer);

        $errors = [];

        if ($quantity < $moq['min_quantity']) {
            $errors[] = trans('plugins/ecommerce-wholesale::wholesale.moq.error_min', [
                'product' => $product->name,
                'quantity' => $moq['min_quantity'],
            ]);
        }

        if ($moq['quantity_increment'] > 1) {
            $remainder = ($quantity - $moq['min_quantity']) % $moq['quantity_increment'];
            if ($remainder !== 0 && $quantity >= $moq['min_quantity']) {
                $errors[] = trans('plugins/ecommerce-wholesale::wholesale.moq.error_increment', [
                    'product' => $product->name,
                    'increment' => $moq['quantity_increment'],
                ]);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'moq' => $moq,
        ];
    }

    public function validateCart(Collection $cartItems, ?Customer $customer = null): array
    {
        $errors = [];
        $totalValue = 0;

        foreach ($cartItems as $item) {
            $product = $item->model ?? Product::query()->find($item->id);

            if (! $product) {
                $errors[] = trans('plugins/ecommerce-wholesale::wholesale.moq.error_product_not_found');

                continue;
            }

            $validation = $this->validateQuantity($product, $item->qty, $customer);

            if (! $validation['valid']) {
                $errors = array_merge($errors, $validation['errors']);
            }

            $totalValue += ($item->price ?? $product->price) * $item->qty;
        }

        if ($customer && WholesaleHelper::isWholesaleCustomer($customer)) {
            $group = $customer->wholesaleGroups()
                ->where('status', CustomerGroupStatusEnum::PUBLISHED)
                ->whereNotNull('min_order_value')
                ->orderBy('priority')
                ->orderBy('id')
                ->first();

            if ($group && $group->min_order_value > $totalValue) {
                $errors[] = trans('plugins/ecommerce-wholesale::wholesale.moq.error_min_value', [
                    'min' => format_price($group->min_order_value),
                    'current' => format_price($totalValue),
                ]);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function getMinOrderValue(?Customer $customer = null): float
    {
        if (! $customer || ! WholesaleHelper::isWholesaleCustomer($customer)) {
            return 0;
        }

        $group = $customer->wholesaleGroups()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->whereNotNull('min_order_value')
            ->orderBy('priority')
            ->orderBy('id')
            ->first();

        return $group?->min_order_value ?? 0;
    }
}
