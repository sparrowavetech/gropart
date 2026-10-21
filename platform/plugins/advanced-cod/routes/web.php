<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::get('advanced-cod/settings', [\SparroWave\AdvancedCod\Http\Controllers\ManualConfirmationController::class,'settings'])->name('advanced-cod.settings');
    Route::get('advanced-cod/confirm/{order}', [\SparroWave\AdvancedCod\Http\Controllers\ManualConfirmationController::class,'show'])->name('advanced-cod.confirm.show');
    Route::post('advanced-cod/confirm/{order}', [\SparroWave\AdvancedCod\Http\Controllers\ManualConfirmationController::class,'store'])->middleware('preventDemo')->name('advanced-cod.confirm.store');
    Route::group(['prefix'=>'advanced-cod/settlement','permission'=>'advanced-cod.settle','middleware'=>['preventDemo']], function () {
        Route::get('{order}', [\SparroWave\AdvancedCod\Http\Controllers\SettlementController::class,'show'])->name('advanced-cod.settlement.show');
        Route::post('{order}', [\SparroWave\AdvancedCod\Http\Controllers\SettlementController::class,'store'])->name('advanced-cod.settlement.store');
    });
});
