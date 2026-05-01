<?php

namespace Botble\LoyaltyPoints\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class LoyaltyLevelRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:255'],
            'min_points' => ['required', 'integer', 'min:0'],
            'max_points' => ['nullable', 'integer', 'min:0', 'gt:min_points'],
            'earning_rate' => ['required', 'numeric', 'min:0'],
            'status' => Rule::in(BaseStatusEnum::values()),
            'order' => ['required', 'integer', 'min:0'],
        ];
    }
}
