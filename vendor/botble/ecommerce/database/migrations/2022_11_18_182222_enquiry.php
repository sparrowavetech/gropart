<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Enquiry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ec_product_enquiry')) {
            Schema::create('ec_product_enquiry', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('product_id')->comment('Product id');
                $table->string('name');
                $table->string('email');
                $table->string('phone');
                $table->integer('country');
                $table->integer('state');
                $table->integer('city');
                $table->text('address');
                $table->string('zip_code')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ec_product_enquiry');
    }
}
