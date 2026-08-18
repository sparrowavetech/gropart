<?php

namespace Datlechin\WhatsAppFloatingButton\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WhatsAppFloatingButtonSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'enabled' => [new OnOffRule()],
            'phone_number' => ['nullable', 'string', 'max:60'],
            'position' => ['nullable', 'string', Rule::in(['left', 'right'])],
            'show_popup' => [new OnOffRule()],
            'popup_title' => ['nullable', 'string', 'max:255'],
            'popup_message' => ['nullable', 'string', 'max:1000'],
            'size' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'z_index' => ['nullable', 'integer'],
            'offset_x' => ['nullable', 'integer'],
            'offset_y' => ['nullable', 'integer'],
        ];
    }
}
