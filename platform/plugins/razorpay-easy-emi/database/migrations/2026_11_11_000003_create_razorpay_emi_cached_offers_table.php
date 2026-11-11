<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('razorpay_emi_cached_offers')) {
            Schema::create('razorpay_emi_cached_offers', function (Blueprint $table) {
                $table->string('id', 100)->primary(); // e.g. offer_Nce781...
                $table->string('name', 255);
                $table->string('type', 50)->nullable();
                $table->text('description')->nullable();
                $table->decimal('min_order_amount', 15, 2)->nullable();
                $table->decimal('max_discount', 15, 2)->nullable();
                $table->longText('raw_payload')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('synced_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('razorpay_emi_cached_offers');
    }
};
