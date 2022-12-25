<?php

Route::group(['namespace' => 'Botble\Sms\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'sms', 'as' => 'sms.'], function () {
            Route::resource('', 'SmsController')->parameters(['' => 'sms']);
            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'SmsController@deletes',
                'permission' => 'sms.destroy',
            ]);
            Route::get('settings', [
                'as' => 'settings',
                'uses' => 'SmsController@getSettings',
            ]);

            Route::post('settings', [
                'as' => 'settings.post',
                'uses' => 'SmsController@postSettings',
                'permission' => 'sms.settings',
            ]);

        });
    });

});
Route::get('settings', [
    'as' => 'ecommerce.settings',
    'uses' => 'EcommerceController@getSettings',
]);

Route::post('settings', [
    'as' => 'ecommerce.settings.post',
    'uses' => 'EcommerceController@postSettings',
    'permission' => 'ecommerce.settings',
]);

Route::group(['namespace' => 'Botble\Sms\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
        Route::get('sms', [
            'uses' => 'SmsController@test',
            'as' => 'public.testsms',
        ]);
    });
});
