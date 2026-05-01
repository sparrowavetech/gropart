<?php

namespace Botble\LoyaltyPoints;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public const ASSETS_VERSION = '1.0.7';

    public static function remove(): void
    {
        Schema::dropIfExists('ec_order_loyalty_points');
        Schema::dropIfExists('ec_customer_points_transactions');
        Schema::dropIfExists('ec_customer_points_balances');
    }
}
