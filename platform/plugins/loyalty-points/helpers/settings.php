<?php

use Botble\Language\Facades\Language;

if (! function_exists('get_loyalty_setting')) {
    function get_loyalty_setting(string $key, mixed $default = null): mixed
    {
        return setting('loyalty_points_' . $key, $default);
    }
}

if (! function_exists('set_loyalty_setting')) {
    function set_loyalty_setting(string $key, mixed $value): void
    {
        setting()->set('loyalty_points_' . $key, $value)->save();
    }
}

if (! function_exists('get_loyalty_customer_page_slug')) {
    function get_loyalty_customer_page_slug(?string $locale = null): string
    {
        $default = 'customer/loyalty-points';

        if (! is_plugin_active('language')) {
            return get_loyalty_setting('customer_page_slug', $default);
        }

        $currentLocale = $locale ?: Language::getCurrentLocale();
        $defaultLocale = Language::getDefaultLocale();

        if ($currentLocale && $currentLocale !== $defaultLocale) {
            $localizedSlug = get_loyalty_setting('customer_page_slug_' . $currentLocale);

            if ($localizedSlug) {
                return $localizedSlug;
            }
        }

        return get_loyalty_setting('customer_page_slug', $default);
    }
}

if (! function_exists('get_loyalty_customer_page_url')) {
    function get_loyalty_customer_page_url(?string $locale = null): string
    {
        $slug = get_loyalty_customer_page_slug($locale);

        if (is_plugin_active('language')) {
            $currentLocale = $locale ?: Language::getCurrentLocale();
            $defaultLocale = Language::getDefaultLocale();
            $hideDefaultLocale = Language::hideDefaultLocaleInURL();

            if ($currentLocale && (! $hideDefaultLocale || $currentLocale !== $defaultLocale)) {
                return url($currentLocale . '/' . $slug);
            }
        }

        return url($slug);
    }
}
