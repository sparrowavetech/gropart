<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        foreach ($this->customerTables() as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'phone_verified_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dateTime('phone_verified_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->customerTables() as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'phone_verified_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('phone_verified_at');
            });
        }
    }

    private function customerTables(): array
    {
        $tables = ['ec_customers'];

        try {
            if (class_exists('Botble\\Ecommerce\\Models\\Customer')) {
                $model = new \Botble\Ecommerce\Models\Customer();
                $tables[] = $model->getTable();
            }
        } catch (\Throwable) {
        }

        return array_values(array_unique(array_filter($tables)));
    }
};
