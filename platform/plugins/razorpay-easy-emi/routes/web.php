<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;
use SparroWave\RazorpayEasyEmi\Http\Controllers\AdminEmiRequestController;
use SparroWave\RazorpayEasyEmi\Http\Controllers\RazorpayEasyEmiSettingController;
use SparroWave\RazorpayEasyEmi\Http\Controllers\VendorEmiRequestController;

AdminHelper::registerRoutes(function (): void {
    Route::group(['prefix' => 'razorpay-easy-emi', 'as' => 'razorpay-easy-emi.'], function (): void {
        Route::group(['permission' => 'razorpay-easy-emi.settings'], function (): void {
            Route::get('settings', [RazorpayEasyEmiSettingController::class, 'edit'])->name('settings');
            Route::put('settings', [RazorpayEasyEmiSettingController::class, 'update'])->name('settings.update');
            Route::post('sync-offers', [RazorpayEasyEmiSettingController::class, 'syncOffers'])->name('settings.sync-offers');
        });

        Route::group(['prefix' => 'requests', 'as' => 'requests.', 'permission' => 'razorpay-easy-emi.requests.index'], function (): void {
            Route::match(['GET', 'POST'], '/', [AdminEmiRequestController::class, 'index'])->name('index');

            Route::group(['permission' => 'razorpay-easy-emi.requests.action'], function (): void {
                Route::get('{id}/review', [AdminEmiRequestController::class, 'review'])->name('review');
                Route::post('{id}/approve', [AdminEmiRequestController::class, 'approve'])->name('approve');
                Route::post('{id}/reject', [AdminEmiRequestController::class, 'reject'])->name('reject');
                Route::delete('{id}', [AdminEmiRequestController::class, 'destroy'])->name('destroy');
            });
        });
    });
});

Route::group([
    'prefix' => config('plugins.marketplace.general.vendor_panel_dir', 'vendor') . '/razorpay-easy-emi',
    'as' => 'marketplace.vendor.razorpay-easy-emi.',
    'middleware' => ['web', 'core', 'vendor'],
], function (): void {
    Route::group(['prefix' => 'requests', 'as' => 'requests.'], function (): void {
        Route::match(['GET', 'POST'], '/', [VendorEmiRequestController::class, 'index'])->name('index');
        Route::post('store', [VendorEmiRequestController::class, 'store'])->name('store');
        Route::post('{id}/cancel', [VendorEmiRequestController::class, 'cancel'])->name('cancel');
    });
});
