<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('mp_stores')) {
            Schema::table('mp_stores', function (Blueprint $table) {
                if (! Schema::hasColumn('mp_stores', 'gstin')) {
                    $table->string('gstin', 30)->nullable()->after('phone');
                }
                if (! Schema::hasColumn('mp_stores', 'vendor_managed_shipping')) {
                    $table->boolean('vendor_managed_shipping')->default(0)->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mp_stores')) {
            Schema::table('mp_stores', function (Blueprint $table) {
                $columns = array_filter([
                    Schema::hasColumn('mp_stores', 'gstin') ? 'gstin' : null,
                    Schema::hasColumn('mp_stores', 'vendor_managed_shipping') ? 'vendor_managed_shipping' : null,
                ]);

                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
