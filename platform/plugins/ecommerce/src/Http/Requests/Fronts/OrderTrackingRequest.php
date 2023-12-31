<?php

namespace Botble\Ecommerce\Http\Requests\Fronts;

use Botble\Base\Rules\EmailRule;
use Botble\Support\Http\Requests\Request;

class OrderTrackingRequest extends Request
{
    public function rules(): array
    {
        return [
            'order_id' => ['nullable', 'string', 'min:1'],
            'email' => ['nullable', new EmailRule()],
        ];
    }
}
