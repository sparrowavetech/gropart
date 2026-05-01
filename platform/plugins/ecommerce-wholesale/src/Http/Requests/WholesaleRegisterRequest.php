<?php

namespace Botble\EcommerceWholesale\Http\Requests;

use Botble\Support\Http\Requests\Request;

class WholesaleRegisterRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'company_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'expected_volume' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if (! auth('customer')->check()) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255', 'unique:ec_customers,email'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => trans('plugins/ecommerce-wholesale::wholesale.application.name'),
            'email' => trans('plugins/ecommerce-wholesale::wholesale.application.email'),
            'company_name' => trans('plugins/ecommerce-wholesale::wholesale.application.company_name'),
            'tax_id' => trans('plugins/ecommerce-wholesale::wholesale.application.tax_id'),
            'phone' => trans('plugins/ecommerce-wholesale::wholesale.application.phone'),
            'business_type' => trans('plugins/ecommerce-wholesale::wholesale.application.business_type'),
            'expected_volume' => trans('plugins/ecommerce-wholesale::wholesale.application.expected_volume'),
            'notes' => trans('plugins/ecommerce-wholesale::wholesale.application.notes'),
        ];
    }
}
