<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('loyalty_levels_translations')) {
            Schema::create('loyalty_levels_translations', function (Blueprint $table): void {
                $table->string('lang_code');
                $table->foreignId('loyalty_levels_id');
                $table->string('name')->nullable();
                $table->text('benefits')->nullable();

                $table->primary(['lang_code', 'loyalty_levels_id'], 'loyalty_levels_translations_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_levels_translations');
    }
};
