<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('advanced_cod_entries')) return;
        Schema::create('advanced_cod_entries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->index();
            $table->string('kind', 24);
            $table->string('source', 24);
            $table->unsignedBigInteger('amount_paise');
            $table->unsignedBigInteger('fee_paise')->default(0);
            $table->string('currency', 12);
            $table->string('reference', 120);
            $table->uuid('idempotency_key')->unique();
            $table->unsignedBigInteger('reversal_of')->nullable()->unique();
            $table->unsignedBigInteger('user_id');
            $table->text('note');
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->unique(['order_id', 'kind', 'reference'], 'advanced_cod_reference_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('advanced_cod_entries'); }
};
