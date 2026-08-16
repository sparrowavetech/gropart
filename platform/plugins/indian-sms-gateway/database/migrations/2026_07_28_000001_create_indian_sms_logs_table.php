<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('india_sms_logs')) {
            return;
        }

        Schema::create('india_sms_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('gateway', 60)->index();
            $table->string('recipient', 30)->index();
            $table->string('sender_id', 100)->nullable();
            $table->text('message');
            $table->string('message_type', 30)->default('transactional');
            $table->unsignedSmallInteger('segments')->default(1);
            $table->string('status', 30)->default('queued')->index();
            $table->string('provider_message_id', 191)->nullable()->index();
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->dateTime('queued_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('india_sms_logs');
    }
};
