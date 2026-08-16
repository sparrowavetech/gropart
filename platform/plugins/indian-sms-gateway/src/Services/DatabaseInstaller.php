<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DatabaseInstaller
{
    private static bool $checked = false;

    public function ensure(): bool
    {
        if (self::$checked && $this->ready()) {
            return true;
        }

        self::$checked = true;

        try {
            $this->createLogs();
            $this->createTemplates();
            $this->addTemplateGatewayColumns();
            $this->createOtps();
            $this->createWebhookEvents();
            $this->createVerifiedPhones();
            $this->addOtpColumns();
            $this->seedTemplates();

            return $this->ready();
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    public function ready(): bool
    {
        foreach ($this->requiredTables() as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    public function missingTables(): array
    {
        return array_values(array_filter(
            $this->requiredTables(),
            fn (string $table): bool => ! Schema::hasTable($table)
        ));
    }

    public function requiredTables(): array
    {
        return [
            'india_sms_logs',
            'india_sms_templates',
            'india_sms_otps',
            'india_sms_webhook_events',
            'india_sms_verified_phones',
        ];
    }

    private function createLogs(): void
    {
        if (Schema::hasTable('india_sms_logs')) return;

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

    private function createTemplates(): void
    {
        if (Schema::hasTable('india_sms_templates')) return;

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
    }

    private function addTemplateGatewayColumns(): void
    {
        if (! Schema::hasTable('india_sms_templates')) {
            return;
        }

        if (! Schema::hasColumn('india_sms_templates', 'gateway_template_ids')) {
            Schema::table('india_sms_templates', function (Blueprint $table): void {
                $table->json('gateway_template_ids')->nullable()->after('variables');
            });
        }

        if (! Schema::hasColumn('india_sms_templates', 'gateway_sender_ids')) {
            Schema::table('india_sms_templates', function (Blueprint $table): void {
                $table->json('gateway_sender_ids')->nullable()->after('gateway_template_ids');
            });
        }
    }

    private function createOtps(): void
    {
        if (Schema::hasTable('india_sms_otps')) return;

        Schema::create('india_sms_otps', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->char('phone_hash', 64)->index();
            $table->text('phone_encrypted');
            $table->string('purpose', 60)->index();
            $table->string('token_hash', 255);
            $table->char('verification_token_hash', 64)->nullable()->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(5);
            $table->string('status', 30)->default('pending')->index();
            $table->char('request_ip_hash', 64)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->dateTime('resend_available_at')->nullable();
            $table->dateTime('expires_at')->index();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    private function createWebhookEvents(): void
    {
        if (Schema::hasTable('india_sms_webhook_events')) return;

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

    private function createVerifiedPhones(): void
    {
        if (Schema::hasTable('india_sms_verified_phones')) return;

        Schema::create('india_sms_verified_phones', function (Blueprint $table): void {
            $table->id();
            $table->char('phone_hash', 64)->index();
            $table->text('phone_encrypted');
            $table->string('purpose', 60)->index();
            $table->nullableMorphs('subject');
            $table->dateTime('verified_at')->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->dateTime('last_used_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['phone_hash', 'purpose'],
                'india_sms_verified_phone_purpose_unique'
            );
        });
    }

    private function addOtpColumns(): void
    {
        if (! Schema::hasTable('india_sms_otps')) return;

        if (! Schema::hasColumn('india_sms_otps', 'verification_token_hash')) {
            Schema::table('india_sms_otps', function (Blueprint $table): void {
                $table->char('verification_token_hash', 64)
                    ->nullable()->after('token_hash')->index();
            });
        }

        if (! Schema::hasColumn('india_sms_otps', 'consumed_at')) {
            Schema::table('india_sms_otps', function (Blueprint $table): void {
                $table->dateTime('consumed_at')
                    ->nullable()->after('verified_at');
            });
        }
    }

    private function seedTemplates(): void
    {
        if (! Schema::hasTable('india_sms_templates')) return;

        $templates = [
            'otp' => ['General OTP', 'Your {{ site_name }} verification code is {{ code }}. It expires in {{ expires_in }} minutes.', ['site_name', 'code', 'expires_in']],
            'registration_otp' => ['Registration OTP', 'Your {{ site_name }} registration code is {{ code }}. It expires in {{ expires_in }} minutes.', ['site_name', 'code', 'expires_in']],
            'login_otp' => ['Login OTP', 'Use {{ code }} to sign in to {{ site_name }}. The code expires in {{ expires_in }} minutes.', ['site_name', 'code', 'expires_in']],
            'password_reset_otp' => ['Password Reset OTP', 'Your {{ site_name }} password reset code is {{ code }}.', ['site_name', 'code']],
            'checkout_otp' => ['Checkout OTP', 'Your {{ site_name }} checkout code is {{ code }}. It expires in {{ expires_in }} minutes.', ['site_name', 'code', 'expires_in']],
            'order_created_customer' => ['Customer: New Order Received', 'Hi {{ customer_name }}, your order {{ order_id }} has been received. Total: {{ order_total }}.', ['customer_name', 'order_id', 'order_total']],
            'order_created_admin' => ['Admin: New Order Alert', 'New order {{ order_id }} from {{ customer_name }}. Total: {{ order_total }}. Phone: {{ customer_phone }}.', ['order_id', 'customer_name', 'order_total', 'customer_phone']],
            'order_status_changed_customer' => ['Customer: Order Status Changed', 'Hi {{ customer_name }}, your order {{ order_id }} status is now {{ status }}.', ['customer_name', 'order_id', 'status', 'previous_status']],
            'order_status_pending' => ['Customer: Order Pending', 'Hi {{ customer_name }}, your order {{ order_id }} is pending confirmation.', ['customer_name', 'order_id', 'status']],
            'order_status_processing' => ['Customer: Order Processing', 'Hi {{ customer_name }}, your order {{ order_id }} is being processed.', ['customer_name', 'order_id', 'status']],
            'order_status_confirmed' => ['Customer: Order Confirmed', 'Hi {{ customer_name }}, your order {{ order_id }} has been confirmed.', ['customer_name', 'order_id', 'status']],
            'order_status_completed' => ['Customer: Order Completed', 'Hi {{ customer_name }}, your order {{ order_id }} has been completed.', ['customer_name', 'order_id', 'status']],
            'order_status_delivered' => ['Customer: Order Delivered', 'Hi {{ customer_name }}, your order {{ order_id }} has been delivered.', ['customer_name', 'order_id', 'status']],
            'order_status_canceled' => ['Customer: Order Canceled', 'Hi {{ customer_name }}, your order {{ order_id }} has been canceled.', ['customer_name', 'order_id', 'status']],
            'order_status_cancelled' => ['Customer: Order Cancelled', 'Hi {{ customer_name }}, your order {{ order_id }} has been cancelled.', ['customer_name', 'order_id', 'status']],
            'order_status_returned' => ['Customer: Order Returned', 'Hi {{ customer_name }}, your order {{ order_id }} has been returned.', ['customer_name', 'order_id', 'status']],
            'order_status_partial_returned' => ['Customer: Order Partially Returned', 'Hi {{ customer_name }}, your order {{ order_id }} has been partially returned.', ['customer_name', 'order_id', 'status']],
            'order_payment_confirmed_customer' => ['Customer: Payment Confirmed', 'Hi {{ customer_name }}, payment for order {{ order_id }} has been confirmed.', ['customer_name', 'order_id', 'order_total']],
            'shipping_status_changed_customer' => ['Customer: Shipping Status Changed', 'Hi {{ customer_name }}, shipping for order {{ order_id }} is now {{ shipping_status }}.', ['customer_name', 'order_id', 'shipping_status', 'tracking_id']],
        ];

        $now = now();

        foreach ($templates as $key => [$name, $content, $variables]) {
            // Seed defaults only when the template does not already exist.
            // Never overwrite administrator-customized content during normal requests.
            if (! DB::table('india_sms_templates')->where('key', $key)->exists()) {
                DB::table('india_sms_templates')->insert([
                    'key' => $key,
                    'name' => $name,
                    'language' => 'en',
                    'content' => $content,
                    'is_active' => true,
                    'variables' => json_encode($variables, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
