<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_products', function (Blueprint $table) {
            if (! Schema::hasIndex('ec_products', 'ec_products_status_is_variation_index')) {
                $table->index(['status', 'is_variation'], 'ec_products_status_is_variation_index');
            }
            
            if (! Schema::hasIndex('ec_products', 'ec_products_is_featured_index')) {
                $table->index('is_featured', 'ec_products_is_featured_index');
            }
            
            if (! Schema::hasIndex('ec_products', 'ec_products_sale_price_price_index')) {
                $table->index(['sale_price', 'price'], 'ec_products_sale_price_price_index');
            }
            
            if (! Schema::hasIndex('ec_products', 'ec_products_stock_management_index')) {
                $table->index(['with_storehouse_management', 'quantity', 'stock_status'], 'ec_products_stock_management_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->dropIndex(['status', 'is_variation']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['sale_price', 'price']);
            $table->dropIndex(['with_storehouse_management', 'quantity', 'stock_status']);
        });
    }
};
