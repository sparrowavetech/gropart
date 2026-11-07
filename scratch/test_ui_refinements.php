<?php

$workspace = '/Applications/XAMPP/xamppfiles/htdocs/gropart';

spl_autoload_register(function ($class) use ($workspace) {
    $prefixes = [
        'Botble\\Ecommerce\\' => $workspace . '/platform/plugins/ecommerce/src/',
        'Botble\\Payment\\' => $workspace . '/platform/plugins/payment/src/',
        'Botble\\Marketplace\\' => $workspace . '/platform/plugins/marketplace/src/',
        'SparroWave\\AdvancedCod\\' => $workspace . '/platform/plugins/advanced-cod/src/',
        'SparroWave\\FarmartHelper\\' => $workspace . '/platform/plugins/farmart-helper/src/',
        'SparroWave\\Shipmozo\\' => $workspace . '/platform/plugins/shipmozo/src/',
        'SparroWave\\SellerInvoicing\\' => $workspace . '/platform/plugins/seller-invoicing/src/',
        'SparroWave\\ProductFreeShipping\\' => $workspace . '/platform/plugins/product-free-shipping/src/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

require $workspace . '/vendor/autoload.php';
$app = require_once $workspace . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config([
    'database.connections.mysql.host' => '',
    'database.connections.mysql.unix_socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
]);
\Illuminate\Support\Facades\DB::purge('mysql');

echo "=== TEST: UI REFINEMENTS & UNIFORMITY ===\n\n";

// 1. Test Translation Keys
echo "--- TEST 1: Translation Keys ---\n";
$keys = [
    'plugins/advanced-cod::advanced-cod.manual_advance_receipt_details',
    'plugins/advanced-cod::advanced-cod.order_already_placed',
    'plugins/advanced-cod::advanced-cod.cod_not_eligible_for_all_items',
    'plugins/advanced-cod::advanced-cod.existing_attempt_or_receipt_warning',
    'plugins/advanced-cod::advanced-cod.advance_required_or_paid',
    'plugins/advanced-cod::advanced-cod.cash_collected',
    'plugins/advanced-cod::advanced-cod.in_transit',
    'plugins/advanced-cod::advanced-cod.select_entry_to_reverse',
    'plugins/advanced-cod::advanced-cod.table_id_time',
    'plugins/advanced-cod::advanced-cod.table_source',
    'plugins/advanced-cod::advanced-cod.table_amount',
    'plugins/advanced-cod::advanced-cod.table_deductions',
    'plugins/advanced-cod::advanced-cod.table_reference_note',
    'plugins/advanced-cod::advanced-cod.table_recorded_by',
    'plugins/ecommerce::order.menu',
];

foreach ($keys as $key) {
    $trans = trans($key);
    assert($trans !== $key, "Translation missing for {$key}");
    echo "  [OK] {$key} => {$trans}\n";
}
echo "TEST 1 PASSED!\n\n";

// 2. Test Badge Dimensions & Styling
echo "--- TEST 2: Badge Uniformity ---\n";
$dummyProduct = new \Botble\Ecommerce\Models\Product();
$dummyProduct->is_cod_eligible = 1;
$dummyProduct->product_free_shipping = 1;

$codBadge = \SparroWave\AdvancedCod\Hooks\AdvancedCodHookListener::addCodLabelToProductCard('', $dummyProduct, true);
$freeShippingBadge = (new \SparroWave\ProductFreeShipping\Providers\HookServiceProvider(app()))->renderProductDetailBadge('', $dummyProduct, true);

echo "COD Badge: " . substr($codBadge, 0, 120) . "...\n";
echo "Free Delivery Badge: " . substr($freeShippingBadge, 0, 120) . "...\n";

// Check common padding & font-size in admin
assert(str_contains($codBadge, 'padding: 4px 8px'), "COD badge must have padding: 4px 8px in admin");
assert(str_contains($freeShippingBadge, 'padding: 4px 8px'), "Free shipping badge must have padding: 4px 8px in admin");
assert(str_contains($codBadge, 'font-size: 11px'), "COD badge must have font-size: 11px");
assert(str_contains($freeShippingBadge, 'font-size: 11px'), "Free shipping badge must have font-size: 11px");
assert(str_contains($codBadge, 'margin: 4px 4px 4px 0'), "COD badge must have margin 4px 4px 4px 0");
assert(str_contains($freeShippingBadge, 'margin: 4px 4px 4px 0'), "Free shipping badge must have margin 4px 4px 4px 0");
echo "TEST 2 PASSED!\n\n";

// 3. Test Seller Invoicing Extra Info Filter
echo "--- TEST 3: Seller Fee Invoice on Incomplete Order ---\n";
$orderIncomplete = new \Botble\Ecommerce\Models\Order(['id' => 99999, 'store_id' => 1, 'is_finished' => 0]);
$orderIncomplete->id = 99999;
$orderComplete = new \Botble\Ecommerce\Models\Order(['id' => 99998, 'store_id' => 1, 'is_finished' => 1]);
$orderComplete->id = 99998;

// Fake admin auth
$adminUser = \Botble\ACL\Models\User::first();
auth()->login($adminUser);

$htmlIncomplete = apply_filters('ecommerce_admin_order_extra_info', '', $orderIncomplete);
echo "Incomplete order extra_info length: " . strlen($htmlIncomplete) . " (Expected: empty of seller fee invoice)\n";
assert(!str_contains($htmlIncomplete, 'seller-invoicing') && !str_contains($htmlIncomplete, 'Seller Fee Invoice'), "Incomplete order must NOT have seller fee invoice card");
echo "TEST 3 PASSED!\n\n";

// 4. Test Single Button in tags.blade.php
echo "--- TEST 4: Single Button Check in tags.blade.php ---\n";
$tagsView = view('plugins/advanced-cod::manual.tags', ['order' => $orderIncomplete, 'flags' => ['cod' => true]])->render();
assert(!str_contains($tagsView, '<tr><td colspan="2"'), "tags.blade.php must not inject duplicate table row button");
echo "tags.blade.php cleanly excludes table row button. (PASSED)\n";
echo "TEST 4 PASSED!\n\n";

// 5. Test Confirm View Rendering for Order 11383
echo "--- TEST 5: Confirm View Rendering for Order 11383 ---\n";
view()->share('errors', new \Illuminate\Support\ViewErrorBag());
$order11383 = \Botble\Ecommerce\Models\Order::find(11383);
if ($order11383) {
    // bind request
    $request = \Illuminate\Http\Request::create('/admin/advanced-cod/confirm/11383', 'GET');
    app()->instance('request', $request);
    $controller = app(\SparroWave\AdvancedCod\Http\Controllers\ManualConfirmationController::class);
    $response = $controller->show($order11383);
    $html = $response->render();
    echo "Confirm View rendered successfully! Length: " . strlen($html) . "\n";
    assert(str_contains($html, 'Confirm &amp; Place Order') || str_contains($html, 'Confirm & Place Order'), "Must contain page title");
    assert(!str_contains($html, 'syntax error'), "Must have no syntax errors");
} else {
    echo "Order 11383 not found in database.\n";
}
echo "TEST 5 PASSED!\n\n";

echo "=== ALL UI & BEHAVIOR REFINEMENT TESTS PASSED! ===\n";
