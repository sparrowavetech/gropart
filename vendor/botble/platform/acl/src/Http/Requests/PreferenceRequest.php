<?php

namespace Botble\ACL\Http\Requests;

use Botble\Base\Supports\Language;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PreferenceRequest extends Request
{
    public function rules(): array
    {
        return [
            'locale' => ['sometimes', Rule::in(array_keys(Language::getAvailableLocales()))],
            'theme_mode' => ['required', 'string', 'in:light,dark'],
        ];
    }
}
