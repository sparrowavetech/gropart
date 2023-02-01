<?php

Route::group(['namespace' => 'Botble\Pickrr\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group([
            'prefix' => 'shipments/pickrr',
            'as' => 'ecommerce.shipments.pickrr.',
            'permission' => 'ecommerce.shipments.index',
        ], function () {
            Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
                Route::post('update', [
                    'as' => 'update',
                    'uses' => 'PickrrController@postSettings',
                    'middleware' => 'preventDemo',
                    'permission' => 'shipping_methods.index',
                ]);
            });
        });
    });

});
