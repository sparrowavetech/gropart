<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_customer_points_balances')) {
            Schema::create('ec_customer_points_balances', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id');
                $table->integer('total_points')->default(0);
                $table->integer('lifetime_points')->default(0)->comment('Total points earned in lifetime');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique('customer_id');
            });
        }

        if (! Schema::hasTable('ec_customer_points_transactions')) {
            Schema::create('ec_customer_points_transactions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id');
                $table->foreignId('order_id')->nullable();
                $table->enum('type', ['earn', 'redeem', 'adjust', 'reverse'])->default('earn');
                $table->integer('points')->comment('Positive for earn/adjust up, negative for redeem/reverse');
                $table->text('note')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index('customer_id');
                $table->index('order_id');
                $table->index('type');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_customer_points_transactions');
        Schema::dropIfExists('ec_customer_points_balances');
    }
};
