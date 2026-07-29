<?php

namespace Botble\EcommerceWholesale\Http\Requests;

use Botble\EcommerceWholesale\Enums\WholesaleDisplayModeEnum;
use Botble\EcommerceWholesale\Enums\WholesaleStyleEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WholesaleSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'wholesale_enabled' => ['nullable', 'in:0,1'],
            'wholesale_require_approval' => ['nullable', 'in:0,1'],
            'wholesale_show_prices_to_guests' => ['nullable', 'in:0,1'],
            'wholesale_allow_multiple_groups' => ['nullable', 'in:0,1'],
            'wholesale_enable_for_guests' => ['nullable', 'in:0,1'],
            'wholesale_enable_registration' => ['nullable', 'in:0,1'],
            'wholesale_enable_vendor_dashboard' => ['nullable', 'in:0,1'],
            'wholesale_default_group' => ['nullable', 'integer', 'exists:ws_customer_groups,id'],
            'wholesale_style' => ['nullable', Rule::in(WholesaleStyleEnum::values())],
            'wholesale_display_mode' => ['nullable', Rule::in(WholesaleDisplayModeEnum::values())],
            'wholesale_primary_color' => ['nullable', 'string', 'max:20'],
            'wholesale_header_color' => ['nullable', 'string', 'max:20'],
            'wholesale_price_color' => ['nullable', 'string', 'max:20'],
            'wholesale_badge_color' => ['nullable', 'string', 'max:20'],
            'wholesale_savings_color' => ['nullable', 'string', 'max:20'],
            'wholesale_border_color' => ['nullable', 'string', 'max:20'],
            'wholesale_show_pricing_table' => ['nullable', 'in:0,1'],
            'wholesale_auto_display' => ['nullable', 'in:0,1'],
            'wholesale_show_icon' => ['nullable', 'in:0,1'],
            'wholesale_icon' => ['nullable', 'string', 'max:50'],
            'wholesale_show_original_price' => ['nullable', 'in:0,1'],
            'wholesale_show_savings' => ['nullable', 'in:0,1'],
        ];
    }
}
