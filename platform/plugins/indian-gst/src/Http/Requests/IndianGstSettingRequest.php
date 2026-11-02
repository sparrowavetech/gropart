<?php

namespace SparroWave\IndianGst\Http\Requests;

use Botble\Support\Http\Requests\Request;

class IndianGstSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'indian_gst_enabled' => 'nullable|in:0,1',
            'indian_gst_default_company_state' => ['nullable', 'string', \Illuminate\Validation\Rule::in(array_keys(\SparroWave\IndianGst\Supports\IndianGstHelper::getIndianStates()))],
            'indian_gst_default_company_gstin' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]$/i'],
        ];
    }
}
