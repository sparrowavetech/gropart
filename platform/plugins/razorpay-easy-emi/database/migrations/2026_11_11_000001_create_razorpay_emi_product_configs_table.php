<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('razorpay_emi_product_configs')) {
            Schema::create('razorpay_emi_product_configs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->unique();
                $table->string('status', 20)->default('inherit'); // inherit, enabled, disabled
                $table->string('emi_type', 20)->default('standard'); // standard, no_cost, low_cost
                $table->string('offer_id', 100)->nullable();
                $table->string('subvention_payer', 20)->default('platform'); // platform, seller
                $table->string('custom_badge', 255)->nullable();
                $table->decimal('min_amount', 15, 2)->nullable();
                $table->timestamps();

                $table->index(['product_id', 'status']);
                if (Schema::hasTable('ec_products')) {
                    $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('razorpay_emi_product_configs');
    }
};

