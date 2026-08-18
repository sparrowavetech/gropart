<?php

namespace SparroWave\AdvancedCod;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{

    public static function remove(): void
    {
        if (Schema::hasTable('ec_products') && Schema::hasColumn('ec_products', 'is_cod_eligible')) {
            Schema::table('ec_products', function ($table) {
                $table->dropColumn('is_cod_eligible');
            });
        }

        if (Schema::hasTable('ec_products') && Schema::hasColumn('ec_products', 'hsn_code')) {
            Schema::table('ec_products', function ($table) {
                $table->dropColumn('hsn_code');
            });
        }

        if (Schema::hasTable('ec_orders')) {
            $columns = array_filter([
                Schema::hasColumn('ec_orders', 'cod_prepayment_amount') ? 'cod_prepayment_amount' : null,
                Schema::hasColumn('ec_orders', 'cod_remaining_amount') ? 'cod_remaining_amount' : null,
            ]);

            if ($columns) {
                Schema::table('ec_orders', function ($table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
}
