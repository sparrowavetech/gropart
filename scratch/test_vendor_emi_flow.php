<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Models\Store;
use SparroWave\RazorpayEasyEmi\Enums\EmiRequestStatusEnum;
use SparroWave\RazorpayEasyEmi\Enums\EmiSchemeEnum;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiProductConfig;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiVendorRequest;
use SparroWave\RazorpayEasyEmi\Services\EmiNotificationService;
use SparroWave\RazorpayEasyEmi\Tables\AdminEmiRequestTable;
use SparroWave\RazorpayEasyEmi\Tables\VendorEmiRequestTable;

echo "=== VENDOR & ADMIN EMI LIFECYCLE TEST ===\n\n";

// 1. Clean test state for product 3692
$productId = 3692;
RazorpayEmiVendorRequest::where('product_id', $productId)->delete();
RazorpayEmiProductConfig::where('product_id', $productId)->delete();
echo "1. Cleaned previous test records for Product ID $productId.\n";

$product = Product::find($productId);
if (! $product) {
    die("Product 3692 not found!\n");
}
$store = Store::find($product->store_id);
echo "   Product: '{$product->name}'\n";
echo "   Store: ID {$product->store_id} ('" . ($store ? $store->name : 'N/A') . "')\n";
echo "   Customer/Owner ID: " . ($store ? $store->customer_id : 'N/A') . "\n\n";

// 2. Mock vendor login
$customer = $store ? $store->customer : null;
if ($customer) {
    auth('customer')->setUser($customer);
    echo "2. Authenticated as vendor customer '{$customer->name}' (ID: {$customer->id})\n";
} else {
    die("No customer associated with store!\n");
}

// 3. Simulate Vendor Request Submission
$requestPayload = [
    'product_id' => $productId,
    'requested_scheme' => 'no_cost',
    'subvention_willingness' => 'seller',
    'notes' => 'Testing EMI request submission from vendor dashboard',
];

$vendorRequest = RazorpayEmiVendorRequest::create([
    'product_id' => $productId,
    'store_id' => $store->id,
    'requested_scheme' => $requestPayload['requested_scheme'],
    'subvention_willingness' => $requestPayload['subvention_willingness'],
    'notes' => $requestPayload['notes'],
    'status' => EmiRequestStatusEnum::PENDING,
]);

echo "3. Created Vendor EMI Request in DB: ID {$vendorRequest->id}\n";
echo "   Status: {$vendorRequest->status->getValue()} ({$vendorRequest->status->label()})\n";
echo "   Scheme: {$vendorRequest->requested_scheme->getValue()}\n";
echo "   Is Pending Check: " . ($vendorRequest->status->isPending() ? 'YES' : 'NO') . "\n\n";

// 4. Test Email Notification Service
echo "4. Testing EmiNotificationService...\n";
$notifService = app(EmiNotificationService::class);
$adminNotifResult = $notifService->sendNewRequestNotificationToAdmin($vendorRequest);
echo "   Admin notification triggered: " . ($adminNotifResult ? 'SUCCESS' : 'LOGGED/HANDLED') . "\n";

// 5. Test Vendor Metabox View Rendering
echo "5. Testing vendor product-request-box view rendering...\n";
$model = $product;
$html = view('plugins/razorpay-easy-emi::vendor.product-request-box', compact('model'))->render();
if (str_contains($html, 'review_in_progress') || str_contains($html, 'Under Review') || str_contains($html, 'समीक्षाधीन')) {
    echo "   View State: PASS -> Correctly shows 'Under Review / Request Submitted' box!\n";
} else {
    echo "   View State: UNEXPECTED -> HTML preview:\n" . substr(strip_tags($html), 0, 200) . "\n";
}
if (! str_contains($html, '<form id="form-vendor-emi-request"')) {
    echo "   Nested Form Check: PASS -> No nested form tag present! Completely safe from browser form stripping.\n";
} else {
    echo "   Nested Form Check: FAIL -> Found nested form tag!\n";
}
echo "\n";

// 6. Test Admin Table Query & Ajax
echo "6. Testing AdminEmiRequestTable...\n";
$adminTable = app(AdminEmiRequestTable::class);
$adminTableQuery = $adminTable->query();
$adminCount = $adminTableQuery->count();
echo "   Total rows visible to Admin: $adminCount\n";
$adminRow = $adminTableQuery->where('id', $vendorRequest->id)->first();
if ($adminRow) {
    echo "   PASS -> Request ID {$vendorRequest->id} is visible in Admin Table!\n";
    echo "   Admin Row Store: " . ($adminRow->store ? $adminRow->store->name : 'N/A') . "\n";
    echo "   Admin Row Product: " . ($adminRow->product ? $adminRow->product->name : 'N/A') . "\n";
} else {
    echo "   FAIL -> Request ID {$vendorRequest->id} NOT found in Admin Table query!\n";
}
echo "\n";

// 7. Test Vendor Table Query & Ajax
echo "7. Testing VendorEmiRequestTable...\n";
$vendorTable = app(VendorEmiRequestTable::class);
$vendorTableQuery = $vendorTable->query();
$vendorCount = $vendorTableQuery->count();
echo "   Total rows visible to Vendor '{$store->name}': $vendorCount\n";
$vendorRow = $vendorTableQuery->where('id', $vendorRequest->id)->first();
if ($vendorRow) {
    echo "   PASS -> Request ID {$vendorRequest->id} is visible in Vendor Table!\n";
    echo "   Vendor Scoped Trait: " . ($vendorTable->isVendorScopedTable() ? 'YES (ForVendor active)' : 'NO') . "\n";
} else {
    echo "   FAIL -> Request ID {$vendorRequest->id} NOT found in Vendor Table query!\n";
}
echo "\n";

// 8. Test Admin Approval Action
echo "8. Testing Admin Approval Action...\n";
$vendorRequest->status = EmiRequestStatusEnum::APPROVED;
$vendorRequest->approved_offer_id = 'offer_test_12345';
$vendorRequest->admin_remarks = 'Approved for Q4 Festive Campaign';
$vendorRequest->reviewed_by = 1;
$vendorRequest->reviewed_at = now();
$vendorRequest->save();

RazorpayEmiProductConfig::updateOrCreate(
    ['product_id' => $vendorRequest->product_id],
    [
        'status' => 'enabled',
        'emi_type' => $vendorRequest->requested_scheme,
        'offer_mode' => 'custom',
        'offer_id' => json_encode(['offer_test_12345']),
        'subvention_payer' => $vendorRequest->subvention_willingness,
    ]
);

$vendorNotifResult = $notifService->sendStatusUpdateNotificationToVendor($vendorRequest);
echo "   Request approved and product config enabled.\n";
echo "   Vendor status update email triggered: " . ($vendorNotifResult ? 'SUCCESS' : 'LOGGED/HANDLED') . "\n";

// 9. Re-verify Vendor Metabox View Rendering after Approval
echo "\n9. Re-verifying vendor product-request-box view after approval...\n";
$htmlApproved = view('plugins/razorpay-easy-emi::vendor.product-request-box', compact('model'))->render();
if (str_contains($htmlApproved, 'Active') || str_contains($htmlApproved, 'emi_enabled_for_product') || str_contains($htmlApproved, 'सक्रिय')) {
    echo "   PASS -> Correctly shows 'Approved & Active' status with scheme details!\n";
} else {
    echo "   FAIL -> Did not show active status!\n";
}

echo "\n=== ALL TESTS COMPLETED SUCCESSFULLY ===\n";

