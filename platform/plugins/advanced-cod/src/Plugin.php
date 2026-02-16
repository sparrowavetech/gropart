<?php

namespace SparroWave\AdvancedCod;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{

    public static function remove(): void
    {
        Schema::table('ec_products', function ($table) {
            $table->dropColumn('is_cod_eligible');
        });

        Schema::table('ec_orders', function ($table) {
            $table->dropColumn(['cod_prepayment_amount', 'cod_remaining_amount']);
        });
    }
}
