<?php

namespace FriendsOfBotble\ProductWhatsAppOrder\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class ProductWhatsAppOrderSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_whatsapp_order_enabled' => [new OnOffRule()],
            'product_whatsapp_order_phone_number' => ['nullable', 'string', 'max:60'],
            'product_whatsapp_order_message_template' => ['nullable', 'string', 'max:2000'],
            'product_whatsapp_order_button_icon' => ['nullable', 'string', 'max:120'],
            'product_whatsapp_order_button_color' => ['nullable', 'string', 'max:50'],
            'product_whatsapp_order_button_hover_color' => ['nullable', 'string', 'max:50'],
            'product_whatsapp_order_button_radius' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'product_whatsapp_order_show_for_out_of_stock' => [new OnOffRule()],
            'product_whatsapp_order_show_always' => [new OnOffRule()],
        ];
    }
}
