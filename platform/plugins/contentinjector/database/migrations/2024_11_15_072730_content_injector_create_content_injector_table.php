<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('content_injectors')) {
            Schema::create('content_injectors', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->longText('value');
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_injectors_translations')) {
            Schema::create('content_injectors_translations', function (Blueprint $table) {
                $table->string('lang_code');
                $table->foreignId('content_injectors_id');
                $table->string('name', 255)->nullable();
                $table->longText('value')->nullable();
                $table->primary(['lang_code', 'content_injectors_id'], 'content_injectors_translations_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_injectors');
        Schema::dropIfExists('content_injectors_translations');
    }
};
