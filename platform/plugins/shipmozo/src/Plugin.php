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
            'shipping_shipmozo_public_key',
            'shipping_shipmozo_private_key',
            'shipping_shipmozo_webhook_secret',
            'shipping_shipmozo_logging',
            'shipping_shipmozo_webhooks',
            'shipping_shipmozo_rate_adjustment_type',
            'shipping_shipmozo_rate_adjustment_value',
        ]);
    }
}
