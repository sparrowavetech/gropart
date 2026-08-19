<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_products') && ! Schema::hasColumn('ec_products', 'product_free_shipping')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->boolean('product_free_shipping')->default(0)->after('is_featured')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ec_products') && Schema::hasColumn('ec_products', 'product_free_shipping')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->dropColumn('product_free_shipping');
            });
        }
    }
};
