<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ReviewRequest extends Request
{
    public function rules(): array
    {
        return [
            'created_at' => ['required', 'date'],
            'product_id' => ['required', 'exists:ec_products,id'],
            'customer_id' => ['required', 'exists:ec_customers,id'],
            'star' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:5000'],
        ];
    }
}
