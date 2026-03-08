<?php

namespace SparroWave\Shipmozo;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'shipping_shipmozo_status',
            'shipping_shipmozo_test_key',
            'shipping_shipmozo_production_key',
            'shipping_shipmozo_sandbox',
            'shipping_shipmozo_logging',
            'shipping_shipmozo_cache_response',
            'shipping_shipmozo_webhooks',
        ]);
    }
}
