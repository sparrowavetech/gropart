<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;
use SparroWave\SellerInvoicing\Http\Controllers\AdminController;
use SparroWave\SellerInvoicing\Http\Controllers\SellerInvoicingSettingController;
use SparroWave\SellerInvoicing\Http\Controllers\VendorController;

AdminHelper::registerRoutes(function (): void {
    Route::group(['prefix' => 'seller-invoicing', 'as' => 'seller-invoicing.'], function (): void {
        Route::group(['permission' => 'seller-invoicing.index'], function (): void {
            Route::match(['GET', 'POST'], '/', [AdminController::class, 'index'])->name('index');
            Route::get('orders/{order}', [AdminController::class, 'showByOrder'])->name('orders.show');
        });

        Route::group(['permission' => 'seller-invoicing.show'], function (): void {
            Route::get('{document}', [AdminController::class, 'show'])->name('show')->whereNumber('document');
            Route::get('{document}/download', [AdminController::class, 'download'])->name('download')->whereNumber('document');
            Route::get('{document}/print', [AdminController::class, 'print'])->name('print')->whereNumber('document');
            Route::post('bulk-download', [AdminController::class, 'bulkDownload'])->name('bulk-download');

            // Legacy backward-compatibility routes
            Route::get('documents/{document}', fn ($document) => redirect()->route('seller-invoicing.show', $document));
            Route::get('documents/{document}/download', fn ($document) => redirect()->route('seller-invoicing.download', $document));
            Route::get('documents/{document}/print', fn ($document) => redirect()->route('seller-invoicing.print', $document));
        });

        Route::group(['permission' => 'seller-invoicing.generate'], function (): void {
            Route::post('orders/{order}/generate', [AdminController::class, 'generate'])->name('orders.generate');
        });

        Route::group(['permission' => 'seller-invoicing.settings'], function (): void {
            Route::get('settings', [SellerInvoicingSettingController::class, 'edit'])->name('settings');
            Route::put('settings', [SellerInvoicingSettingController::class, 'update'])->name('settings.update');
            Route::post('settings/regenerate', [SellerInvoicingSettingController::class, 'regenerate'])->name('settings.regenerate');
        });
    });
});

Route::group([
    'prefix' => config('plugins.marketplace.general.vendor_panel_dir', 'vendor') . '/seller-invoicing',
    'as' => 'marketplace.vendor.seller-invoicing.',
    'middleware' => ['web', 'core', 'vendor', \Botble\Marketplace\Http\Middleware\LocaleMiddleware::class],
], function (): void {
    Route::match(['GET', 'POST'], '/', [VendorController::class, 'index'])->name('index');
    Route::get('{document}', [VendorController::class, 'show'])->name('show')->whereNumber('document');
    Route::get('{document}/download', [VendorController::class, 'download'])->name('download')->whereNumber('document');
    Route::get('{document}/print', [VendorController::class, 'print'])->name('print')->whereNumber('document');
    Route::post('bulk-download', [VendorController::class, 'bulkDownload'])->name('bulk-download');
    Route::get('filter-input', [VendorController::class, 'filterInput'])->name('filter-input');
    Route::get('orders/{order}', [VendorController::class, 'showByOrder'])->name('orders.show');

    // Legacy backward-compatibility routes
    Route::get('documents/{document}', fn ($document) => redirect()->route('marketplace.vendor.seller-invoicing.show', $document));
    Route::get('documents/{document}/download', fn ($document) => redirect()->route('marketplace.vendor.seller-invoicing.download', $document));
    Route::get('documents/{document}/print', fn ($document) => redirect()->route('marketplace.vendor.seller-invoicing.print', $document));
});
