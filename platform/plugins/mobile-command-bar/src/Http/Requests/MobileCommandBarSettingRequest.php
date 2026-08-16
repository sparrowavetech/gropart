<?php

namespace Botble\MobileCommandBar\Http\Requests;

use Botble\MobileCommandBar\Supports\MobileCommandBarHelper;
use Illuminate\Foundation\Http\FormRequest;

class MobileCommandBarSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The settings form submits every field nested under `mcb[...]`
     * (see settings.blade.php), so every rule must be declared against
     * the dot-notation path `mcb.<field>` - not the bare field name -
     * or Laravel's validated() will not see them at all and every
     * setting will silently be treated as absent on save.
     */
    public function rules(): array
    {
        $rules = [];

        foreach (MobileCommandBarHelper::booleanKeys() as $key) {
            $rules['mcb.' . $key] = ['nullable'];
        }

        foreach (MobileCommandBarHelper::integerKeys() as $key => $bounds) {
            $rules['mcb.' . $key] = ['nullable', 'integer', 'min:' . $bounds[0], 'max:' . $bounds[1]];
        }

        foreach (MobileCommandBarHelper::urlKeys() as $key) {
            $rules['mcb.' . $key] = ['nullable', 'string', 'max:2048'];
        }

        foreach (MobileCommandBarHelper::textKeys() as $key) {
            $rules['mcb.' . $key] = ['nullable', 'string', 'max:255'];
        }

        foreach (MobileCommandBarHelper::textareaKeys() as $key) {
            $rules['mcb.' . $key] = ['nullable', 'string', 'max:20000'];
        }

        $rules['mcb.custom_css'] = ['nullable', 'string', 'max:50000'];

        return $rules;
    }
}
