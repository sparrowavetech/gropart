<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('india_sms_templates')) {
            return;
        }

        Schema::create('india_sms_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 191);
            $table->string('key', 100)->unique();
            $table->string('language', 10)->default('en');
            $table->text('content');
            $table->string('gateway', 60)->nullable();
            $table->string('sender_id', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('variables')->nullable();
            $table->json('gateway_template_ids')->nullable();
            $table->json('gateway_sender_ids')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('india_sms_templates')->insert([
            ['name' => 'General OTP', 'key' => 'otp', 'language' => 'en', 'content' => 'Your {{ site_name }} verification code is {{ code }}. It expires in {{ expires_in }} minutes.', 'variables' => json_encode(['site_name', 'code', 'expires_in']), 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Registration OTP', 'key' => 'registration_otp', 'language' => 'en', 'content' => 'Your {{ site_name }} registration code is {{ code }}. It expires in {{ expires_in }} minutes.', 'variables' => json_encode(['site_name', 'code', 'expires_in']), 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Login OTP', 'key' => 'login_otp', 'language' => 'en', 'content' => 'Use {{ code }} to sign in to {{ site_name }}. The code expires in {{ expires_in }} minutes.', 'variables' => json_encode(['site_name', 'code', 'expires_in']), 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Password Reset OTP', 'key' => 'password_reset_otp', 'language' => 'en', 'content' => 'Your {{ site_name }} password reset code is {{ code }}. Do not share it with anyone.', 'variables' => json_encode(['site_name', 'code']), 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Order Confirmed', 'key' => 'order_confirmed', 'language' => 'en', 'content' => 'Hi {{ customer_name }}, your order {{ order_id }} has been confirmed. Total: {{ order_total }}.', 'variables' => json_encode(['customer_name', 'order_id', 'order_total']), 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('india_sms_templates');
    }
};
