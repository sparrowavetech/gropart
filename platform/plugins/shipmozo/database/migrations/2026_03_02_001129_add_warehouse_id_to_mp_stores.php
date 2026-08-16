<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('mp_stores') || Schema::hasColumn('mp_stores', 'warehouse_id')) {
            return;
        }

        Schema::table('mp_stores', function (Blueprint $table) {
            $table->string('warehouse_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('mp_stores') || ! Schema::hasColumn('mp_stores', 'warehouse_id')) {
            return;
        }

        Schema::table('mp_stores', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
    }
};
