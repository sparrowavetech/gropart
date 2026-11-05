<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Route;
use SparroWave\SellerInvoicing\Http\Controllers\AdminController;
use SparroWave\SellerInvoicing\Http\Controllers\SellerInvoicingSettingController;
use SparroWave\SellerInvoicing\Http\Controllers\VendorController;

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$expected = [
    'seller-invoicing.index' => [AdminController::class . '@index', ['GET', 'HEAD']],
    'seller-invoicing.show' => [AdminController::class . '@show', ['GET', 'HEAD']],
    'seller-invoicing.download' => [AdminController::class . '@download', ['GET', 'HEAD']],
    'seller-invoicing.print' => [AdminController::class . '@print', ['GET', 'HEAD']],
    'seller-invoicing.orders.show' => [AdminController::class . '@showByOrder', ['GET', 'HEAD']],
    'seller-invoicing.orders.generate' => [AdminController::class . '@generate', ['POST']],
    'seller-invoicing.settings' => [SellerInvoicingSettingController::class . '@edit', ['GET', 'HEAD']],
    'seller-invoicing.settings.update' => [SellerInvoicingSettingController::class . '@update', ['PUT']],
    'marketplace.vendor.seller-invoicing.index' => [VendorController::class . '@index', ['GET', 'HEAD']],
    'marketplace.vendor.seller-invoicing.show' => [VendorController::class . '@show', ['GET', 'HEAD']],
    'marketplace.vendor.seller-invoicing.download' => [VendorController::class . '@download', ['GET', 'HEAD']],
    'marketplace.vendor.seller-invoicing.print' => [VendorController::class . '@print', ['GET', 'HEAD']],
    'marketplace.vendor.seller-invoicing.orders.show' => [VendorController::class . '@showByOrder', ['GET', 'HEAD']],
];

foreach ($expected as $name => [$action, $methods]) {
    $route = Route::getRoutes()->getByName($name);
    if (! $route) {
        throw new RuntimeException("Route {$name} is not registered.");
    }

    if ($route->getActionName() !== $action) {
        throw new RuntimeException("Route {$name} action mismatch: expected {$action}, got {$route->getActionName()}.");
    }

    foreach ($methods as $method) {
        if (! in_array($method, $route->methods(), true)) {
            throw new RuntimeException("Route {$name} missing method {$method}.");
        }
    }
}

echo "All seller invoicing routes verified: PASS\n";
