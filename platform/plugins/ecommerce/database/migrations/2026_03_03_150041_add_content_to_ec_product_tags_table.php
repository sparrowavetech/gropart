<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_product_tags', function (Blueprint $table): void {
            $table->mediumText('content')->nullable()->after('description');
        });

        if (Schema::hasTable('ec_product_tags_translations')) {
            Schema::table('ec_product_tags_translations', function (Blueprint $table): void {
                $table->mediumText('content')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('ec_product_tags', function (Blueprint $table): void {
            $table->dropColumn('content');
        });

        if (Schema::hasTable('ec_product_tags_translations')) {
            Schema::table('ec_product_tags_translations', function (Blueprint $table): void {
                $table->dropColumn('content');
            });
        }
    }
};
