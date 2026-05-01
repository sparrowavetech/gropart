<?php

use Botble\EcommerceWholesale\Http\Controllers\Fronts\CompanyProfileController;
use Botble\EcommerceWholesale\Http\Controllers\Fronts\CustomerWholesaleController;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Theme::registerRoutes(function (): void {
    Route::middleware('customer')
        ->prefix('customer/wholesale')
        ->name('customer.wholesale.')
        ->group(function (): void {
            Route::get('/', [CustomerWholesaleController::class, 'index'])->name('index');

            Route::get('/reapply', [CustomerWholesaleController::class, 'showReapplyForm'])->name('reapply.form');
            Route::post('/reapply', [CustomerWholesaleController::class, 'reapply'])
                ->middleware('throttle:1,1440')
                ->name('reapply.submit');

            Route::get('/profile/edit', [CompanyProfileController::class, 'edit'])->name('profile.edit');
            Route::post('/profile/update', [CompanyProfileController::class, 'update'])->name('profile.update');
        });
});
