<?php

namespace Botble\Sms;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove()
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms');
        Schema::dropIfExists('sms_translations');

        if (Schema::hasTable('ec_customers') && Schema::hasColumn('ec_customers', 'otp')) {
            Schema::table('ec_customers', function ($table): void {
                $table->dropColumn('otp');
            });
        }

        if (Schema::hasTable('ec_customers') && Schema::hasColumn('ec_customers', 'sms_otp_verified_at')) {
            Schema::table('ec_customers', function ($table): void {
                $table->dropColumn('sms_otp_verified_at');
            });
        }

        if (Schema::hasTable('ec_customers') && Schema::hasColumn('ec_customers', 'sms_otp_required_at')) {
            Schema::table('ec_customers', function ($table): void {
                $table->dropColumn('sms_otp_required_at');
            });
        }
    }
}
