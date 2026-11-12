<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$configs = Illuminate\Support\Facades\DB::table('razorpay_emi_product_configs')->get();
foreach ($configs as $c) {
    $p = Illuminate\Support\Facades\DB::table('ec_products')->where('id', $c->product_id)->first();
    $slug = Illuminate\Support\Facades\DB::table('slugs')->where('reference_type', 'Botble\Ecommerce\Models\Product')->where('reference_id', $c->product_id)->value('key');
    echo "Product ID: {$c->product_id}, Name: {$p?->name}, Status: {$c->status}, Type: {$c->emi_type}, MinAmount: {$c->min_amount}, Slug: {$slug}\n";
}
