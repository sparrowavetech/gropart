<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToEnquiryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_product_enquiry', function (Blueprint $table) {
            $table->string('status');
            $table->text('description')->nullable();
            $table->text('attachment')->nullable();
            $table->text('code')->nullable();
            
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
            $table->dropColumn('status');
            $table->dropColumn('description');
            $table->dropColumn('attachment');
            $table->dropColumn('code');
        });
    }
}
