<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ws_group_pricing_rules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();
            $table->string('discount_type', 60)->default('percentage');
            $table->decimal('discount_value', 15, 2);
            $table->string('status', 60)->default('published');
            $table->timestamps();

            $table->index('product_id');
            $table->index(['product_id', 'customer_group_id']);
            $table->index('store_id');
            $table->index(['min_quantity', 'max_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ws_group_pricing_rules');
    }
};
