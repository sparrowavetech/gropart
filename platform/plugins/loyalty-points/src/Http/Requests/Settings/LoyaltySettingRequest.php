<?php

namespace Botble\LoyaltyPoints\Http\Requests\Settings;

use Botble\Language\Facades\Language;
use Botble\LoyaltyPoints\Enums\ProductInfoBoxStyleEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class LoyaltySettingRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'loyalty_points_enable_loyalty_program' => ['nullable', 'boolean'],
            'loyalty_points_points_exchange_rate' => ['required', 'numeric', 'min:1'],
            'loyalty_points_points_earning_rate' => ['required', 'numeric', 'min:0'],
            'loyalty_points_points_earning_currency' => ['required', 'numeric', 'min:1'],
            'loyalty_points_eligible_order_statuses' => ['nullable', 'array'],
            'loyalty_points_eligible_order_statuses.*' => ['string'],
            'loyalty_points_points_for_registration' => ['nullable', 'integer', 'min:0'],
            'loyalty_points_points_for_review' => ['nullable', 'integer', 'min:0'],
            'loyalty_points_points_for_photo_review' => ['nullable', 'integer', 'min:0'],
            'loyalty_points_points_for_referral' => ['nullable', 'integer', 'min:0'],
            'loyalty_points_points_for_birthday' => ['nullable', 'integer', 'min:0'],
            'loyalty_points_points_redemption_rate' => ['required', 'numeric', 'min:1'],
            'loyalty_points_points_redemption_currency' => ['required', 'numeric', 'min:1'],
            'loyalty_points_min_redeemable_points' => ['nullable', 'integer', 'min:0'],
            'loyalty_points_max_redeemable_points' => ['nullable', 'integer', 'min:0'],
            'loyalty_points_max_redemption_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'loyalty_points_points_expiry_months' => ['nullable', 'integer', 'min:0'],
            'loyalty_points_enable_loyalty_card' => ['nullable', 'boolean'],
            'loyalty_points_enable_guest_checkout_member_id' => ['nullable', 'boolean'],
            'loyalty_points_enable_product_info' => ['nullable', 'boolean'],
            'loyalty_points_product_info_box_style' => ['nullable', 'string', Rule::in(ProductInfoBoxStyleEnum::values())],
            'loyalty_points_product_info_bg_color' => ['nullable', 'string', 'max:30'],
            'loyalty_points_product_info_text_color' => ['nullable', 'string', 'max:30'],
            'loyalty_points_product_info_icon_color' => ['nullable', 'string', 'max:30'],
            'loyalty_points_product_info_border_color' => ['nullable', 'string', 'max:30'],
            'loyalty_points_product_info_border_radius' => ['nullable', 'integer', 'min:0', 'max:50'],
            'loyalty_points_product_info_padding' => ['nullable', 'integer', 'min:0', 'max:100'],
            'loyalty_points_customer_page_slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9\-\/]+$/'],
            'loyalty_points_enable_email_notification' => ['nullable', 'boolean'],
            'loyalty_points_notification_emails' => ['nullable', 'string', 'max:500'],
        ];

        if (is_plugin_active('language')) {
            $languages = Language::getActiveLanguage(['lang_locale']);

            foreach ($languages as $language) {
                $rules['loyalty_points_customer_page_slug_' . $language->lang_locale] = [
                    'nullable',
                    'string',
                    'max:100',
                    'regex:/^[a-z0-9\-\/]+$/',
                ];
            }
        }

        return $rules;
    }
}
