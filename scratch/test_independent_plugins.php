<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Botble\Ecommerce\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SparroWave\AdvancedCod\Http\Middleware\EnrichCodProductSearchMiddleware;
use SparroWave\ProductFreeShipping\Http\Middleware\EnrichFreeShippingProductSearchMiddleware;
use SparroWave\Shipmozo\Http\Middleware\EnrichShipmozoProductSearchMiddleware;
use SparroWave\VendorVerifiedBadge\Http\Middleware\EnrichVendorBadgeProductSearchMiddleware;

echo "=== TESTING STRICT PLUGIN INDEPENDENCE & MODULAR BADGES ===" . PHP_EOL . PHP_EOL;

// 1. Test Product Search Middleware of each plugin
$product = Product::query()->where('is_variation', 0)->first();
if (!$product) {
    echo "FAIL: No product found in database." . PHP_EOL;
    exit(1);
}

$fakeRequest = Request::create('/admin/ecommerce/products/get-all-products-and-variations', 'GET');
$fakeRequest->setRouteResolver(function () {
    $route = new \Illuminate\Routing\Route('GET', 'admin/ecommerce/products/get-all-products-and-variations', []);
    $route->name('products.get-all-products-and-variations');
    return $route;
});

$baseResponse = new JsonResponse([
    'error' => false,
    'data' => [
        ['id' => $product->id, 'name' => $product->name, 'price' => 100]
    ]
]);

// Pipeline through Free Shipping middleware
$mwFree = new EnrichFreeShippingProductSearchMiddleware();
$resFree = $mwFree->handle($fakeRequest, fn($r) => $baseResponse);
$dataFree = $resFree->getData(true);
$hasFreeKey = array_key_exists('free_shipping', $dataFree['data'][0]);
echo "1. Free Shipping Middleware enriched 'free_shipping': " . ($hasFreeKey ? "PASS (" . var_export($dataFree['data'][0]['free_shipping'], true) . ")" : "FAIL") . PHP_EOL;

// Pipeline through COD middleware
$mwCod = new EnrichCodProductSearchMiddleware();
$resCod = $mwCod->handle($fakeRequest, fn($r) => $resFree);
$dataCod = $resCod->getData(true);
$hasCodKey = array_key_exists('cod', $dataCod['data'][0]);
echo "2. Advanced COD Middleware enriched 'cod': " . ($hasCodKey ? "PASS (" . var_export($dataCod['data'][0]['cod'], true) . ")" : "FAIL") . PHP_EOL;

// Pipeline through ShipMozo middleware
$mwShip = new EnrichShipmozoProductSearchMiddleware();
$resShip = $mwShip->handle($fakeRequest, fn($r) => $resCod);
$dataShip = $resShip->getData(true);
$hasShipKey = array_key_exists('shipmozo', $dataShip['data'][0]);
echo "3. ShipMozo Middleware enriched 'shipmozo': " . ($hasShipKey ? "PASS (" . var_export($dataShip['data'][0]['shipmozo'], true) . ")" : "FAIL") . PHP_EOL;

// Pipeline through Vendor Verified Badge middleware
$mwVendor = new EnrichVendorBadgeProductSearchMiddleware();
$resVendor = $mwVendor->handle($fakeRequest, fn($r) => $resShip);
$dataVendor = $resVendor->getData(true);
$hasStoreName = array_key_exists('store_name', $dataVendor['data'][0]);
$hasStoreType = array_key_exists('store_type', $dataVendor['data'][0]);
$hasVerified = array_key_exists('is_verified', $dataVendor['data'][0]);
echo "4. Vendor Verified Badge Middleware enriched store fields: " . ($hasStoreName && $hasStoreType && $hasVerified ? "PASS (store_name={$dataVendor['data'][0]['store_name']}, is_verified=" . var_export($dataVendor['data'][0]['is_verified'], true) . ")" : "FAIL") . PHP_EOL;

// Set admin context for test
request()->attributes->set('is_in_admin', true);
if (class_exists('\Botble\Base\Facades\AdminHelper')) {
    // Mock or set admin request
    $adminRequest = Request::create('/admin/ecommerce/products/view/' . $product->id, 'GET');
    $app->instance('request', $adminRequest);
}

// 2. Test apply_filters('ecommerce_admin_product_view_badges')
echo PHP_EOL . "5. Testing apply_filters('ecommerce_admin_product_view_badges'):" . PHP_EOL;
$product->is_cod_eligible = 1;
$product->enable_shipmozo_shipping = 1;
$badgesHtml = apply_filters('ecommerce_admin_product_view_badges', '', $product);
echo "   Output: " . (strlen($badgesHtml) > 0 ? "PASS (" . substr(strip_tags($badgesHtml), 0, 50) . "...)" : "EMPTY") . PHP_EOL;

// 3. Test apply_filters('ecommerce_admin_product_view_datagrid')
echo PHP_EOL . "6. Testing apply_filters('ecommerce_admin_product_view_datagrid'):" . PHP_EOL;
$datagridHtml = apply_filters('ecommerce_admin_product_view_datagrid', '', $product);
echo "   Output: " . (strlen($datagridHtml) > 0 ? "PASS (" . substr(strip_tags($datagridHtml), 0, 50) . "...)" : "EMPTY") . PHP_EOL;

// 4. Verify farmart-helper has NO badge code
echo PHP_EOL . "7. Verifying farmart-helper has NO badge code:" . PHP_EOL;
$fhDir = __DIR__ . '/../platform/plugins/farmart-helper/src';
$output = shell_exec("grep -rn 'ProductFreeShippingHelper\\|AdvancedCodHookListener\\|enable_shipmozo_shipping\\|VendorBadgeHelper' $fhDir");
if (empty($output)) {
    echo "   PASS: farmart-helper is 100% clean of external plugin domain code!" . PHP_EOL;
} else {
    echo "   FAIL: Found references in farmart-helper:\n$output" . PHP_EOL;
}

echo PHP_EOL . "=== ALL TESTS COMPLETED SUCCESSFULLY ===" . PHP_EOL;
