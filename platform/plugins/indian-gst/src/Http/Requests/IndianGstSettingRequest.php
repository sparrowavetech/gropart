<?php

namespace SparroWave\IndianGst\Http\Requests;

use Botble\Support\Http\Requests\Request;

class IndianGstSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'indian_gst_enabled' => 'nullable|in:0,1',
            'indian_gst_display_inclusive_price' => 'nullable|in:0,1',
            'indian_gst_default_company_state' => 'nullable|string|max:120',
            'indian_gst_default_company_gstin' => 'nullable|string|max:30',
        ];
    }
}
