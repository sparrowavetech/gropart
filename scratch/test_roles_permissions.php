<?php

spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'SparroWave\\SellerInvoicing\\')) {
        $file = __DIR__ . '/../platform/plugins/seller-invoicing/src/' . str_replace('\\', '/', substr($class, 27)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Load the plugin configs just like when plugins boot
foreach (['enquiry-products', 'sms', 'seller-invoicing', 'product-free-shipping', 'mobile-command-bar'] as $plugin) {
    $configFile = __DIR__ . "/../platform/plugins/{$plugin}/config/permissions.php";
    if (file_exists($configFile)) {
        config(["plugins.{$plugin}.permissions" => require $configFile]);
    }
}

$role = new \Botble\ACL\Models\Role();
$permissions = $role->getAvailablePermissions();

$checkFlags = [
    'enquiry-products.index',
    'enquiry-products.settings',
    'enquiry-products.edit',
    'enquiry-products.destroy',
    'sms.index',
    'sms.create',
    'sms.edit',
    'sms.destroy',
    'sms.settings',
    'sms.delivery-reports.index',
    'sms.logs.index',
    'sms.logs.show',
    'seller-invoicing.index',
    'seller-invoicing.show',
    'seller-invoicing.generate',
    'seller-invoicing.settings',
    'ecommerce.settings.product-free-shipping',
    'mobile-command-bar.settings',
];

echo "=== CHECKING PERMISSIONS FOR ROLES EDIT PAGE ===\n";
foreach ($checkFlags as $flag) {
    if (isset($permissions[$flag])) {
        echo "[$flag] => Name: '" . $permissions[$flag]['name'] . "'\n";
    } else {
        echo "[$flag] => NOT FOUND\n";
    }
}
