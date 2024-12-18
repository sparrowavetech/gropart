<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->string('email', 120);
            $table->string('name', 120)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('status', 60)->default('subscribed');
            $table->timestampss();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletters');
    }
};
