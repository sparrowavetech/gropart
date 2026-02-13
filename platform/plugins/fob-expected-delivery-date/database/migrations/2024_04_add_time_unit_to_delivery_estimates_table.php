<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_delivery_estimates')) {
            return;
        }

        if (Schema::hasColumn('ec_delivery_estimates', 'time_unit')) {
            return;
        }

        Schema::table('ec_delivery_estimates', function (Blueprint $table) {
            $table->string('time_unit', 20)->default('days')->after('max_days');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ec_delivery_estimates')) {
            return;
        }

        if (! Schema::hasColumn('ec_delivery_estimates', 'time_unit')) {
            return;
        }

        Schema::table('ec_delivery_estimates', function (Blueprint $table) {
            $table->dropColumn('time_unit');
        });
    }
};
