<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('product_bundles')) {
            return;
        }

        Schema::table('product_bundles', function (Blueprint $table) {
            if (! Schema::hasColumn('product_bundles', 'ecommerce_product_id')) {
                $table->unsignedBigInteger('ecommerce_product_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_bundles')) {
            return;
        }

        Schema::table('product_bundles', function (Blueprint $table) {
            if (Schema::hasColumn('product_bundles', 'ecommerce_product_id')) {
                $table->dropIndex(['ecommerce_product_id']);
                $table->dropColumn('ecommerce_product_id');
            }
        });
    }
};
