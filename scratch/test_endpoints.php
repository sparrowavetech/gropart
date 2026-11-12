<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Botble\ACL\Models\User;
use Botble\Marketplace\Models\Store;
use Illuminate\Http\Request;

echo "=== ROUTING & CONTROLLER RESPONSE TEST ===\n\n";

// 1. Test Admin Requests Index (/admin/razorpay-easy-emi/requests)
$admin = User::query()->where('super_user', 1)->first();
auth()->setUser($admin);

$requestAdmin = Request::create('/admin/razorpay-easy-emi/requests', 'GET');
$responseAdmin = $kernel->handle($requestAdmin);
echo "1. GET /admin/razorpay-easy-emi/requests\n";
echo "   Status Code: " . $responseAdmin->getStatusCode() . "\n";
echo "   Response Length: " . strlen($responseAdmin->getContent()) . "\n";
$kernel->terminate($requestAdmin, $responseAdmin);

// 2. Test Vendor Requests Index (/vendor/razorpay-easy-emi/requests)
$store = Store::query()->where('id', 12)->first();
$customer = $store ? $store->customer : null;
auth('customer')->setUser($customer);

$requestVendor = Request::create('/vendor/razorpay-easy-emi/requests', 'GET');
$responseVendor = $kernel->handle($requestVendor);
echo "\n2. GET /vendor/razorpay-easy-emi/requests\n";
echo "   Status Code: " . $responseVendor->getStatusCode() . "\n";
echo "   Response Length: " . strlen($responseVendor->getContent()) . "\n";
if ($responseVendor->getStatusCode() === 200) {
    echo "   PASS -> Vendor requests table loaded successfully with 200 OK!\n";
} else {
    echo "   Status: " . $responseVendor->getStatusCode() . "\n";
}
$kernel->terminate($requestVendor, $responseVendor);

// 3. Test Vendor Request Store (/vendor/razorpay-easy-emi/requests/store)
auth('customer')->setUser($customer);
$requestStore = Request::create('/vendor/razorpay-easy-emi/requests/store', 'POST', [
    'product_id' => 3692,
    'requested_scheme' => 'no_cost',
    'subvention_willingness' => 'seller',
    'notes' => 'API simulation notes',
]);
$requestStore->headers->set('Accept', 'application/json');
$responseStore = $kernel->handle($requestStore);
echo "\n3. POST /vendor/razorpay-easy-emi/requests/store\n";
echo "   Status Code: " . $responseStore->getStatusCode() . "\n";
echo "   Response: " . $responseStore->getContent() . "\n";
$kernel->terminate($requestStore, $responseStore);

echo "\n=== ROUTING TESTS COMPLETE ===\n";
