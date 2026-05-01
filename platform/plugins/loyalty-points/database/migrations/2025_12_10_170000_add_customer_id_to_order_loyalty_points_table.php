<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_order_loyalty_points') && ! Schema::hasColumn('ec_order_loyalty_points', 'customer_id')) {
            Schema::table('ec_order_loyalty_points', function (Blueprint $table): void {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('order_id')
                    ->comment('Customer ID for guest orders with member ID');

                $table->index('customer_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ec_order_loyalty_points') && Schema::hasColumn('ec_order_loyalty_points', 'customer_id')) {
            Schema::table('ec_order_loyalty_points', function (Blueprint $table): void {
                $table->dropIndex(['customer_id']);
                $table->dropColumn('customer_id');
            });
        }
    }
};
