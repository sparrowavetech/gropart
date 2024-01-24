<?php

namespace Skillcraft\ContactManager\Http\Requests\Settings;

use Botble\Support\Http\Requests\Request;

class ContactManagerSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'sc-cm-contact-form' => ['boolean'],
        ];
    }
}
