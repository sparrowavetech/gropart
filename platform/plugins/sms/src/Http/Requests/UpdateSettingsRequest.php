<?php

namespace Botble\Sms\Http\Requests;

use BaseHelper;
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
