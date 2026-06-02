<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Self-healing migration: guarantees the customer_id column exists on
 * ec_order_loyalty_points.
 *
 * The original add-column migration (2025_12_10_170000) is guarded with
 * Schema::hasTable(). On installs where the create-table migration ran in the
 * same batch but ordered AFTER the add-column migration, or where the table did
 * not yet exist when the add-column migration was first recorded, the guard
 * short-circuited and Laravel still marked that migration as run - leaving the
 * column permanently missing. Since v1.0.9 the pre-payment redemption-intent
 * hook is the first code path to INSERT customer_id during checkout, so the
 * missing column now throws a 500 (SQLSTATE 42S22). This fresh-timestamp
 * migration re-checks and adds the column if it is still absent.
 */
return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_order_loyalty_points')) {
            return;
        }

        if (! Schema::hasColumn('ec_order_loyalty_points', 'customer_id')) {
            Schema::table('ec_order_loyalty_points', function (Blueprint $table): void {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('order_id')
                    ->comment('Customer ID for guest orders with member ID');

                $table->index('customer_id');
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty: dropping customer_id is handled by the
        // original 2025_12_10_170000 migration's down() method.
    }
};
