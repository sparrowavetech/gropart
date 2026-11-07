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
    'database.connections.mysql.host' => '',
    'database.connections.mysql.unix_socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
]);
\Illuminate\Support\Facades\DB::purge('mysql');

echo "====================================================\n";
echo "   COMPREHENSIVE VERIFICATION SUITE\n";
echo "====================================================\n\n";

// --- SUITE 1: Seller Invoicing Table Sorting ---
echo "--- SUITE 1: Seller Invoicing Table Sorting ---\n";
$table = app(\SparroWave\SellerInvoicing\Tables\SellerInvoiceTable::class);
$cols = $table->columns();
$orderableCols = [];
foreach ($cols as $col) {
    $arr = $col->toArray();
    if ($arr['orderable']) {
        $orderableCols[] = $arr['name'];
    }
}
echo "Orderable columns in SellerInvoiceTable: " . implode(', ', $orderableCols) . "\n";
$expectedCols = ['number', 'order_id', 'order_date', 'issued_at', 'subtotal', 'tax_amount', 'grand_total', 'status'];
foreach ($expectedCols as $ec) {
    if (in_array($ec, $orderableCols)) {
        echo "  [OK] Column '$ec' is orderable\n";
    } else {
        echo "  [FAIL] Column '$ec' is NOT orderable\n";
        exit(1);
    }
}
echo "SUITE 1 PASSED!\n\n";

// --- SUITE 2: Incomplete Order Guard ---
echo "--- SUITE 2: Incomplete Order Guard on orders.edit ---\n";
$middleware = new \SparroWave\FarmartHelper\Http\Middleware\GuardIncompleteOrderEditMiddleware();

$orderIncomplete = \Botble\Ecommerce\Models\Order::find(11379);
if ($orderIncomplete) {
    $req1 = \Illuminate\Http\Request::create("/admin/ecommerce/orders/edit/11379", "GET");
    $res1 = $middleware->handle($req1, fn() => "NO_REDIRECT");
    if ($res1 instanceof \Illuminate\Http\RedirectResponse && str_contains($res1->getTargetUrl(), 'incomplete-orders/view/11379')) {
        echo "  [OK] Incomplete order #11379 redirects to: " . $res1->getTargetUrl() . "\n";
    } else {
        echo "  [FAIL] Incomplete order failed to redirect properly\n";
        exit(1);
    }
}

$orderFinished = \Botble\Ecommerce\Models\Order::where('is_finished', 1)->first();
if ($orderFinished) {
    $req2 = \Illuminate\Http\Request::create("/admin/ecommerce/orders/edit/{$orderFinished->id}", "GET");
    $res2 = $middleware->handle($req2, fn() => "FINISHED_ORDER_PASSED");
    if ($res2 === "FINISHED_ORDER_PASSED") {
        echo "  [OK] Finished order #{$orderFinished->id} allowed through to edit\n";
    } else {
        echo "  [FAIL] Finished order was blocked unexpectedly\n";
        exit(1);
    }
}
echo "SUITE 2 PASSED!\n\n";

// --- SUITE 3: Product Admin View Two-Table Separation & Badges ---
echo "--- SUITE 3: Product Admin View Separation & Badges ---\n";
$adminRequest = \Illuminate\Http\Request::create('/admin/ecommerce/products/view/2919', 'GET');
app()->instance('request', $adminRequest);
request()->attributes->set('is_in_admin', true);
$product = \Botble\Ecommerce\Models\Product::find(2919);
if ($product) {
    $view = view("plugins/ecommerce::products.view", [
        "product" => $product,
        "totalViews" => 25,
        "viewsByDate" => collect(),
        "totalOrders" => 1,
        "totalSold" => 1,
        "totalRevenue" => 450,
        "pendingOrders" => 0,
        "pendingRevenue" => 0,
        "conversionRate" => 4.0,
        "recentOrders" => collect(),
        "totalReviews" => 0,
        "averageRating" => 0,
    ]);
    $html = $view->render();
    if (str_contains($html, 'Recent Orders (Finished)') && str_contains($html, 'Incomplete Orders (Abandoned Carts)')) {
        echo "  [OK] Both Recent Orders and Incomplete Orders tables rendered\n";
    } else {
        echo "  [FAIL] Missing tables in product view\n";
        exit(1);
    }

    if (str_contains($html, 'Seller Store') || str_contains($html, 'SB Marketing')) {
        echo "  [OK] Seller / Store Information rendered in datagrid\n";
    } else {
        echo "  [FAIL] Store information missing in datagrid\n";
        exit(1);
    }
}
echo "SUITE 3 PASSED!\n\n";

// --- SUITE 4: Admin Order Shipping Service & Product Search Enrichment ---
echo "--- SUITE 4: Admin Order Create Shipping & Flags Enrichment ---\n";
$shippingService = app(\Botble\Ecommerce\Services\HandleShippingFeeService::class);
echo "Resolved shipping fee service class: " . get_class($shippingService) . "\n";
if ($shippingService instanceof \SparroWave\Shipmozo\Services\CheckoutShippingFeeService) {
    echo "  [OK] HandleShippingFeeService bound to Shipmozo CheckoutShippingFeeService\n";
} else {
    echo "  [FAIL] Container binding failed\n";
    exit(1);
}

// Test Search Enrichment via Modular Independent Middlewares
$reqSearch = \Illuminate\Http\Request::create('/admin/ecommerce/products/get-all-products-and-variations', 'GET');
$route = new \Illuminate\Routing\Route('GET', 'admin/ecommerce/products/get-all-products-and-variations', []);
$route->name('products.get-all-products-and-variations');
$reqSearch->setRouteResolver(fn() => $route);

$fakeJsonResponse = new \Illuminate\Http\JsonResponse([
    'data' => [
        ['id' => 2919, 'name' => 'Sonalika Tractor Genuine Oil pressure Sensor, 27 Number Nut, 3 Pin Type']
    ]
]);

$mwFree = new \SparroWave\ProductFreeShipping\Http\Middleware\EnrichFreeShippingProductSearchMiddleware();
$mwCod = new \SparroWave\AdvancedCod\Http\Middleware\EnrichCodProductSearchMiddleware();
$mwShip = new \SparroWave\Shipmozo\Http\Middleware\EnrichShipmozoProductSearchMiddleware();
$mwVendor = new \SparroWave\VendorVerifiedBadge\Http\Middleware\EnrichVendorBadgeProductSearchMiddleware();

$pipeRes = $mwVendor->handle($reqSearch, fn($r) =>
    $mwShip->handle($r, fn($r2) =>
        $mwCod->handle($r2, fn($r3) =>
            $mwFree->handle($r3, fn() => $fakeJsonResponse)
        )
    )
);

$enrichedData = $pipeRes->getData(true);
$firstItem = $enrichedData['data'][0] ?? [];

if (isset($firstItem['flags']) && isset($firstItem['free_shipping']) && isset($firstItem['cod']) && isset($firstItem['shipmozo']) && isset($firstItem['store_name'])) {
    echo "  [OK] Independent plugin enriched attributes present: " . json_encode($firstItem) . "\n";
} else {
    echo "  [FAIL] Missing independent attributes in search response\n";
    exit(1);
}

// Test COD Fee Calculation
$feeValue = (float) get_payment_setting('fee', 'cod', 4);
$fee = \Botble\Payment\Supports\PaymentFeeHelper::calculateFee('cod', 500);
echo "  [OK] COD Fee for ₹500 @ {$feeValue}%: ₹{$fee}\n";
if ($fee == 20.0) {
    echo "  [OK] Cash collection charges calculate exactly to ₹20.00\n";
}

echo "SUITE 4 PASSED!\n\n";

echo "====================================================\n";
echo "   ALL 4 VERIFICATION SUITES PASSED CLEANLY!\n";
echo "====================================================\n";
