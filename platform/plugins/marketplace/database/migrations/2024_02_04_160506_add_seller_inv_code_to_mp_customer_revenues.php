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
        Schema::table('mp_customer_revenues', function (Blueprint $table) {
            // Check if the column already exists
            if (!Schema::hasColumn('mp_customer_revenues', 'seller_inv_code')) {
                $table->string('seller_inv_code')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mp_customer_revenues', function (Blueprint $table) {
            // Drop the column if it exists
            $table->dropColumn('seller_inv_code');
        });
    }
};
