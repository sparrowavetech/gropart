<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToEcProductEnquiryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_product_enquiry', function (Blueprint $table) {
            $table->integer('store_id')->unsigned()->nullable()->comment('Store ID')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ec_product_enquiry', function (Blueprint $table) {
            $table->dropColumn('shop_id');
        });
    }
}
