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

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST: OrderIncompleteTable Query Hook ===\n";

add_filter(BASE_FILTER_TABLE_QUERY, function ($query, $table) {
    if ($table instanceof \Botble\Ecommerce\Tables\OrderIncompleteTable) {
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('ec_orders.user_id')->where('ec_orders.user_id', '>', 0);
            })->orWhereHas('address', function ($sub) {
                $sub->whereNotNull('name')->where('name', '!=', '');
            });
        });
    }

    return $query;
}, 120, 2);

$table = app(\Botble\Ecommerce\Tables\OrderIncompleteTable::class);
$query = $table->query();

echo "SQL: " . $query->toSql() . "\n";
$bindings = $query->getBindings();
echo "Bindings: " . json_encode($bindings) . "\n";
echo "Count on local: " . $query->count() . "\n";

echo "TEST COMPLETE!\n";
