<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SparroWave\FarmartHelper\Http\Middleware\GuardContactChangeMiddleware;
use SparroWave\FarmartHelper\Http\Middleware\SanitizePhoneRequestMiddleware;

echo "=== STARTING CONTACT LOCK & PHONE FORMATTING TESTS ===\n\n";

$middleware = new GuardContactChangeMiddleware();
$sanitizer = new SanitizePhoneRequestMiddleware();

// -----------------------------------------------------------------------------
// Test 1: Customer Profile Edit Contact Lock
// -----------------------------------------------------------------------------
echo "[Test 1] Customer Profile Contact Lock... ";

$customer = new Customer();
$customer->id = 9991;
$customer->phone = '+919876543210';
$customer->email = 'original.customer@test.com';

Auth::guard('customer')->setUser($customer);
Auth::guard('web')->logout();

$request = Request::create('/customer/edit-account', 'POST', [
    'name' => 'Updated Name',
    'phone' => '+911111111111',       // Malicious attempt to change phone
    'phone_display' => '1111111111',  // Malicious attempt via display field
    'email' => 'hacked@evil.com',     // Malicious attempt to change email
]);

$response = $middleware->handle($request, function ($req) {
    return $req;
});

assert($response->input('phone') === '+919876543210', 'Phone was not locked to customer original phone');
assert($response->input('phone_display') === '+919876543210', 'Phone display was not locked');
assert($response->input('email') === 'original.customer@test.com', 'Email was not locked to customer original email');
assert($response->input('name') === 'Updated Name', 'Non-contact field should remain editable');
echo "PASS\n";

// -----------------------------------------------------------------------------
// Test 2: Customer Address Create Contact Lock
// -----------------------------------------------------------------------------
echo "[Test 2] Customer Address Create Contact Lock... ";

$request = Request::create('/customer/address/create', 'POST', [
    'name' => 'Office Address',
    'phone' => '+912222222222',
    'email' => 'fake@office.com',
    'address' => '123 Market Street',
]);

$response = $middleware->handle($request, function ($req) {
    return $req;
});

assert($response->input('phone') === '+919876543210', 'New address phone was not locked to customer account');
assert($response->input('email') === 'original.customer@test.com', 'New address email was not locked to customer account');
assert($response->input('address') === '123 Market Street', 'Address field should remain intact');
echo "PASS\n";

// -----------------------------------------------------------------------------
// Test 3: Vendor Store Settings Contact Lock
// -----------------------------------------------------------------------------
echo "[Test 3] Vendor Store Settings Contact Lock... ";

$store = new Store();
$store->id = 555;
$store->customer_id = $customer->id;
$store->phone = '+919988776655';
$store->email = 'store.official@gropart.com';

$customer->setRelation('store', $store);

$request = Request::create('/marketplace/vendor/settings', 'POST', [
    'name' => 'Super Store',
    'phone' => '+910000000000',
    'phone_display' => '0000000000',
    'email' => 'changed.store@fake.com',
]);

$response = $middleware->handle($request, function ($req) {
    return $req;
});

assert($response->input('phone') === '+919988776655', 'Store phone was not locked to store official phone');
assert($response->input('phone_display') === '+919988776655', 'Store phone display was not locked');
assert($response->input('email') === 'store.official@gropart.com', 'Store email was not locked to store official email');
assert($response->input('name') === 'Super Store', 'Store name should remain editable');
echo "PASS\n";

// -----------------------------------------------------------------------------
// Test 4: Admin Bypass Verification (Admin MUST be allowed to edit all contacts)
// -----------------------------------------------------------------------------
echo "[Test 4] Admin Bypass Verification... ";

$adminUser = new \Botble\ACL\Models\User();
$adminUser->id = 1;
Auth::guard('web')->setUser($adminUser);

$request = Request::create('/admin/ecommerce/customers/edit/9991', 'POST', [
    'phone' => '+919999988888',
    'email' => 'admin.updated@gropart.com',
]);

$response = $middleware->handle($request, function ($req) {
    return $req;
});

assert($response->input('phone') === '+919999988888', 'Admin update should NOT be locked');
assert($response->input('email') === 'admin.updated@gropart.com', 'Admin update should NOT be locked');
echo "PASS\n";

// Reset auth
Auth::guard('web')->logout();
Auth::guard('customer')->logout();

// -----------------------------------------------------------------------------
// Test 5: Universal Phone Sanitizer Middleware
// -----------------------------------------------------------------------------
echo "[Test 5] Universal Phone Sanitizer Middleware... ";

// Case A: phone_display on random route
$request = Request::create('/any/custom/route', 'POST', [
    'phone' => '',
    'phone_display' => '+919876543210',
]);

$response = $sanitizer->handle($request, function ($req) {
    return $req;
});

assert($response->input('phone') === '+919876543210', 'phone_display was not merged to phone');

// Case B: nested address array on checkout or order update
$request = Request::create('/checkout/process', 'POST', [
    'address' => [
        'name' => 'John Doe',
        'phone' => '',
        'phone_display' => '+919123456789',
    ],
]);

$response = $sanitizer->handle($request, function ($req) {
    return $req;
});

$addr = $response->input('address');
assert($addr['phone'] === '+919123456789', 'Nested address.phone_display was not merged to address.phone');
echo "PASS\n";

// -----------------------------------------------------------------------------
// Test 6: Translations Integrity Check (EN & HI)
// -----------------------------------------------------------------------------
echo "[Test 6] Translations Integrity Check (EN & HI)... ";

$enKeys = include __DIR__ . '/../platform/plugins/farmart-helper/resources/lang/en/farmart-helper.php';
$hiKeys = include __DIR__ . '/../platform/plugins/farmart-helper/resources/lang/hi/farmart-helper.php';

$requiredKeys = [
    'contact_lock_notice',
    'support_ticket',
    'contact_lock_warning',
    'address_contact_locked_notice',
    'vendor_contact_locked_notice',
    'order_contact_help',
];

foreach ($requiredKeys as $key) {
    assert(isset($enKeys[$key]) && ! empty($enKeys[$key]), "Missing EN key: $key");
    assert(isset($hiKeys[$key]) && ! empty($hiKeys[$key]), "Missing HI key: $key");
}
echo "PASS\n";

echo "\n>>> ALL 6 TESTS PASSED SUCCESSFULLY! <<<\n";

