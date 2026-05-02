<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcProductFrequentlyBoughtTogether extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ec_product_frequently_bought_together', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->integer('from_product_id')->unsigned()->index();
            $table->integer('to_product_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ec_product_frequently_bought_together', function (Blueprint $table) {
            Schema::dropIfExists('ec_product_frequently_bought_together');
        });
    }
}
