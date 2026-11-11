<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('razorpay_emi_product_configs')) {
            Schema::table('razorpay_emi_product_configs', function (Blueprint $table) {
                if (! Schema::hasColumn('razorpay_emi_product_configs', 'offer_mode')) {
                    $table->string('offer_mode', 20)->default('auto')->after('emi_type');
                }
            });

            // Modify offer_id to TEXT to store multiple offer IDs (JSON)
            DB::statement('ALTER TABLE razorpay_emi_product_configs MODIFY COLUMN offer_id TEXT NULL');
        }

        if (Schema::hasTable('razorpay_emi_vendor_requests')) {
            DB::statement('ALTER TABLE razorpay_emi_vendor_requests MODIFY COLUMN approved_offer_id TEXT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('razorpay_emi_product_configs')) {
            Schema::table('razorpay_emi_product_configs', function (Blueprint $table) {
                if (Schema::hasColumn('razorpay_emi_product_configs', 'offer_mode')) {
                    $table->dropColumn('offer_mode');
                }
            });
        }
    }
};

