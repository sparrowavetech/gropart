<?php

use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Illuminate\Support\Arr;

if (!function_exists('get_marketplace_setting_key')) {
    /**
     * @param string $key
     * @return string
     */
    function get_marketplace_setting_key($key = '')
    {
        return config('plugins.marketplace.general.prefix') . $key;
    }
}

if (!function_exists('get_marketplace_setting')) {
    /**
     * @param string $key
     * @param null $default
     * @return string
     */
    function get_marketplace_setting($key, $default = '')
    {
        return setting(get_marketplace_setting_key($key), $default);
    }
}

if (!function_exists('get_discount_type_options_for_vendor')) {
    /**
     * @param string $key
     * @param null $default
     * @return array
     */
    function get_discount_type_options_for_vendor()
    {
        return Arr::except(DiscountTypeOptionEnum::labels(), [DiscountTypeOptionEnum::SAME_PRICE]);
    }
}
