<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Botble\Ecommerce\Models\Product;

$product = Product::find(3692) ?? Product::first();
$html = view('plugins/razorpay-easy-emi::admin.product-metabox', ['model' => $product])->render();

echo "1. Has scheme_filter_indicator? " . (str_contains($html, 'scheme_filter_indicator') ? "YES (Error)" : "NO (Correct - Removed)") . "\n";
echo "2. Has SVG tag icon? " . (str_contains($html, 'icon-tabler-tag') ? "YES (Correct - Tabler SVG tag rendered)" : "NO (Error)") . "\n";
echo "3. Has SVG discount icon in header? " . (str_contains($html, 'icon-tabler-discount-2') ? "YES (Correct - Tabler SVG discount rendered)" : "NO (Error)") . "\n";
echo "4. Has Select All Available Offers checkbox? " . (str_contains($html, 'id="master_checkbox_all"') ? "YES (Correct)" : "NO (Error)") . "\n";
echo "5. Has Category Nav Tabs? " . (str_contains($html, 'rzp-category-tabs') ? "YES (Correct)" : "NO (Error)") . "\n";

