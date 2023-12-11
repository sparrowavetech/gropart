<?php

namespace Botble\Ecommerce\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class ProductSearchSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'search_for_an_exact_phrase' => new OnOffRule(),
            'search_products_by' => ['required', 'array'],
            'search_products_by.*' => ['required', 'in:name,sku,variation_sku,description,brand,tag'],
        ];
    }
}
