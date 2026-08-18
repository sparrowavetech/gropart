<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_products')) {
            return;
        }

        if (! Schema::hasColumn('ec_products', 'hsn_code')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                $table->string('hsn_code', 150)->nullable()->after('sku');
            });
        }

        if (Schema::hasColumn('ec_products', 'barcode')) {
            DB::table('ec_products')
                ->whereNull('hsn_code')
                ->whereNotNull('barcode')
                ->where('barcode', '!=', '')
                ->update(['hsn_code' => DB::raw('barcode')]);

        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ec_products') && Schema::hasColumn('ec_products', 'hsn_code')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                $table->dropColumn('hsn_code');
            });
        }
    }
};
