<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ws_wholesale_applications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('email');
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('tax_id', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('business_type', 100)->nullable();
            $table->string('expected_volume', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 60)->default('pending');
            $table->unsignedBigInteger('assigned_group_id')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ws_wholesale_applications');
    }
};
