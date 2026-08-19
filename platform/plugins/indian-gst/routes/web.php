<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;
use SparroWave\IndianGst\Http\Controllers\IndianGstSettingController;
use SparroWave\IndianGst\Http\Controllers\IndianGstSlabController;

Route::group(['namespace' => 'SparroWave\IndianGst\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'ecommerce/settings/indian-gst', 'as' => 'ecommerce.settings.indian-gst.'], function () {
            Route::get('', [IndianGstSettingController::class, 'edit'])->name('index');
            Route::put('', [IndianGstSettingController::class, 'update'])->name('update');
        });

        Route::group(['prefix' => 'indian-gst', 'as' => 'indian-gst.'], function () {
            Route::group(['prefix' => 'slabs', 'as' => 'slabs.'], function () {
                Route::match(['GET', 'POST'], '', [IndianGstSlabController::class, 'index'])->name('index');
                Route::match(['GET', 'POST'], '{id}/products', [IndianGstSlabController::class, 'products'])->name('products');
            });
        });
    });
});
