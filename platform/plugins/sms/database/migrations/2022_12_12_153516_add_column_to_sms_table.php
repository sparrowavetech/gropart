<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms', function (Blueprint $table) {
            if (! Schema::hasColumn('sms', 'template')) {
                $table->text('template')->nullable();
            }

            if (! Schema::hasColumn('sms', 'template_id')) {
                $table->string('template_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms', function (Blueprint $table) {
            if (Schema::hasColumn('sms', 'template')) {
                $table->dropColumn('template');
            }

            if (Schema::hasColumn('sms', 'template_id')) {
                $table->dropColumn('template_id');
            }
        });
    }
}
