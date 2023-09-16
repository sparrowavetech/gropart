<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('slugs', function (Blueprint $table) {
            $table->index('key');
            $table->index('prefix');
            $table->index(['reference_id', 'reference_type'], 'slugs_reference_index');
        });
    }

    public function down(): void
    {
        Schema::table('slugs', function (Blueprint $table) {
            $table->dropIndex('slugs_key_index');
            $table->dropIndex('slugs_prefix_index');
            $table->dropIndex('slugs_reference_index');
        });
    }
};
