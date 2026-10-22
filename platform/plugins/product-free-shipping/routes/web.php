<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;
use SparroWave\ProductFreeShipping\Http\Controllers\Settings\ProductFreeShippingSettingController;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'ecommerce/settings/product-free-shipping', 'as' => 'ecommerce.settings.product-free-shipping.', 'permission' => 'ecommerce.settings.product-free-shipping'], function () {
        Route::get('/', [ProductFreeShippingSettingController::class, 'edit'])->name('index');
        Route::put('/', [ProductFreeShippingSettingController::class, 'update'])->name('update');
    });
});
