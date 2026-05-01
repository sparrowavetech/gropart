<?php

namespace Botble\EcommerceWholesale\Http\Requests;

use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PricingRuleRequest extends Request
{
    public function rules(): array
    {
        $discountValueRules = ['required', 'numeric', 'min:0'];

        if ($this->input('discount_type') === PricingDiscountTypeEnum::PERCENTAGE) {
            $discountValueRules[] = 'max:100';
        }

        $scope = $this->input('scope', PricingRuleScopeEnum::PRODUCT);

        return [
            'scope' => ['required', Rule::in(PricingRuleScopeEnum::values())],
            'product_id' => [
                $scope === PricingRuleScopeEnum::PRODUCT ? 'required' : 'nullable',
                'exists:ec_products,id',
            ],
            'category_id' => [
                $scope === PricingRuleScopeEnum::CATEGORY ? 'required' : 'nullable',
                'exists:ec_product_categories,id',
            ],
            'customer_group_id' => ['nullable', 'exists:ws_customer_groups,id'],
            'store_id' => ['nullable', 'integer'],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'max_quantity' => ['nullable', 'integer', 'min:1', 'gte:min_quantity'],
            'discount_type' => ['required', Rule::in(PricingDiscountTypeEnum::values())],
            'discount_value' => $discountValueRules,
            'status' => ['required', Rule::in(CustomerGroupStatusEnum::values())],
        ];
    }

    public function attributes(): array
    {
        return [
            'scope' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.scope'),
            'product_id' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.product'),
            'category_id' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.category'),
            'customer_group_id' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.customer_group'),
            'min_quantity' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.min_quantity'),
            'max_quantity' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.max_quantity'),
            'discount_type' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_type'),
            'discount_value' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_value'),
            'status' => trans('core/base::tables.status'),
        ];
    }
}
