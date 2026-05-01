<?php

use Botble\Base\Facades\AdminHelper;
use Botble\EcommerceWholesale\Http\Controllers\ApplicationController;
use Botble\EcommerceWholesale\Http\Controllers\CustomerGroupController;
use Botble\EcommerceWholesale\Http\Controllers\Fronts\WholesaleRegisterController;
use Botble\EcommerceWholesale\Http\Controllers\LicenseController;
use Botble\EcommerceWholesale\Http\Controllers\PricingRuleController;
use Botble\EcommerceWholesale\Http\Controllers\WholesaleProductController;
use Botble\EcommerceWholesale\Http\Controllers\WholesaleSettingController;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::group(['prefix' => 'wholesale', 'as' => 'wholesale.'], function (): void {
        Route::get('products', [WholesaleProductController::class, 'index'])
            ->name('products.index');

        Route::group(['prefix' => 'customer-groups', 'as' => 'customer-groups.'], function (): void {
            Route::resource('', CustomerGroupController::class)
                ->parameters(['' => 'customer_group']);
        });

        Route::group(['prefix' => 'pricing-rules', 'as' => 'pricing-rules.'], function (): void {
            Route::resource('', PricingRuleController::class)
                ->parameters(['' => 'pricing_rule']);
        });

        Route::group(['prefix' => 'applications', 'as' => 'applications.'], function (): void {
            Route::resource('', ApplicationController::class)
                ->parameters(['' => 'application'])
                ->only(['index', 'edit', 'destroy']);
            Route::post('{application}/approve', [ApplicationController::class, 'approve'])->name('approve');
            Route::post('{application}/reject', [ApplicationController::class, 'reject'])->name('reject');
        });

        Route::prefix('settings')->name('settings')->group(function (): void {
            Route::get('', [WholesaleSettingController::class, 'edit']);
            Route::put('', [WholesaleSettingController::class, 'update'])->name('.update');
        });

        Route::group(['prefix' => 'license', 'as' => 'license.', 'permission' => 'wholesale.settings'], function (): void {
            Route::post('activate', [LicenseController::class, 'activate'])->name('activate');
            Route::post('deactivate', [LicenseController::class, 'deactivate'])->name('deactivate');
        });
    });
});

Theme::registerRoutes(function (): void {
    Route::group(['prefix' => 'wholesale', 'as' => 'public.wholesale.'], function (): void {
        Route::get('register', [WholesaleRegisterController::class, 'show'])->name('register');
        Route::post('register', [WholesaleRegisterController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('register.store');
        Route::get('success', [WholesaleRegisterController::class, 'success'])->name('success');
    });
});
