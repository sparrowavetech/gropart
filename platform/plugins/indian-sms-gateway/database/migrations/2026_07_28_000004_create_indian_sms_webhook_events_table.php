<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('india_sms_webhook_events')) {
            return;
        }

        Schema::create('india_sms_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('gateway', 60)->index();
            $table->string('event_id', 191)->nullable();
            $table->string('provider_message_id', 191)->nullable()->index();
            $table->string('status', 60)->nullable();
            $table->json('payload');
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['gateway', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('india_sms_webhook_events');
    }
};
