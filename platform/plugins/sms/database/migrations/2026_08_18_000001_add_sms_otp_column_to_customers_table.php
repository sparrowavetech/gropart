<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach ($this->customerTables() as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'otp')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table): void {
                $table->string('otp', 10)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->customerTables() as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'otp')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table): void {
                $table->dropColumn('otp');
            });
        }
    }

    private function customerTables(): array
    {
        $tables = ['ec_customers'];

        if (class_exists('Botble\Ecommerce\Models\Customer')) {
            try {
                $tables[] = (new \Botble\Ecommerce\Models\Customer())->getTable();
            } catch (Throwable) {
            }
        }

        return array_values(array_unique(array_filter($tables)));
    }
};
