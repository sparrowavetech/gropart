<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

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

Route::group(['namespace' => 'Botble\Sms\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
        Route::get('sms', [
            'uses' => 'SmsController@test',
            'as' => 'public.testsms',
        ]);
    });
});

Theme::registerRoutes(function (): void {
    Route::group([
        'namespace' => 'Botble\Sms\Http\Controllers\Fronts',
        'middleware' => ['customer.guest'],
        'as' => 'customer.',
    ], function (): void {
        Route::get('otp/{id}', 'OtpController@otp')->name('otp');
        Route::get('resend/{id}', 'OtpController@resend')->name('resend');
        Route::post('otp', 'OtpController@verifyotp')->name('otp.post');
        Route::post('changePhone', 'OtpController@changePhone')->name('otp.changePhone');
        
        // Override register POST route to intercept and redirect to OTP screen
        if (is_plugin_active('ecommerce') && \Botble\Ecommerce\Facades\EcommerceHelper::isCustomerRegistrationEnabled()) {
             Route::post('register', 'SmsOtpRegisterController@register')->name('register.post');
        }
    });
});
