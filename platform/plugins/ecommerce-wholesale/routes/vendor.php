<?php

use Botble\EcommerceWholesale\Http\Controllers\Fronts\VendorWholesaleController;
use Botble\Marketplace\Http\Middleware\LocaleMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('plugins.marketplace.general.vendor_panel_dir', 'vendor'),
    'as' => 'marketplace.vendor.',
    'middleware' => ['web', 'core', 'vendor', LocaleMiddleware::class],
], function (): void {
    Route::get('wholesale-products', [VendorWholesaleController::class, 'index'])
        ->name('wholesale-products.index');
});
