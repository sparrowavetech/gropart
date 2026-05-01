<?php

namespace Botble\EcommerceWholesale\Http\Requests;

use Botble\Support\Http\Requests\Request;

class UpdateCompanyProfileRequest extends Request
{
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:50'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'expected_volume' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => trans('plugins/ecommerce-wholesale::wholesale.application.phone'),
            'business_type' => trans('plugins/ecommerce-wholesale::wholesale.application.business_type'),
            'expected_volume' => trans('plugins/ecommerce-wholesale::wholesale.application.expected_volume'),
            'notes' => trans('plugins/ecommerce-wholesale::wholesale.application.notes'),
        ];
    }
}
