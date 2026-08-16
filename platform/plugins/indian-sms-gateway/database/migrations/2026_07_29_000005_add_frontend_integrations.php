<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('india_sms_otps')) {
            return;
        }

        Schema::table('india_sms_otps', function (Blueprint $table): void {
            if (! Schema::hasColumn('india_sms_otps', 'verification_token_hash')) {
                $table->char('verification_token_hash', 64)->nullable()->after('token_hash')->index();
            }

            if (! Schema::hasColumn('india_sms_otps', 'consumed_at')) {
                $table->dateTime('consumed_at')->nullable()->after('verified_at');
            }
        });

        if (! Schema::hasTable('india_sms_verified_phones')) {
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
                $table->unique(['phone_hash', 'purpose'], 'india_sms_verified_phone_purpose_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('india_sms_verified_phones');

        if (! Schema::hasTable('india_sms_otps')) {
            return;
        }

        Schema::table('india_sms_otps', function (Blueprint $table): void {
            $columns = [];

            if (Schema::hasColumn('india_sms_otps', 'verification_token_hash')) {
                $columns[] = 'verification_token_hash';
            }

            if (Schema::hasColumn('india_sms_otps', 'consumed_at')) {
                $columns[] = 'consumed_at';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
