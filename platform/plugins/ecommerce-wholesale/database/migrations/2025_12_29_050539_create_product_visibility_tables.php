<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ws_product_visibility', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('visibility_type', 60)->default('public');
            $table->timestamps();
            $table->unique('product_id');
        });

        Schema::create('ws_product_group_access', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('customer_group_id');
            $table->timestamps();
            $table->unique(['product_id', 'customer_group_id'], 'unique_product_group');
            $table->index('product_id');
            $table->index('customer_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ws_product_group_access');
        Schema::dropIfExists('ws_product_visibility');
    }
};
