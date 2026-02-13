<?php

use Botble\ACL\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('audit_histories', 'reference_user')) {
            Schema::table('audit_histories', function (Blueprint $table): void {
                $table->renameColumn('reference_user', 'actor_id');
            });
        }

        if (! Schema::hasColumn('audit_histories', 'user_type')) {
            Schema::table('audit_histories', function (Blueprint $table): void {
                $table->string('user_type')->nullable()->after('user_id');
            });

            DB::table('audit_histories')->update([
                'user_type' => User::class,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('audit_histories', function (Blueprint $table): void {
            $table->dropColumn(['user_type']);
        });

        Schema::table('audit_histories', function (Blueprint $table): void {
            $table->renameColumn('actor_id', 'reference_user');
        });
    }
};
