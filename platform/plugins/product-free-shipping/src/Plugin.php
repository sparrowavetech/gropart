<?php

namespace SparroWave\ProductFreeShipping;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        if (Schema::hasTable('ec_products') && Schema::hasColumn('ec_products', 'product_free_shipping')) {
            Schema::table('ec_products', function ($table) {
                $table->dropColumn('product_free_shipping');
            });
        }
    }
}
