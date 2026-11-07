<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST: SELLER NAME STRIPPING & MODAL ENHANCEMENTS ===\n\n";

// 1. Suffix stripping regex test (simulating the JS regex logic)
$cases = [
    [
        'title' => 'Hadamba (Patta) Multi Crop Thresher Rotor Guide Frame 5 Hole Foundation (SB Marketing)',
        'store' => 'SB Marketing',
        'expected' => 'Hadamba (Patta) Multi Crop Thresher Rotor Guide Frame 5 Hole Foundation',
    ],
    [
        'title' => 'Tokri (Basket) Thresher Double (link) Kadi Rear Chain (SB Marketing)',
        'store' => 'SB Marketing',
        'expected' => 'Tokri (Basket) Thresher Double (link) Kadi Rear Chain',
    ],
    [
        'title' => 'Tractor Spare Part (Ameta & Sons Pvt. Ltd.)',
        'store' => 'Ameta & Sons Pvt. Ltd.',
        'expected' => 'Tractor Spare Part',
    ],
    [
        'title' => 'Product without store',
        'store' => null,
        'expected' => 'Product without store',
    ],
];

$passCount = 0;
foreach ($cases as $c) {
    $title = $c['title'];
    $store = $c['store'];
    if ($store) {
        $escapedStore = preg_quote($store, '/');
        $cleaned = trim(preg_replace('/\s*\(' . $escapedStore . '\)\s*$/i', '', $title));
    } else {
        $cleaned = $title;
    }

    if ($cleaned === $c['expected']) {
        echo "[PASS] '{$title}' -> '{$cleaned}'\n";
        $passCount++;
    } else {
        echo "[FAIL] '{$title}' -> '{$cleaned}', expected '{$c['expected']}'\n";
    }
}

require_once __DIR__ . '/../platform/plugins/farmart-helper/src/Providers/OrderCreateHelperServiceProvider.php';

// 2. Test CSS and JS injection in OrderCreateHelperServiceProvider
$provider = new \SparroWave\FarmartHelper\Providers\OrderCreateHelperServiceProvider($app);
$reflector = new ReflectionClass($provider);
$method = $reflector->getMethod('getAssetVersion');
$method->setAccessible(true);
$version = $method->invoke($provider);

echo "\nAsset version: {$version}\n";
if (!empty($version)) {
    echo "[PASS] Dynamic cache-busting version verified.\n";
} else {
    echo "[FAIL] Version is empty.\n";
}

// 3. Verify CSS in OrderCreateHelperServiceProvider
$code = file_get_contents(__DIR__ . '/../platform/plugins/farmart-helper/src/Providers/OrderCreateHelperServiceProvider.php');
if (strpos($code, 'max-width: 760px !important') !== false && strpos($code, '125px !important') !== false) {
    echo "[PASS] 760px modal width and 125px country select width present in ServiceProvider.\n";
} else {
    echo "[FAIL] Missing expected styles in ServiceProvider.\n";
}

// 4. Verify JS assets published
$filesToCheck = [
    'public/vendor/core/plugins/vendor-verified-badge/js/admin-vendor-badge.js',
    'public/vendor/core/plugins/farmart-helper/js/admin-order-create-helper.js',
    'public/vendor/core/plugins/advanced-cod/js/admin-order-advanced-cod.js',
    'public/vendor/core/plugins/shipmozo/js/admin-shipmozo.js',
    'public/vendor/core/plugins/product-free-shipping/js/admin-product-free-shipping.js',
];

foreach ($filesToCheck as $f) {
    $full = __DIR__ . '/../' . $f;
    if (file_exists($full)) {
        echo "[PASS] Published asset exists: {$f}\n";
    } else {
        echo "[FAIL] Asset missing: {$f}\n";
    }
}

echo "\nALL LOCAL CHECKS COMPLETE!\n";
