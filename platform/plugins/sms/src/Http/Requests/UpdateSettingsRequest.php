<?php

namespace Botble\Sms\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class UpdateSettingsRequest extends Request
{
    public function rules(): array
    {
        return [
            'sms_url' => 'required',
        ];
    }
}
