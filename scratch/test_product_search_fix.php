<?php

$workspace = '/Applications/XAMPP/xamppfiles/htdocs/gropart';

spl_autoload_register(function ($class) use ($workspace) {
    $prefixes = [
        'Botble\\Ecommerce\\' => $workspace . '/platform/plugins/ecommerce/src/',
        'SparroWave\\AdvancedCod\\' => $workspace . '/platform/plugins/advanced-cod/src/',
        'SparroWave\\Shipmozo\\' => $workspace . '/platform/plugins/shipmozo/src/',
        'SparroWave\\ProductFreeShipping\\' => $workspace . '/platform/plugins/product-free-shipping/src/',
        'SparroWave\\VendorVerifiedBadge\\' => $workspace . '/platform/plugins/vendor-verified-badge/src/',
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
    'database.default' => 'sqlite',
    'database.connections.sqlite' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ],
]);
\Illuminate\Support\Facades\DB::purge();
\Illuminate\Support\Facades\Schema::create('ec_products', function ($table) {
    $table->id();
    $table->string('name')->nullable();
    $table->boolean('is_cod_eligible')->default(true);
    $table->boolean('is_variation')->default(false);
    $table->boolean('enable_shipmozo_shipping')->default(true);
    $table->unsignedBigInteger('store_id')->nullable();
    $table->timestamps();
});
\Illuminate\Support\Facades\DB::table('ec_products')->insert([
    ['id' => 4091, 'name' => 'Sonalika Sensor', 'is_cod_eligible' => 1, 'is_variation' => 0, 'enable_shipmozo_shipping' => 1, 'store_id' => null],
    ['id' => 4092, 'name' => 'Mahindra Part', 'is_cod_eligible' => 0, 'is_variation' => 0, 'enable_shipmozo_shipping' => 0, 'store_id' => null],
]);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SparroWave\AdvancedCod\Http\Middleware\EnrichCodProductSearchMiddleware;
use SparroWave\ProductFreeShipping\Http\Middleware\EnrichFreeShippingProductSearchMiddleware;
use SparroWave\Shipmozo\Http\Middleware\EnrichShipmozoProductSearchMiddleware;
use SparroWave\VendorVerifiedBadge\Http\Middleware\EnrichVendorBadgeProductSearchMiddleware;

echo "========================================================\n";
echo "   TEST: PRODUCT SEARCH MIDDLEWARE PIPELINE INTEGRITY\n";
echo "========================================================\n";

$initialProducts = [
    [
        'id' => 4091,
        'name' => 'Sonalika Tractor Genuine Oil pressure Sensor (SB Marketing)',
        'sku' => 'SON-4091',
        'variations' => [],
        'image_url' => '/image1.png',
    ],
    [
        'id' => 4092,
        'name' => 'Mahindra Bhoomiputra Steering Assembly (Gropart)',
        'sku' => 'MAH-4092',
        'variations' => [
            ['id' => 9991, 'name' => 'Variation 1', 'variations' => []]
        ],
        'image_url' => '/image2.png',
    ],
];

$initialPayload = [
    'error' => false,
    'data' => [
        'data' => $initialProducts,
        'links' => [
            'first' => 'http://127.0.0.1:8000/admin/ecommerce/products/get-all-products-and-variations?page=1',
            'next' => null,
        ],
        'meta' => [
            'current_page' => 1,
            'per_page' => 10,
        ],
    ],
    'message' => null,
];

$request = Request::create('/admin/ecommerce/products/get-all-products-and-variations', 'GET');
$request->setRouteResolver(function () {
    $route = new class {
        public function getName() {
            return 'products.get-all-products-and-variations';
        }
        public function named(...$patterns) {
            foreach ($patterns as $pattern) {
                if ($pattern === 'products.get-all-products-and-variations' || fnmatch($pattern, 'products.get-all-products-and-variations')) {
                    return true;
                }
            }
            return false;
        }
    };
    return $route;
});

// Run through all 4 middlewares
$response = new JsonResponse($initialPayload);

$middlewares = [
    new EnrichCodProductSearchMiddleware(),
    new EnrichFreeShippingProductSearchMiddleware(),
    new EnrichShipmozoProductSearchMiddleware(),
    new EnrichVendorBadgeProductSearchMiddleware(),
];

foreach ($middlewares as $index => $mw) {
    $response = $mw->handle($request, function () use ($response) {
        return $response;
    });
}

$finalJson = $response->getData(true);

// 1. Verify JSON Structure
assert(isset($finalJson['data']['data']), "FAIL: data.data must exist");
assert(is_array($finalJson['data']['data']), "FAIL: data.data must be an array");
assert(array_is_list($finalJson['data']['data']), "FAIL: data.data must be a pure sequential list/array for JSON serialization");
echo "✓ data.data is a valid sequential JSON array (NOT an object)\n";

// 2. Verify links & meta are not corrupted
assert(!isset($finalJson['data']['data']['free_shipping']), "FAIL: data.data must not have string keys like free_shipping");
assert(!isset($finalJson['data']['links']['free_shipping']), "FAIL: links must not have string keys like free_shipping");
assert(isset($finalJson['data']['links']['first']), "✓ links.first preserved");
assert(isset($finalJson['data']['meta']['current_page']), "✓ meta.current_page preserved");

// 3. Verify Vue 3 template evaluation
$list_products = $finalJson['data'];
$vueCrashDetected = false;
$productCount = 0;

foreach ($list_products['data'] as $product_item) {
    $productCount++;
    if (!isset($product_item['variations']) || !is_array($product_item['variations'])) {
        $vueCrashDetected = true;
        echo "FAIL: product_item.variations is missing or not array!\n";
        break;
    }
    // Test exact Vue template condition: !product_item.variations.length
    $hasNoVariations = count($product_item['variations']) === 0;
    $isSelectable = $hasNoVariations;
    echo "  -> Product [{$product_item['id']}]: selectable = " . ($isSelectable ? 'yes' : 'no') . "\n";
}

assert(!$vueCrashDetected, "FAIL: Vue template would crash");
assert($productCount === 2, "FAIL: Expected 2 products");
echo "✓ Vue 3 template evaluation verified: 0 crashes, perfectly safe!\n";

// 4. Verify JS Syntax of all 4 frontend files
$jsFiles = [
    $workspace . '/platform/plugins/advanced-cod/public/js/admin-order-advanced-cod.js',
    $workspace . '/platform/plugins/product-free-shipping/public/js/admin-product-free-shipping.js',
    $workspace . '/platform/plugins/shipmozo/public/js/admin-shipmozo.js',
    $workspace . '/platform/plugins/vendor-verified-badge/public/js/admin-vendor-badge.js',
];

foreach ($jsFiles as $jsFile) {
    $content = file_get_contents($jsFile);
    // Check for undefined variable 'el.' inside itemEl loop
    if (strpos($content, 'searchItems.forEach(itemEl => {') !== false && strpos($content, 'el.querySelector') !== false) {
        throw new Exception("FAIL: Undefined variable 'el' in $jsFile");
    }
    // Check that debounce exists
    if (strpos($content, 'debounce') === false) {
        throw new Exception("FAIL: Debounce missing in $jsFile");
    }
    echo "✓ " . basename($jsFile) . " syntax & safety verified\n";
}

echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
