<?php

namespace SparroWave\ProductFreeShipping\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class ProductFreeShippingSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_free_shipping_enabled' => [new OnOffRule()],
            'product_free_shipping_badge_text' => ['nullable', 'string', 'max:120'],
            'product_free_shipping_method_title' => ['nullable', 'string', 'max:120'],
            'product_free_shipping_hide_other_methods' => [new OnOffRule()],
        ];
    }
}
