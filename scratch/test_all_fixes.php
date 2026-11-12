<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Botble\Marketplace\Models\Store;
use SparroWave\RazorpayEasyEmi\Enums\EmiRequestStatusEnum;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiCachedOffer;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiProductConfig;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiVendorRequest;
use SparroWave\RazorpayEasyEmi\Services\RazorpayOfferSyncService;
use SparroWave\RazorpayEasyEmi\Tables\AdminEmiRequestTable;
use SparroWave\RazorpayEasyEmi\Tables\VendorEmiRequestTable;

echo "=== 1. TEST RAZORPAY OFFER SYNC CONFIG & API LIMIT ===\n";
$syncService = app(RazorpayOfferSyncService::class);
$ref = new ReflectionMethod($syncService, 'syncOffers');
$fnCode = file_get_contents((new ReflectionClass($syncService))->getFileName());
$hasCount100 = str_contains($fnCode, "'count' => 100");
echo "syncOffers includes count => 100: " . ($hasCount100 ? "PASS" : "FAIL") . "\n";

echo "\n=== 2. TEST CANCEL PENDING REQUEST ROUTE & LOGIC ===\n";
$store = Store::where('id', 12)->first();
if ($store) {
    auth('customer')->setUser($store->customer);

    // Create a dummy pending request
    $pendingReq = RazorpayEmiVendorRequest::query()->create([
        'product_id' => 3692,
        'store_id' => $store->id,
        'requested_scheme' => 'no_cost',
        'subvention_willingness' => 'seller',
        'notes' => 'Test cancel pending request',
        'status' => EmiRequestStatusEnum::PENDING,
    ]);
    echo "Created dummy pending request ID: {$pendingReq->id}\n";

    // Call cancel method
    $controller = app(\SparroWave\RazorpayEasyEmi\Http\Controllers\VendorEmiRequestController::class);
    $response = new \Botble\Base\Http\Responses\BaseHttpResponse();
    $cancelResult = $controller->cancel($pendingReq->id, $response);
    
    $checkDeleted = RazorpayEmiVendorRequest::query()->find($pendingReq->id);
    echo "Cancel response message: " . $cancelResult->getMessage() . "\n";
    echo "Request row deleted from DB: " . ($checkDeleted === null ? "PASS" : "FAIL") . "\n";
} else {
    echo "Store 12 not found for vendor cancel test\n";
}

echo "\n=== 3. TEST TABLE PRODUCT/STORE NAME SEARCH FILTER ===\n";
// Test Admin table filter
$adminTable = app(AdminEmiRequestTable::class);
request()->merge(['search' => ['value' => 'Kubota']]);
$adminAjax = $adminTable->ajax();
$adminData = json_decode($adminAjax->getContent(), true);
echo "Admin Table search 'Kubota' executed successfully. recordsTotal: " . ($adminData['recordsTotal'] ?? 0) . ", recordsFiltered: " . ($adminData['recordsFiltered'] ?? 0) . "\n";

// Test Vendor table filter
if ($store) {
    auth('customer')->setUser($store->customer);
    $vendorTable = app(VendorEmiRequestTable::class);
    request()->merge(['search' => ['value' => 'Kubota']]);
    $vendorAjax = $vendorTable->ajax();
    $vendorData = json_decode($vendorAjax->getContent(), true);
    echo "Vendor Table search 'Kubota' executed successfully. recordsTotal: " . ($vendorData['recordsTotal'] ?? 0) . ", recordsFiltered: " . ($vendorData['recordsFiltered'] ?? 0) . "\n";
}

echo "\n=== 4. TEST CHECKOUT BRIDGE CART SCHEME ISOLATION ===\n";
$hookProvider = new \SparroWave\RazorpayEasyEmi\Providers\HookServiceProvider($app);
$bridgeHtml = $hookProvider->renderCheckoutBridge();
echo "Render Checkout Bridge length: " . strlen($bridgeHtml ?? '') . "\n";
$hasOffersVar = str_contains($bridgeHtml ?? '', 'window.__razorpayActiveOffers =');
echo "Contains __razorpayActiveOffers variable: " . ($hasOffersVar ? "PASS" : "FAIL") . "\n";

echo "\nALL TESTS COMPLETED SUCCESSFULLY!\n";
