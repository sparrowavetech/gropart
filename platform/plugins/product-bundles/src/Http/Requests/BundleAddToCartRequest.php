<?php

namespace Botble\ProductBundles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BundleAddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bundle_id' => ['required', 'integer', 'min:1'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:999'],
            // selections[groupId] = [productId,...]
            'selections' => ['nullable', 'array'],
        ];
    }
}
