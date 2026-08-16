<?php

namespace SparroWave\Shipmozo\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Setting\Supports\SettingStore;
use Botble\Support\Services\Cache\Cache;
use Illuminate\Http\Request;

class ShipmozoSettingController extends BaseController
{
    public function update(Request $request, BaseHttpResponse $response, SettingStore $settingStore)
    {
        $data = $request->validate([
            'shipping_shipmozo_public_key' => ['nullable', 'string', 'max:255'],
            'shipping_shipmozo_private_key' => ['nullable', 'string', 'max:255'],
            'shipping_shipmozo_webhook_secret' => ['nullable', 'string', 'min:16', 'max:255'],
            'shipping_shipmozo_status' => ['nullable', 'boolean'],
            'shipping_shipmozo_logging' => ['nullable', 'boolean'],
            'shipping_shipmozo_webhooks' => ['nullable', 'boolean'],
            'shipping_shipmozo_rate_adjustment_type' => ['required', 'in:none,fixed,percent'],
            'shipping_shipmozo_rate_adjustment_value' => ['required', 'numeric', 'min:0', 'max:100000'],
        ]);

        foreach (['shipping_shipmozo_status', 'shipping_shipmozo_logging', 'shipping_shipmozo_webhooks'] as $key) {
            $data[$key] = $request->boolean($key);
        }

        foreach ($data as $settingKey => $settingValue) {
            $settingStore->set($settingKey, $settingValue);
        }

        $settingStore->save();

        Cache::make(HandleShippingFeeService::class)->flush();

        $message = trans('plugins/shipmozo::shipmozo.saved_shipping_settings_success');
        $isError = false;

        return $response->setError($isError)->setMessage($message);
    }
}
