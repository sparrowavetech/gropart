<?php

namespace Botble\EcommerceWholesale\Http\Requests;

use Botble\Support\Http\Requests\Request;

class RejectApplicationRequest extends Request
{
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'rejection_reason' => trans('plugins/ecommerce-wholesale::wholesale.application.rejection_reason'),
        ];
    }
}
