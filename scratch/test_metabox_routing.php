<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Botble\Base\Facades\MetaBox;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Http\Request;
use SparroWave\RazorpayEasyEmi\Providers\HookServiceProvider;

$product = Product::find(3692) ?? Product::first();
$provider = new HookServiceProvider($app);

// Simulate logged in vendor customer in session
$vendorCustomer = Customer::where('is_vendor', true)->first();
if ($vendorCustomer) {
    auth('customer')->setUser($vendorCustomer);
}

echo "Customer logged in: " . (auth('customer')->check() ? "YES (is_vendor: " . auth('customer')->user()->is_vendor . ")" : "NO") . "\n";

// SCENARIO 1: Real Admin Product Edit Route: /admin/ecommerce/products/edit/3692
echo "\n=== Scenario 1: Admin Route with Vendor Session Active (/admin/ecommerce/products/edit/3692) ===\n";
$adminReq = Request::create('/admin/ecommerce/products/edit/3692', 'GET');
app()->instance('request', $adminReq);

MetaBox::removeMetaBox('razorpay_easy_emi_meta_box', get_class($product), 'advanced');
MetaBox::removeMetaBox('razorpay_easy_emi_vendor_meta_box', get_class($product), 'advanced');

$provider->addProductMetaBox('default', $product);
$provider->addVendorProductMetaBox('default', $product);

$metaBoxesAdmin = MetaBox::getMetaBoxes();
$adminHasAdminBox = ! empty($metaBoxesAdmin[get_class($product)]['advanced']['default']['razorpay_easy_emi_meta_box']);
$adminHasVendorBox = ! empty($metaBoxesAdmin[get_class($product)]['advanced']['default']['razorpay_easy_emi_vendor_meta_box']);

echo "Admin Panel has Admin MetaBox? " . ($adminHasAdminBox ? "YES (Correct)" : "NO (Error)") . "\n";
echo "Admin Panel has Vendor MetaBox? " . ($adminHasVendorBox ? "YES (LEAK ERROR!)" : "NO (Correct - Zero Leak!)") . "\n";

// SCENARIO 2: Vendor Product Edit Route: /vendor/products/edit/3692
echo "\n=== Scenario 2: Vendor Route (/vendor/products/edit/3692) ===\n";
$vendorReq = Request::create('/vendor/products/edit/3692', 'GET');
app()->instance('request', $vendorReq);

MetaBox::removeMetaBox('razorpay_easy_emi_meta_box', get_class($product), 'advanced');
MetaBox::removeMetaBox('razorpay_easy_emi_vendor_meta_box', get_class($product), 'advanced');

$provider->addProductMetaBox('default', $product);
$provider->addVendorProductMetaBox('default', $product);

$metaBoxesVendor = MetaBox::getMetaBoxes();
$vendorHasAdminBox = ! empty($metaBoxesVendor[get_class($product)]['advanced']['default']['razorpay_easy_emi_meta_box']);
$vendorHasVendorBox = ! empty($metaBoxesVendor[get_class($product)]['advanced']['default']['razorpay_easy_emi_vendor_meta_box']);

echo "Vendor Panel has Admin MetaBox? " . ($vendorHasAdminBox ? "YES (LEAK ERROR!)" : "NO (Correct - Protected!)") . "\n";
echo "Vendor Panel has Vendor Request MetaBox? " . ($vendorHasVendorBox ? "YES (Correct)" : "NO (Error)") . "\n";
