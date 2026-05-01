<?php

namespace Botble\EcommerceWholesale\Http\Requests;

use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CustomerGroupRequest extends Request
{
    public function rules(): array
    {
        $discountValueRules = ['required', 'numeric', 'min:0'];

        if ($this->input('discount_type') === DiscountTypeEnum::PERCENTAGE) {
            $discountValueRules[] = 'max:100';
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_type' => ['required', Rule::in(DiscountTypeEnum::values())],
            'discount_value' => $discountValueRules,
            'priority' => ['nullable', 'integer', 'min:0'],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(CustomerGroupStatusEnum::values())],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('core/base::forms.name'),
            'description' => trans('core/base::forms.description'),
            'discount_type' => trans('plugins/ecommerce-wholesale::wholesale.customer_group.discount_type'),
            'discount_value' => trans('plugins/ecommerce-wholesale::wholesale.customer_group.discount_value'),
            'priority' => trans('plugins/ecommerce-wholesale::wholesale.customer_group.priority'),
            'min_order_quantity' => trans('plugins/ecommerce-wholesale::wholesale.moq.min_quantity'),
            'min_order_value' => trans('plugins/ecommerce-wholesale::wholesale.moq.min_order_value'),
            'status' => trans('core/base::tables.status'),
        ];
    }
}
