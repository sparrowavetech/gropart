<?php
 
 use Illuminate\Database\Migrations\Migration;
 use Illuminate\Database\Schema\Blueprint;
 use Illuminate\Support\Facades\Schema;
 
 return new class () extends Migration {
     public function up(): void
     {
         if (Schema::hasTable('ec_products') && ! Schema::hasColumn('ec_products', 'is_cod_eligible')) {
             Schema::table('ec_products', function (Blueprint $table) {
                 $table->tinyInteger('is_cod_eligible')->default(0)->index();
             });
         }
 
         if (Schema::hasTable('ec_orders') && ! Schema::hasColumn('ec_orders', 'cod_prepayment_amount')) {
             Schema::table('ec_orders', function (Blueprint $table) {
                 $table->decimal('cod_prepayment_amount', 15)->nullable();
                 $table->decimal('cod_remaining_amount', 15)->nullable();
             });
         }
     }
 
     public function down(): void
     {
         if (Schema::hasTable('ec_products') && Schema::hasColumn('ec_products', 'is_cod_eligible')) {
             Schema::table('ec_products', function (Blueprint $table) {
                 $table->dropColumn('is_cod_eligible');
             });
         }
 
         if (Schema::hasTable('ec_orders')) {
             Schema::table('ec_orders', function (Blueprint $table) {
                 $table->dropColumn(['cod_prepayment_amount', 'cod_remaining_amount']);
             });
         }
     }
 };
