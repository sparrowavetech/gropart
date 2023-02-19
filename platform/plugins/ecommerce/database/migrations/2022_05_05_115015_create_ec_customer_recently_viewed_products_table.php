<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('ec_customer_recently_viewed_products');

        Schema::create('ec_customer_recently_viewed_products', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->unsigned();
            $table->integer('product_id')->unsigned();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_customer_recently_viewed_products');
    }
};
