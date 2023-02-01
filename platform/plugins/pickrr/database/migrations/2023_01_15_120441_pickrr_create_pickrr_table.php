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
        Schema::create('pickrrs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('pickrrs_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->integer('pickrrs_id');
            $table->string('name', 255)->nullable();

            $table->primary(['lang_code', 'pickrrs_id'], 'pickrrs_translations_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pickrrs');
        Schema::dropIfExists('pickrrs_translations');
    }
};
