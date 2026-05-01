<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('loyalty_levels')) {
            Schema::create('loyalty_levels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('min_points')->default(0);
                $table->integer('max_points')->nullable(); // Optional cap
                $table->decimal('earning_rate', 8, 2)->default(1.00); // 1.0 = 100%, 1.1 = 110%
                $table->text('benefits')->nullable(); // Store arbitrary benefits
                $table->boolean('is_default')->default(false);
                $table->string('status')->default('published');
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('ec_customer_points_balances', 'level_id')) {
            Schema::table('ec_customer_points_balances', function (Blueprint $table) {
                $table->foreignId('level_id')->nullable();
                $table->timestamp('level_updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ec_customer_points_balances', 'level_id')) {
            Schema::table('ec_customer_points_balances', function (Blueprint $table) {
                $table->dropColumn(['level_id', 'level_updated_at']);
            });
        }

        Schema::dropIfExists('loyalty_levels');
    }
};
