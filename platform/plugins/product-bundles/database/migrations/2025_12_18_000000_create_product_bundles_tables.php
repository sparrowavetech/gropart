<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();

            // fixed | mix
            $table->string('type', 20)->default('fixed');

            $table->boolean('is_active')->default(true);

            // fixed_total | percent_off | amount_off
            $table->string('pricing_type', 30)->default('percent_off');
            $table->decimal('pricing_value', 15, 4)->default(0);

            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('product_bundle_products', function (Blueprint $table) {
            $table->unsignedBigInteger('bundle_id');
            $table->unsignedBigInteger('product_id');

            $table->primary(['bundle_id', 'product_id'], 'pb_products_primary');
            $table->index(['product_id']);
        });

        Schema::create('product_bundle_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bundle_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['bundle_id']);
            $table->index(['product_id']);
        });

        Schema::create('product_bundle_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bundle_id');
            $table->string('name', 255);
            $table->unsignedInteger('choose_min')->default(1);
            $table->unsignedInteger('choose_max')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['bundle_id']);
        });

        Schema::create('product_bundle_group_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group_id']);
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bundle_group_items');
        Schema::dropIfExists('product_bundle_groups');
        Schema::dropIfExists('product_bundle_items');
        Schema::dropIfExists('product_bundle_products');
        Schema::dropIfExists('product_bundles');
    }
};