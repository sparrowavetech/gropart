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
            if (!Schema::hasColumn('mp_customer_revenues', 'shipping_cost')) {
                $table->string('shipping_cost')->nullable();
            }
            if (!Schema::hasColumn('mp_customer_revenues', 'platform_fee')) {
                $table->string('platform_fee')->nullable();
            }
            if (!Schema::hasColumn('mp_customer_revenues', 'commission_fee')) {
                $table->string('commission_fee')->nullable();
            }
            if (!Schema::hasColumn('mp_customer_revenues', 'fee_tax_rate')) {
                $table->string('fee_tax_rate')->nullable();
            }
            if (!Schema::hasColumn('mp_customer_revenues', 'seller_state_code')) {
                $table->string('seller_state_code')->nullable();
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
            $table->dropColumn('shipping_cost');
            $table->dropColumn('platform_fee');
            $table->dropColumn('commission_fee');
            $table->dropColumn('fee_tax_rate');
            $table->dropColumn('seller_state_code');
        });
    }
};
