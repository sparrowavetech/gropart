<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_customers') && ! Schema::hasColumn('ec_customers', 'sms_otp_verified_at')) {
            Schema::table('ec_customers', function (Blueprint $table): void {
                $table->timestamp('sms_otp_verified_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ec_customers') && Schema::hasColumn('ec_customers', 'sms_otp_verified_at')) {
            Schema::table('ec_customers', function (Blueprint $table): void {
                $table->dropColumn('sms_otp_verified_at');
            });
        }
    }
};
