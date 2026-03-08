<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'SparroWave\Shipmozo\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group([
            'prefix' => 'shipments/shipmozo',
            'as' => 'ecommerce.shipments.shipmozo.',
            'permission' => 'ecommerce.shipments.index',
        ], function (): void {
            Route::controller('ShipmozoController')->group(function (): void {
                Route::get('show/{id}', [
                    'as' => 'show',
                    'uses' => 'show',
                ]);

                Route::post('transactions/create/{id}', [
                    'as' => 'transactions.create',
                    'uses' => 'createTransaction',
                    'permission' => 'ecommerce.shipments.edit',
                ]);

                Route::get('rates/{id}', [
                    'as' => 'rates',
                    'uses' => 'getRates',
                ]);

                Route::post('update-rate/{id}', [
                    'as' => 'update-rate',
                    'uses' => 'updateRate',
                    'permission' => 'ecommerce.shipments.edit',
                ]);

                Route::get('view-logs/{file}', [
                    'as' => 'view-log',
                    'uses' => 'viewLog',
                ]);

                Route::get('warehouses/create-from-store/{storeId}', [
                    'as' => 'warehouses.create-from-store',
                    'uses' => 'createWarehouseFromStore',
                    'permission' => 'marketplace.store.edit',
                ]);
            });


            Route::group(['prefix' => 'settings', 'as' => 'settings.'], function (): void {
                Route::post('update', [
                    'as' => 'update',
                    'uses' => 'ShipmozoSettingController@update',
                    'middleware' => 'preventDemo',
                    'permission' => 'shipping_methods.index',
                ]);
            });
        });

        Route::group([
            'prefix' => 'shipmozo/ndr',
            'as' => 'shipmozo.ndr.',
            'permission' => 'orders.index',
        ], function (): void {
            Route::controller('ShipmozoNdrController')->group(function (): void {
                Route::get('/', [
                    'as' => 'index',
                    'uses' => 'index',
                ]);
                Route::post('{awbNumber}/action', [
                    'as' => 'action',
                    'uses' => 'action',
                ]);
            });
        });
    });

    if (is_plugin_active('marketplace')) {
        Theme::registerRoutes(function (): void {
            Route::group([
                'prefix' => 'vendor',
                'as' => 'marketplace.vendor.',
                'middleware' => ['vendor'],
            ], function (): void {
                Route::group(['prefix' => 'orders', 'as' => 'orders.'], function (): void {
                    Route::group(['prefix' => 'shipmozo', 'as' => 'shipmozo.'], function (): void {
                        Route::controller('ShipmozoController')->group(function (): void {
                            Route::get('show/{id}', [
                                'as' => 'show',
                                'uses' => 'show',
                            ]);

                            Route::post('transactions/create/{id}', [
                                'as' => 'transactions.create',
                                'uses' => 'createTransaction',
                            ]);

                            Route::get('rates/{id}', [
                                'as' => 'rates',
                                'uses' => 'getRates',
                            ]);

                            Route::post('update-rate/{id}', [
                                'as' => 'update-rate',
                                'uses' => 'updateRate',
                            ]);
                        });
                    });
                });
            });
        });
    }
});

Route::group([
    'namespace' => 'SparroWave\Shipmozo\Http\Controllers',
    'prefix' => 'shipmozo',
    'middleware' => ['api', 'shipmozo.webhook'],
    'as' => 'shipmozo.',
], function (): void {
    Route::controller('ShipmozoWebhookController')->group(function (): void {
        Route::post('webhooks', [
            'uses' => 'index',
            'as' => 'webhooks',
        ]);
    });
});

Route::group(['namespace' => 'SparroWave\Shipmozo\Http\Controllers\Fronts', 'middleware' => ['web', 'core']], function () {
    Route::get('shipmozo/public/tracking', [
        'as'   => 'shipmozo.public.tracking',
        'uses' => 'ShipmozoPublicController@tracking',
    ]);

    Route::get('shipments/shipmozo/check-pincode', [
        'as'   => 'ecommerce.shipments.shipmozo.check-pincode',
        'uses' => 'ShipmozoPublicController@checkPincode',
    ]);
});
