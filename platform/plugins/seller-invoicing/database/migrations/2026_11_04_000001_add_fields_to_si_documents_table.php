<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('si_documents')) {
            Schema::table('si_documents', function (Blueprint $table): void {
                if (! Schema::hasColumn('si_documents', 'order_id')) {
                    $table->unsignedBigInteger('order_id')->nullable()->index()->after('seller_id');
                }
                if (! Schema::hasColumn('si_documents', 'currency')) {
                    $table->string('currency', 8)->default('INR')->after('number');
                }
                if (! Schema::hasColumn('si_documents', 'subtotal')) {
                    $table->decimal('subtotal', 15, 2)->default(0)->after('taxes');
                }
                if (! Schema::hasColumn('si_documents', 'tax_amount')) {
                    $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
                }
                if (! Schema::hasColumn('si_documents', 'grand_total')) {
                    $table->decimal('grand_total', 15, 2)->default(0)->after('tax_amount');
                }
            });
        }
    }

    public function down(): void
    {
        // Preserved for business compliance.
    }
};

