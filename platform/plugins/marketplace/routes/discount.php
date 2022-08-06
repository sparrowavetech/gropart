<?php

Route::group([
    'namespace'  => 'Botble\Marketplace\Http\Controllers\Fronts',
    'middleware' => ['web', 'core'],
], function () {
    Route::group(['prefix' => 'vendor', 'as' => 'marketplace.vendor.', 'middleware' => ['vendor']], function () {
        Route::group(['prefix' => 'coupons', 'as' => 'discounts.'], function () {
            Route::resource('', 'DiscountController')->parameters(['' => 'coupon'])->except(['edit', 'update']);

            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'DiscountController@deletes',
            ]);

            Route::post('generate-coupon', [
                'as'         => 'generate-coupon',
                'uses'       => 'DiscountController@postGenerateCoupon',
            ]);
        });
    });
});
