<?php

namespace Botble\Ecommerce\Http\Requests;

class ProductImportRequest extends ProductRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['barcode'] = [
            'required',
            'string',
            'max:50',
        ];

        return $rules;
    }
}
