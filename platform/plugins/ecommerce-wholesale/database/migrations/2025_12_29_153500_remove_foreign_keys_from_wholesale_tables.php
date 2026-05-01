<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $this->dropForeignKeysIfExist('ws_customer_group_assignments', [
            'ws_customer_group_assignments_customer_id_foreign',
            'ws_customer_group_assignments_customer_group_id_foreign',
        ]);

        $this->dropForeignKeysIfExist('ws_group_pricing_rules', [
            'ws_group_pricing_rules_product_id_foreign',
            'ws_group_pricing_rules_customer_group_id_foreign',
            'ws_group_pricing_rules_store_id_foreign',
        ]);

        $this->dropForeignKeysIfExist('ws_product_moq', [
            'ws_product_moq_product_id_foreign',
            'ws_product_moq_customer_group_id_foreign',
        ]);

        $this->dropForeignKeysIfExist('ws_wholesale_applications', [
            'ws_wholesale_applications_customer_id_foreign',
            'ws_wholesale_applications_assigned_group_id_foreign',
            'ws_wholesale_applications_reviewed_by_foreign',
        ]);

        $this->dropForeignKeysIfExist('ws_product_visibility', [
            'ws_product_visibility_product_id_foreign',
        ]);

        $this->dropForeignKeysIfExist('ws_product_group_access', [
            'ws_product_group_access_product_id_foreign',
            'ws_product_group_access_customer_group_id_foreign',
        ]);
    }

    public function down(): void
    {
        // Foreign keys are intentionally not restored
    }

    protected function dropForeignKeysIfExist(string $table, array $foreignKeys): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existingForeignKeys = $this->getExistingForeignKeys($table);

        Schema::table($table, function (Blueprint $blueprint) use ($foreignKeys, $existingForeignKeys): void {
            foreach ($foreignKeys as $foreignKey) {
                if (in_array($foreignKey, $existingForeignKeys)) {
                    $blueprint->dropForeign($foreignKey);
                }
            }
        });
    }

    protected function getExistingForeignKeys(string $table): array
    {
        $database = config('database.connections.mysql.database');

        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$database, $table]);

        return array_map(fn ($fk) => $fk->CONSTRAINT_NAME, $foreignKeys);
    }
};
