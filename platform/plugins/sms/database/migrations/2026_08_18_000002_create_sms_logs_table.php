<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sms_logs')) {
            return;
        }

        Schema::create('sms_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('job_id', 60)->nullable()->index();
            $table->string('message_id', 191)->nullable()->index();
            $table->string('template', 100)->nullable()->index();
            $table->string('template_id', 100)->nullable();
            $table->string('recipient', 30)->index();
            $table->text('message');
            $table->string('status', 60)->default('Submitted')->index();
            $table->dateTime('sent_at')->nullable()->index();
            $table->dateTime('delivered_at')->nullable()->index();
            $table->string('delivery_error_code', 60)->nullable();
            $table->decimal('cost', 12, 5)->nullable();
            $table->string('op_cr', 100)->nullable();
            $table->json('response_payload')->nullable();
            $table->json('delivery_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
