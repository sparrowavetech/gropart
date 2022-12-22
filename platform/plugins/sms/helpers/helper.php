<?php

use Botble\Sms\Models\Sms;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Facades\SmsHelperFacade;
if (!function_exists('get_setting_sms_template_content')) {
    /**
     * Get content of email template if module need to config email template
     * @param string $type type of module is system or plugins
     * @param string $module
     * @param string $templateKey key is config in config.email.templates.$key
     * @return bool|mixed|null
     */
    function get_setting_sms_template_content(string $templateKey)
    {
        return  Sms::where('name',$templateKey)->first();
    }
}
if (!function_exists('get_setting_sms_status')) {
    /**
     * Get content of email template if module need to config email template
     * @param string $type type of module is system or plugins
     * @param string $module
     * @param string $templateKey key is config in config.email.templates.$key
     * @return bool|mixed|null
     */
    function get_setting_sms_status(string $templateKey)
    {
        return Sms::where('name',$templateKey)->where('status',BaseStatusEnum::PUBLISHED())->count();
     
    }
}
if (!function_exists('get_sms_setting')) {
    /**
     * @param string $key
     * @param string|null $default
     * @return string|array|null
     */
    function get_sms_setting(string $key, ?string $default = '')
    {
        return setting(SmsHelperFacade::getSettingPrefix() . $key, $default);
    }
}