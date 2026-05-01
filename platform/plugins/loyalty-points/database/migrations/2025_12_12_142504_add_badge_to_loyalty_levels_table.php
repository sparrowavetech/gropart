<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('loyalty_levels', 'badge')) {
            Schema::table('loyalty_levels', function (Blueprint $table) {
                $table->string('badge', 255)->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('loyalty_levels', 'badge')) {
            Schema::table('loyalty_levels', function (Blueprint $table) {
                $table->dropColumn('badge');
            });
        }
    }
};
