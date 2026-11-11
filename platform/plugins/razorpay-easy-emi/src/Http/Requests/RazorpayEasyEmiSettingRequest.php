<?php

namespace SparroWave\RazorpayEasyEmi\Http\Requests;

use Botble\Support\Http\Requests\Request;

class RazorpayEasyEmiSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'razorpay_easy_emi_enabled' => ['nullable', 'in:0,1'],
            'razorpay_easy_emi_min_amount' => ['nullable', 'numeric', 'min:0'],
            'razorpay_easy_emi_default_scheme' => ['nullable', 'string', 'in:standard,no_cost,low_cost,disabled'],
            'razorpay_easy_emi_auto_detect_key' => ['nullable', 'in:0,1'],
            'razorpay_easy_emi_custom_key' => ['nullable', 'string', 'max:255'],
            'razorpay_easy_emi_custom_secret' => ['nullable', 'string', 'max:255'],
            'razorpay_easy_emi_enable_catalog_badge' => ['nullable', 'in:0,1'],
            'razorpay_easy_emi_enable_cart_banner' => ['nullable', 'in:0,1'],
            'razorpay_easy_emi_auto_select_checkout' => ['nullable', 'in:0,1'],
        ];
    }
}

