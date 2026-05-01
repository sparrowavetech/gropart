<?php

namespace Botble\EcommerceWholesale\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ReapplyWholesaleRequest extends Request
{
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'expected_volume' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name' => trans('plugins/ecommerce-wholesale::wholesale.application.company_name'),
            'tax_id' => trans('plugins/ecommerce-wholesale::wholesale.application.tax_id'),
            'phone' => trans('plugins/ecommerce-wholesale::wholesale.application.phone'),
            'business_type' => trans('plugins/ecommerce-wholesale::wholesale.application.business_type'),
            'expected_volume' => trans('plugins/ecommerce-wholesale::wholesale.application.expected_volume'),
            'notes' => trans('plugins/ecommerce-wholesale::wholesale.application.notes'),
        ];
    }
}
