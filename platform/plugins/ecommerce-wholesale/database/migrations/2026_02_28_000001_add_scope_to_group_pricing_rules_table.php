<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ws_group_pricing_rules', function (Blueprint $table): void {
            $table->string('scope', 60)->default('product')->after('id');
            $table->unsignedBigInteger('category_id')->nullable()->after('product_id');
        });

        Schema::table('ws_group_pricing_rules', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });

        Schema::table('ws_group_pricing_rules', function (Blueprint $table): void {
            $table->index('scope');
            $table->index(['scope', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ws_group_pricing_rules', function (Blueprint $table): void {
            $table->dropIndex(['scope']);
            $table->dropIndex(['scope', 'category_id']);
        });

        Schema::table('ws_group_pricing_rules', function (Blueprint $table): void {
            $table->dropColumn(['scope', 'category_id']);
        });

        Schema::table('ws_group_pricing_rules', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
