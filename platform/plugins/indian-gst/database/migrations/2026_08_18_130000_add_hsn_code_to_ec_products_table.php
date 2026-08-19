<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_products') && ! Schema::hasColumn('ec_products', 'hsn_code')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->string('hsn_code', 50)->nullable()->after('sku');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ec_products') && Schema::hasColumn('ec_products', 'hsn_code')) {
            Schema::table('ec_products', function (Blueprint $table) {
                $table->dropColumn('hsn_code');
            });
        }
    }
};
