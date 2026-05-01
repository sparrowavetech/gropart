<?php

use Botble\Base\Facades\AdminHelper;
use Botble\LoyaltyPoints\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\LoyaltyPoints\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'loyalty-points', 'as' => 'loyalty-points.'], function (): void {
            Route::get('reports', 'LoyaltyPointsController@index')->name('index');

            Route::post('validate-member', 'LoyaltyAdminController@validateMemberId')
                ->name('validate-member');

            Route::group([
                'prefix' => 'members',
                'as' => 'members.',
            ], function (): void {
                Route::match(['GET', 'POST'], '/', 'MemberController@index')
                    ->name('index')
                    ->permission('loyalty-points.members.index');
                Route::post('adjust', 'PointAdjustmentController@store')
                    ->name('adjust.store')
                    ->permission('loyalty-points.members.adjust');
                Route::match(['GET', 'POST'], '{id}', 'MemberController@show')
                    ->name('show')
                    ->permission('loyalty-points.members.index');
            });

            Route::group([
                'prefix' => 'transactions',
                'as' => 'transactions.',
                'permission' => 'loyalty-points.members.index',
            ], function (): void {
                Route::match(['GET', 'POST'], '/', 'PointTransactionController@index')->name('index');
                Route::match(['GET', 'POST'], 'customer/{id}', 'PointTransactionController@index')->name('customer');
            });

            Route::group([
                'prefix' => 'levels',
                'as' => 'levels.',
                'permission' => 'loyalty-points.levels.index',
            ], function (): void {
                Route::resource('', 'LoyaltyLevelController')->parameters(['' => 'level']);
            });
        });

        Route::group([
            'prefix' => 'settings/loyalty-points',
            'as' => 'loyalty-points.settings.',
            'permission' => 'loyalty-points.settings',
        ], function (): void {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'Settings\LoyaltySettingController@edit',
            ]);

            Route::put('/', [
                'as' => 'update',
                'uses' => 'Settings\LoyaltySettingController@update',
            ]);
        });

        Route::group([
            'prefix' => 'loyalty-points/license',
            'as' => 'loyalty-points.license.',
            'permission' => 'loyalty-points.license',
        ], function (): void {
            Route::get('/', [LicenseController::class, 'index'])->name('index');
            Route::post('activate', [LicenseController::class, 'activate'])
                ->name('activate')
                ->middleware('preventDemo');
            Route::post('deactivate', [LicenseController::class, 'deactivate'])
                ->name('deactivate')
                ->middleware('preventDemo');
        });
    });
});
