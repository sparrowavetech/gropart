<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ip_blocker_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->integer('count_requests')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_blocker_logs');
    }
};
