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
        Schema::table('mp_stores', function (Blueprint $table) {
            // Check if the column already exists
            if (!Schema::hasColumn('mp_stores', 'is_manage_shipping')) {
                $table->integer('is_manage_shipping')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table) {
            // Drop the column if it exists
            $table->dropColumn('is_manage_shipping');
        });
    }
};
