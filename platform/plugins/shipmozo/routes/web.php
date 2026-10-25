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
                    'permission' => 'shipmozo.shipments.create',
                ]);

                Route::get('rates/{id}', [
                    'as' => 'rates',
                    'uses' => 'getRates',
                ]);

                Route::post('update-rate/{id}', [
                    'as' => 'update-rate',
                    'uses' => 'updateRate',
                    'permission' => 'shipmozo.rates.update',
                ]);

                Route::get('view-logs/{file}', [
                    'as' => 'view-log',
                    'uses' => 'ShipmozoLogController@show',
                    'permission' => 'shipmozo.logs.show',
                ]);

                if (is_plugin_active('marketplace')) {
                    Route::post('warehouses/create-from-store/{storeId}', [
                        'as' => 'warehouses.create-from-store',
                        'uses' => 'createWarehouseFromStore',
                        'permission' => 'marketplace.store.edit',
                    ]);
                }
            });

            Route::group(['prefix' => 'logs', 'as' => 'logs.', 'permission' => 'shipmozo.logs.index'], function (): void {
                Route::controller('ShipmozoLogController')->group(function (): void {
                    Route::get('/', ['as' => 'index', 'uses' => 'index']);
                    Route::get('{file}', ['as' => 'show', 'uses' => 'show', 'permission' => 'shipmozo.logs.show']);
                    Route::get('{file}/download', ['as' => 'download', 'uses' => 'download', 'permission' => 'shipmozo.logs.show']);
                    Route::delete('{file}', ['as' => 'destroy', 'uses' => 'destroy', 'permission' => 'shipmozo.logs.destroy']);
                });
            });

            Route::group(['prefix' => 'settings', 'as' => 'settings.'], function (): void {
                Route::post('update', [
                    'as' => 'update',
                    'uses' => 'ShipmozoSettingController@update',
                    'middleware' => 'preventDemo',
                    'permission' => 'shipmozo.settings',
                ]);
            });
        });

        if (is_plugin_active('marketplace')) {
            Route::group([
                'prefix' => 'marketplace/shipmozo-warehouses',
                'as' => 'marketplace.shipmozo-warehouses.',
                'permission' => 'shipmozo.warehouses.index',
            ], function (): void {
                Route::controller('ShipmozoWarehouseController')->group(function (): void {
                    Route::get('/', ['as' => 'index', 'uses' => 'index']);
                    Route::get('{storeId}/edit', ['as' => 'edit', 'uses' => 'edit', 'permission' => 'shipmozo.warehouses.edit']);
                    Route::match(['PUT', 'POST'], '{storeId}', ['as' => 'update', 'uses' => 'update', 'permission' => 'shipmozo.warehouses.edit']);
                    Route::delete('{storeId}', ['as' => 'destroy', 'uses' => 'destroy', 'permission' => 'shipmozo.warehouses.destroy']);
                    Route::post('sync-all', ['as' => 'sync-all', 'uses' => 'syncAll', 'permission' => 'shipmozo.warehouses.edit']);
                });
            });
        }

        Route::group([
            'prefix' => 'shipmozo/ndr',
            'as' => 'shipmozo.ndr.',
            'permission' => 'shipmozo.ndr.index',
        ], function (): void {
            Route::controller('ShipmozoNdrController')->group(function (): void {
                Route::get('/', ['as' => 'index', 'uses' => 'index']);
                Route::post('{awbNumber}/action', [
                    'as' => 'action',
                    'uses' => 'action',
                    'permission' => 'shipmozo.ndr.action',
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
    'middleware' => ['api', 'throttle:60,1', 'shipmozo.webhook'],
    'as' => 'shipmozo.',
], function (): void {
    Route::controller('ShipmozoWebhookController')->group(function (): void {
        Route::post('webhooks', [
            'uses' => 'index',
            'as' => 'webhooks',
        ]);
    });
});

Route::group(['namespace' => 'SparroWave\Shipmozo\Http\Controllers\Fronts', 'middleware' => ['web', 'core', 'throttle:30,1']], function () {
    Route::get('shipmozo/public/labels/{awbNumber}', [
        'as' => 'shipmozo.public.label',
        'uses' => 'ShipmozoPublicController@label',
    ]);

    Route::get('shipmozo/public/tracking', [
        'as' => 'shipmozo.public.tracking',
        'uses' => 'ShipmozoPublicController@tracking',
    ]);

    Route::get('shipments/shipmozo/check-pincode', [
        'as' => 'ecommerce.shipments.shipmozo.check-pincode',
        'uses' => 'ShipmozoPublicController@checkPincode',
    ]);
});
