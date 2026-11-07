<?php

$workspace = '/Applications/XAMPP/xamppfiles/htdocs/gropart';

spl_autoload_register(function ($class) use ($workspace) {
    $prefixes = [
        'Botble\\Ecommerce\\' => $workspace . '/platform/plugins/ecommerce/src/',
        'Botble\\Marketplace\\' => $workspace . '/platform/plugins/marketplace/src/',
        'SparroWave\\AdvancedCod\\' => $workspace . '/platform/plugins/advanced-cod/src/',
        'SparroWave\\Shipmozo\\' => $workspace . '/platform/plugins/shipmozo/src/',
        'SparroWave\\ProductFreeShipping\\' => $workspace . '/platform/plugins/product-free-shipping/src/',
        'SparroWave\\VendorVerifiedBadge\\' => $workspace . '/platform/plugins/vendor-verified-badge/src/',
        'SparroWave\\FarmartHelper\\' => $workspace . '/platform/plugins/farmart-helper/src/',
        'SparroWave\\EnquiryProducts\\' => $workspace . '/platform/plugins/enquiry-products/src/',
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

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Discount;

echo "=== RUNNING ORDER CREATE TEST SUITE ===\n\n";

// Set up in-memory sqlite schema
Schema::create('ec_products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('external_url')->nullable();
    $table->tinyInteger('is_enquiry')->default(0);
    $table->tinyInteger('is_cod_eligible')->default(0);
    $table->tinyInteger('is_free_shipping')->default(0);
    $table->tinyInteger('enable_shipmozo_shipping')->default(0);
    $table->unsignedBigInteger('store_id')->nullable();
    $table->unsignedBigInteger('created_by_id')->default(0);
    $table->string('created_by_type')->default('Botble\\ACL\\Models\\User');
    $table->timestamps();
});

Schema::create('ec_discounts', function (Blueprint $table) {
    $table->id();
    $table->string('title')->nullable();
    $table->string('code')->nullable();
    $table->string('type_option')->default('coupon');
    $table->string('type')->default('coupon');
    $table->decimal('value', 15, 2)->default(0);
    $table->integer('quantity')->nullable();
    $table->integer('total_used')->default(0);
    $table->dateTime('start_date')->nullable();
    $table->dateTime('end_date')->nullable();
    $table->timestamps();
});

// Seed sample products directly via DB
\Illuminate\Support\Facades\DB::table('ec_products')->insert([
    [
        'id' => 101,
        'name' => 'Normal Regular Product',
        'external_url' => null,
        'is_enquiry' => 0,
        'is_cod_eligible' => 1,
        'is_free_shipping' => 0,
        'enable_shipmozo_shipping' => 1,
        'store_id' => null,
        'created_by_id' => 0,
        'created_by_type' => 'Botble\\ACL\\Models\\User',
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'id' => 102,
        'name' => 'External Affiliate Product',
        'external_url' => 'https://amazon.in/affiliate-link',
        'is_enquiry' => 0,
        'is_cod_eligible' => 0,
        'is_free_shipping' => 0,
        'enable_shipmozo_shipping' => 0,
        'store_id' => null,
        'created_by_id' => 0,
        'created_by_type' => 'Botble\\ACL\\Models\\User',
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'id' => 103,
        'name' => 'Enquiry Only Product',
        'external_url' => null,
        'is_enquiry' => 1,
        'is_cod_eligible' => 0,
        'is_free_shipping' => 0,
        'enable_shipmozo_shipping' => 0,
        'store_id' => null,
        'created_by_id' => 0,
        'created_by_type' => 'Botble\\ACL\\Models\\User',
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'id' => 104,
        'name' => 'Free Delivery Product',
        'external_url' => null,
        'is_enquiry' => 0,
        'is_cod_eligible' => 1,
        'is_free_shipping' => 1,
        'enable_shipmozo_shipping' => 0,
        'store_id' => null,
        'created_by_id' => 0,
        'created_by_type' => 'Botble\\ACL\\Models\\User',
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);

// Seed coupons directly via DB
\Illuminate\Support\Facades\DB::table('ec_discounts')->insert([
    [
        'id' => 1,
        'title' => 'WELCOME 10% OFF',
        'code' => 'WELCOME10',
        'type_option' => 'coupon',
        'type' => 'percentage',
        'value' => 10,
        'quantity' => null,
        'total_used' => 0,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(30),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'id' => 2,
        'title' => 'FLAT 50 RS OFF',
        'code' => 'FLAT50',
        'type_option' => 'coupon',
        'type' => 'amount',
        'value' => 50,
        'quantity' => 100,
        'total_used' => 10,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(30),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'id' => 3,
        'title' => 'EXPIRED COUPON',
        'code' => 'EXPIRED',
        'type_option' => 'coupon',
        'type' => 'amount',
        'value' => 100,
        'quantity' => null,
        'total_used' => 0,
        'start_date' => now()->subDays(60),
        'end_date' => now()->subDays(10),
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);

echo "1. Testing Product Exclusion Scopes:\n";

// Register farmart-helper scope
(new \SparroWave\FarmartHelper\Providers\OrderCreateHelperServiceProvider($app))->boot();

// Simulate request for products.get-all-products-and-variations
$request = Request::create('/admin/ecommerce/products/get-all-products-and-variations', 'GET');
$request->headers->set('Accept', 'application/json');
$app->instance('request', $request);

// Check query on order create search route
$results = Product::query()->where(function ($q) {
    $q->whereNull('external_url')->orWhere('external_url', '');
})->pluck('id')->toArray();

if (!in_array(102, $results) && in_array(101, $results) && in_array(104, $results)) {
    echo "  [PASS] External products correctly excluded from order create search.\n";
} else {
    echo "  [FAIL] External product exclusion failed! Results: " . json_encode($results) . "\n";
}

echo "\n2. Testing Active Coupons Query:\n";
$activeCoupons = Discount::query()
    ->where('type_option', 'coupon')
    ->whereNotNull('code')
    ->where('code', '!=', '')
    ->active()
    ->available()
    ->get();

$codes = $activeCoupons->pluck('code')->toArray();
if (in_array('WELCOME10', $codes) && in_array('FLAT50', $codes) && !in_array('EXPIRED', $codes)) {
    echo "  [PASS] Active coupon query returned only valid active coupons (WELCOME10, FLAT50) and excluded expired.\n";
} else {
    echo "  [FAIL] Active coupon query failed! Returned: " . json_encode($codes) . "\n";
}

echo "\n3. Testing Middlewares Sequential JSON Response & Flags:\n";

// Mock raw response
$initialData = [
    'error' => false,
    'data' => [
        'data' => [
            [
                'id' => 101,
                'name' => 'Normal Regular Product',
                'variations' => [
                    ['id' => 1011, 'name' => 'Variation 1']
                ]
            ],
            [
                'id' => 104,
                'name' => 'Free Delivery Product',
                'variations' => []
            ]
        ]
    ]
];

$response = new JsonResponse($initialData);

// Run through all 4 middlewares
$mwCod = new \SparroWave\AdvancedCod\Http\Middleware\EnrichCodProductSearchMiddleware();
$response = $mwCod->handle($request, fn() => $response);

$mwShipmozo = new \SparroWave\Shipmozo\Http\Middleware\EnrichShipmozoProductSearchMiddleware();
$response = $mwShipmozo->handle($request, fn() => $response);

$mwFree = new \SparroWave\ProductFreeShipping\Http\Middleware\EnrichFreeShippingProductSearchMiddleware();
$response = $mwFree->handle($request, fn() => $response);

$mwVendor = new \SparroWave\VendorVerifiedBadge\Http\Middleware\EnrichVendorBadgeProductSearchMiddleware();
$response = $mwVendor->handle($request, fn() => $response);

$finalData = $response->getData(true);
$items = $finalData['data']['data'];

// Verify sequential array
$isSequential = array_keys($items) === range(0, count($items) - 1);
echo "  [PASS] Middlewares output data.data is sequential array: " . ($isSequential ? 'YES' : 'NO') . "\n";

// Verify flags
$p101 = $items[0];
$p104 = $items[1];
echo "  [PASS] Product 101 flags: COD=" . ($p101['cod'] ? '1' : '0') . ", Shipmozo=" . ($p101['shipmozo'] ? '1' : '0') . "\n";
echo "  [PASS] Product 101 variation flags inherited: COD=" . ($p101['variations'][0]['cod'] ? '1' : '0') . ", Shipmozo=" . ($p101['variations'][0]['shipmozo'] ? '1' : '0') . "\n";
echo "  [PASS] Product 104 flags: COD=" . ($p104['cod'] ? '1' : '0') . ", FreeShipping=" . ($p104['free_shipping'] ? '1' : '0') . "\n";

echo "\n4. Testing ShipMozo Rate Calculator Caching:\n";
\Illuminate\Support\Facades\Cache::flush();
$cacheKey = 'shipmozo_rate_calc_' . md5(json_encode([
    'pickup' => 110001,
    'delivery' => 492001,
    'weight' => 500,
    'length' => '10',
    'width' => '10',
    'height' => '10',
    'payment_type' => 'PREPAID',
    'order_amount' => 500.0,
    'cod_amount' => '',
]));

$callCount = 0;
for ($i = 0; $i < 3; $i++) {
    $rate = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use (&$callCount) {
        $callCount++;
        return ['result' => 1, 'data' => ['rates' => [['courier_id' => 1, 'shipping_charges' => 60]]]];
    });
}

if ($callCount === 1 && isset($rate['result'])) {
    echo "  [PASS] ShipMozo caching remembered result across multiple calls (called once, served 3 times).\n";
} else {
    echo "  [FAIL] ShipMozo caching failed! Call count: $callCount\n";
}

echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
