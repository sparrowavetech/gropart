<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Sms\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'sms', 'as' => 'sms.'], function () {
            Route::get('settings', [
                'as' => 'settings',
                'uses' => 'SmsController@getSettings',
            ]);

            Route::post('settings', [
                'as' => 'settings.post',
                'uses' => 'SmsController@postSettings',
                'permission' => 'sms.settings',
            ]);

            Route::match(['GET', 'POST'], 'delivery-reports/data', [
                'as' => 'delivery-reports.data',
                'uses' => 'SmsController@deliveryReportsData',
                'permission' => 'sms.delivery-reports.index',
            ]);

            Route::get('delivery-reports', [
                'as' => 'delivery-reports.index',
                'uses' => 'SmsController@deliveryReports',
                'permission' => 'sms.delivery-reports.index',
            ]);

            Route::get('delivery-reportses', function () {
                return redirect()->route('sms.delivery-reports.index');
            });

            Route::resource('', 'SmsController')->parameters(['' => 'sms']);
            Route::delete('items/destroy', [
                'as'         => 'deletes',
                'uses'       => 'SmsController@deletes',
                'permission' => 'sms.destroy',
            ]);

        });
    });

});

Theme::registerRoutes(function (): void {
    if (! is_plugin_active('ecommerce')) {
        return;
    }

    Route::group([
        'namespace' => 'Botble\Sms\Http\Controllers\Fronts',
        'middleware' => ['customer.guest'],
        'as' => 'customer.',
    ], function (): void {
        Route::get('otp/{id}', 'OtpController@otp')->name('otp');
        Route::get('resend/{id}', 'OtpController@resend')->name('resend');
        Route::post('otp', 'OtpController@verifyotp')->name('otp.post');
        Route::post('changePhone', 'OtpController@changePhone')->name('otp.changePhone');
        Route::get('login/otp', 'LoginOtpController@showRequestForm')->name('login.otp');
        Route::post('login/otp', 'LoginOtpController@sendOtp')->name('login.otp.send');
        Route::get('login/otp/verify/{id}', 'LoginOtpController@showVerifyForm')->name('login.otp.verify');
        Route::post('login/otp/verify', 'LoginOtpController@verify')->name('login.otp.verify.post');
        
        // Override register POST route to intercept and redirect to OTP screen
        if (\Botble\Ecommerce\Facades\EcommerceHelper::isCustomerRegistrationEnabled()) {
             Route::post('register', 'SmsOtpRegisterController@register')->name('register.post');
        }
    });
});
