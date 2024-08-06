<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 60)->default('pending');
            $table->timestamps();
        });

        Schema::create('sms_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->integer('sms_id');
            $table->string('name', 255)->nullable();

            $table->primary(['lang_code', 'sms_id'], 'sms_translations_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
        Schema::dropIfExists('sms_translations');
    }
};
