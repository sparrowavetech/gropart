<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('razorpay_emi_vendor_requests')) {
            Schema::create('razorpay_emi_vendor_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('store_id')->index();
                $table->string('requested_scheme', 20); // standard, no_cost, low_cost
                $table->string('subvention_willingness', 20)->default('seller'); // seller, platform
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('pending')->index(); // pending, approved, rejected
                $table->text('admin_remarks')->nullable();
                $table->string('approved_offer_id', 100)->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                if (Schema::hasTable('ec_products')) {
                    $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('razorpay_emi_vendor_requests');
    }
};

