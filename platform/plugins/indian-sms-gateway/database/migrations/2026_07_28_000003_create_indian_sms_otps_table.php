<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('india_sms_otps')) {
            return;
        }

        Schema::create('india_sms_otps', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->char('phone_hash', 64)->index();
            $table->text('phone_encrypted');
            $table->string('purpose', 60)->index();
            $table->string('token_hash', 255);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(5);
            $table->string('status', 30)->default('pending')->index();
            $table->char('request_ip_hash', 64)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->dateTime('resend_available_at')->nullable();
            $table->dateTime('expires_at')->index();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('india_sms_otps');
    }
};
