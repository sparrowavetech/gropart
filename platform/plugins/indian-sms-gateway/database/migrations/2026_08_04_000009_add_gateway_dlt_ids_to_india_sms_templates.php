<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
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

    public function down(): void
    {
        if (Schema::hasTable('india_sms_templates')) {
            Schema::table('india_sms_templates', function (Blueprint $table): void {
                foreach (['gateway_sender_ids', 'gateway_template_ids'] as $column) {
                    if (Schema::hasColumn('india_sms_templates', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
