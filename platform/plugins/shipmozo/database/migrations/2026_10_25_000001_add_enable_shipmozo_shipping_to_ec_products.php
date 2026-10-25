<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ec_products') || Schema::hasColumn('ec_products', 'enable_shipmozo_shipping')) {
            return;
        }

        Schema::table('ec_products', function (Blueprint $table) {
            $table->boolean('enable_shipmozo_shipping')->default(0)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ec_products') || ! Schema::hasColumn('ec_products', 'enable_shipmozo_shipping')) {
            return;
        }

        Schema::table('ec_products', function (Blueprint $table) {
            $table->dropColumn('enable_shipmozo_shipping');
        });
    }
};
