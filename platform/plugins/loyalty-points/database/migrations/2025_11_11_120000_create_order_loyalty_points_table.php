<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_order_loyalty_points')) {
            Schema::create('ec_order_loyalty_points', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->unique();
                $table->integer('points_redeemed')->default(0)->comment('Points used for discount');
                $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount from redeemed points');
                $table->integer('points_to_earn')->default(0)->comment('Points customer will earn when order completes');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_order_loyalty_points');
    }
};
