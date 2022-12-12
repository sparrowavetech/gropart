<?php

namespace Botble\Sms\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Sms\Enums\SmsEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class SmsRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::in(SmsEnum::values()),
                'unique:sms'
            ],
            'status' => Rule::in(BaseStatusEnum::values()),
            'template_id' => 'required|integer',
        ];
    }
}
