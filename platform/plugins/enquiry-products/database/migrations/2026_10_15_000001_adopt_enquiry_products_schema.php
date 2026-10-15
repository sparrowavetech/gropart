<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['ec_products', 'ec_product_categories'] as $name) {
            if (! Schema::hasColumn($name, 'is_enquiry')) {
                Schema::table($name, fn (Blueprint $t) => $t->boolean('is_enquiry')->default(false)->index());
            }
            DB::table($name)->whereNull('is_enquiry')->update(['is_enquiry' => 0]);
        }
        if (! Schema::hasTable('ec_product_enquiry')) {
            Schema::create('ec_product_enquiry', function (Blueprint $t): void {
                $t->id();
                $t->unsignedBigInteger('product_id')->index();
                $t->string('name'); $t->string('email'); $t->string('phone');
                $t->unsignedBigInteger('country')->default(0);
                $t->unsignedBigInteger('state')->default(0);
                $t->unsignedBigInteger('city')->default(0);
                $t->text('address'); $t->string('zip_code')->nullable();
                $t->timestamps();
            });
        }
        $columns = [
            'status' => fn (Blueprint $t) => $t->string('status')->default('pending'),
            'description' => fn (Blueprint $t) => $t->text('description')->nullable(),
            'attachment' => fn (Blueprint $t) => $t->text('attachment')->nullable(),
            'code' => fn (Blueprint $t) => $t->string('code')->nullable(),
            'store_id' => fn (Blueprint $t) => $t->unsignedBigInteger('store_id')->nullable()->index(),
            'quantity' => fn (Blueprint $t) => $t->unsignedInteger('quantity')->default(1),
            'submission_token' => fn (Blueprint $t) => $t->string('submission_token', 64)->nullable()->unique(),
            'admin_notes' => fn (Blueprint $t) => $t->text('admin_notes')->nullable(),
        ];
        foreach ($columns as $name => $add) {
            if (! Schema::hasColumn('ec_product_enquiry', $name)) {
                Schema::table('ec_product_enquiry', $add);
            }
        }
        if (! Schema::hasTable('ep_enquiry_history')) {
            Schema::create('ep_enquiry_history', function (Blueprint $t): void {
                $t->id(); $t->unsignedBigInteger('enquiry_id')->index();
                $t->string('status'); $t->unsignedBigInteger('actor_id')->nullable();
                $t->string('actor_type'); $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Adoption is intentionally non-destructive. Legacy business records and flags survive rollback.
    }
};
