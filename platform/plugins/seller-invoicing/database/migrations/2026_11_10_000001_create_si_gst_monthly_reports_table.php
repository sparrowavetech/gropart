<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('si_gst_monthly_reports')) {
            return;
        }

        Schema::create('si_gst_monthly_reports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('seller_id')->index();
            $table->unsignedBigInteger('store_id')->index();
            $table->string('tax_period', 7)->index(); // Format: YYYY-MM (e.g. 2026-10)
            $table->string('financial_year', 7); // Format: 2026-27
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('order_count')->default(0);
            $table->unsignedInteger('b2b_count')->default(0);
            $table->decimal('b2b_taxable', 15, 2)->default(0);
            $table->decimal('b2b_tax', 15, 2)->default(0);
            $table->unsignedInteger('b2cs_count')->default(0);
            $table->decimal('b2cs_taxable', 15, 2)->default(0);
            $table->decimal('b2cs_tax', 15, 2)->default(0);
            $table->decimal('total_taxable_sales', 15, 2)->default(0);
            $table->decimal('total_output_tax', 15, 2)->default(0);
            $table->decimal('itc_gropart_fees', 15, 2)->default(0);
            $table->decimal('net_gst_payable', 15, 2)->default(0);
            $table->json('hsn_summary')->nullable();
            $table->json('gstr1_payload')->nullable();
            $table->json('gstr3b_payload')->nullable();
            $table->string('status', 20)->default('ready');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'tax_period'], 'uk_store_period');
        });
    }

    public function down(): void
    {
        // Reports are historical tax filing records; do not drop accidentally.
    }
};

