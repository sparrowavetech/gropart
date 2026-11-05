<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('si_order_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('seller_id')->index();
            $table->unsignedBigInteger('revenue_id')->nullable();
            $table->string('currency', 8);
            $table->unsignedTinyInteger('currency_digits')->default(2);
            $table->json('amounts');
            $table->json('issuer');
            $table->json('recipient');
            $table->json('evidence');
            $table->string('status', 32)->default('needs_review')->index();
            $table->timestamps();
        });

        Schema::create('si_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('snapshot_id')->index();
            $table->unsignedBigInteger('seller_id')->index();
            $table->string('kind', 32);
            $table->string('status', 32)->default('draft');
            $table->string('number', 64)->nullable();
            $table->unsignedBigInteger('original_document_id')->nullable();
            $table->json('lines');
            $table->json('taxes')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->unique(['kind', 'number']);
        });
    }

    public function down(): void
    {
        // Issued tax documents and fee evidence are business records; uninstall is not data erasure.
    }
};
