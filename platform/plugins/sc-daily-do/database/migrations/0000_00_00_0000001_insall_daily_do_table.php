<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('daily_dos')) {
            Schema::create('daily_dos', function (Blueprint $table) {
                $table->id();
                $table->nullableMorphs('module');
                $table->string('title', 100)->nullable();
                $table->string('description', 255)->nullable();
                $table->date('due_date')->nullable();
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_dos');
    }
};
