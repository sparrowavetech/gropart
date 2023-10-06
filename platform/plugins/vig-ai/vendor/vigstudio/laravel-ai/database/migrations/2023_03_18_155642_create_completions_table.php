<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vig_ai_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->string('external_id')->nullable();
            $table->longText('prompt');
            $table->longText('answer');
            $table->timestamps();

            $table->foreign('model_id')
            ->references('id')
            ->on('vig_ai_models')
            ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vig_ai_completions');
    }
};