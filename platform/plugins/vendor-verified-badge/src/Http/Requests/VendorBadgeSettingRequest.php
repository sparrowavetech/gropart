<?php

namespace SparroWave\VendorVerifiedBadge\Http\Requests;

use Botble\Support\Http\Requests\Request;

class VendorBadgeSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'vendor_verified_badge_icon' => ['nullable', 'string', 'max:255'],
            'vendor_verified_application_url' => ['nullable', 'url', 'max:255'],
            'vendor_verified_menu_gating_enabled' => ['nullable', 'in:0,1'],
            'vendor_verified_completion_threshold' => ['nullable', 'integer', 'min:10', 'max:100'],
            'vendor_verified_tooltip_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
