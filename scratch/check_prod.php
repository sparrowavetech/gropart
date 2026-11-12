<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \DB::table('ec_products')->where('id', 3692)->first();
if ($p) {
    echo "ID: " . $p->id . "\n";
    echo "Name: " . $p->name . "\n";
    echo "Price: " . $p->price . "\n";
    $slug = \DB::table('slugs')->where('reference_id', 3692)->where('reference_type', 'Botble\Ecommerce\Models\Product')->first();
    echo "Slug: " . ($slug ? $slug->key : 'N/A') . "\n";
    $cats = \DB::table('ec_product_category_product')->where('product_id', 3692)->pluck('category_id');
    echo "Categories: " . json_encode($cats) . "\n";
} else {
    echo "Product 3692 not found in DB\n";
}

