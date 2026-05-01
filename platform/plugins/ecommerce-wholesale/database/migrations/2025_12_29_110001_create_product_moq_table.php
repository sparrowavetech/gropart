<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ws_product_moq', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->integer('min_quantity')->default(1);
            $table->integer('quantity_increment')->default(1);
            $table->timestamps();
            $table->unique(['product_id', 'customer_group_id'], 'unique_product_group_moq');
            $table->index('product_id');
        });

        Schema::table('ws_customer_groups', function (Blueprint $table): void {
            $table->integer('min_order_quantity')->nullable()->after('priority');
            $table->decimal('min_order_value', 15, 2)->nullable()->after('min_order_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ws_product_moq');

        Schema::table('ws_customer_groups', function (Blueprint $table): void {
            $table->dropColumn(['min_order_quantity', 'min_order_value']);
        });
    }
};
