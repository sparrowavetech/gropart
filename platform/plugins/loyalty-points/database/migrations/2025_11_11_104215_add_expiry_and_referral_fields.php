<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_customer_points_transactions') && ! Schema::hasColumn('ec_customer_points_transactions', 'expires_at')) {
            Schema::table('ec_customer_points_transactions', function (Blueprint $table): void {
                $table->timestamp('expires_at')->nullable()->after('created_at')->index();
            });
        }

        if (Schema::hasTable('ec_customers')) {
            if (! Schema::hasColumn('ec_customers', 'referral_code')) {
                Schema::table('ec_customers', function (Blueprint $table): void {
                    $table->string('referral_code', 50)->nullable()->unique()->after('remember_token');
                });
            }

            if (! Schema::hasColumn('ec_customers', 'referred_by')) {
                Schema::table('ec_customers', function (Blueprint $table): void {
                    $table->foreignId('referred_by')->nullable()->after('referral_code');
                });
            }

            if (! Schema::hasColumn('ec_customers', 'referral_reward_given')) {
                Schema::table('ec_customers', function (Blueprint $table): void {
                    $table->boolean('referral_reward_given')->default(false)->after('referred_by');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ec_customer_points_transactions') && Schema::hasColumn('ec_customer_points_transactions', 'expires_at')) {
            Schema::table('ec_customer_points_transactions', function (Blueprint $table): void {
                $table->dropColumn('expires_at');
            });
        }

        if (Schema::hasTable('ec_customers')) {
            Schema::table('ec_customers', function (Blueprint $table): void {
                if (Schema::hasColumn('ec_customers', 'referral_reward_given')) {
                    $table->dropColumn('referral_reward_given');
                }

                if (Schema::hasColumn('ec_customers', 'referred_by')) {
                    $table->dropColumn('referred_by');
                }

                if (Schema::hasColumn('ec_customers', 'referral_code')) {
                    $table->dropColumn('referral_code');
                }
            });
        }
    }
};
