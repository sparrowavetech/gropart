<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('product_bundles')) {
            return;
        }

        Schema::table('product_bundles', function (Blueprint $table) {
            if (! Schema::hasColumn('product_bundles', 'slug')) {
                $table->string('slug', 255)->nullable()->unique();
            }

            if (! Schema::hasColumn('product_bundles', 'image')) {
                $table->string('image', 255)->nullable();
            }

            if (! Schema::hasColumn('product_bundles', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_bundles')) {
            return;
        }

        Schema::table('product_bundles', function (Blueprint $table) {
            // Drop columns if they exist. Index name for unique slug is inferred; wrap in try-catch.
            try {
                if (Schema::hasColumn('product_bundles', 'slug')) {
                    $table->dropUnique(['slug']);
                    $table->dropColumn('slug');
                }
            } catch (Throwable $e) {
                // ignore
            }

            if (Schema::hasColumn('product_bundles', 'image')) {
                $table->dropColumn('image');
            }

            if (Schema::hasColumn('product_bundles', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};
