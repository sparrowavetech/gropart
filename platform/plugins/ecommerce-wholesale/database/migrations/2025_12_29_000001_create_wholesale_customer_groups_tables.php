<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ws_customer_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('discount_type', 60)->default('percentage');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->integer('priority')->default(0);
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('ws_customer_group_assignments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('customer_group_id');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'customer_group_id'], 'unique_group_assignment');
            $table->index('customer_id');
            $table->index('customer_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ws_customer_group_assignments');
        Schema::dropIfExists('ws_customer_groups');
    }
};
