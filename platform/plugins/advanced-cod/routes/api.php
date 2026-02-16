<?php

use Illuminate\Support\Facades\Route;
use SparroWave\AdvancedCod\Http\Controllers\AdvancedCodApiController;

Route::group([
    'prefix' => 'api/v1/advanced-cod',
    'middleware' => 'api',
], function () {
    Route::get('eligibility', [AdvancedCodApiController::class, 'checkEligibility']);
    Route::get('prepayment-calculate', [AdvancedCodApiController::class, 'getPrepaymentBreakdown']);
});
