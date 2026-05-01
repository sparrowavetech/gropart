<?php

namespace Botble\EcommerceWholesale\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ApproveApplicationRequest extends Request
{
    public function rules(): array
    {
        return [
            'customer_group_id' => ['required', 'exists:ws_customer_groups,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_group_id' => trans('plugins/ecommerce-wholesale::wholesale.customer_group.name'),
        ];
    }
}
