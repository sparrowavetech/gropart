<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;
use SparroWave\VendorVerifiedBadge\Http\Controllers\VendorBadgeSettingController;

AdminHelper::registerRoutes(function (): void {
    Route::group(['prefix' => 'vendor-verified-badges', 'as' => 'vendor-verified-badge.'], function (): void {
        Route::group(['permission' => 'vendor-verified-badge.settings'], function (): void {
            Route::get('settings', [VendorBadgeSettingController::class, 'edit'])->name('settings');
            Route::put('settings', [VendorBadgeSettingController::class, 'update'])->name('settings.update');
        });

        Route::group(['permission' => 'marketplace.store.edit'], function (): void {
            Route::post('stores/{store}/quick-verify', [VendorBadgeSettingController::class, 'quickVerify'])->name('stores.quick-verify');
            Route::post('stores/{store}/quick-unverify', [VendorBadgeSettingController::class, 'quickUnverify'])->name('stores.quick-unverify');
        });
    });
});
