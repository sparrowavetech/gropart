<?php

namespace Botble\ContentInjector\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ContentInjectorRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:220'],
            'value' => ['required'],
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
