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
                'permission' => 'sms.settings',
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
            })->permission('sms.delivery-reports.index');

            Route::get('logs', [
                'as' => 'logs.index',
                'uses' => 'SmsLogController@index',
                'permission' => 'sms.logs.index',
            ]);
            Route::get('logs/{file}', [
                'as' => 'logs.show',
                'uses' => 'SmsLogController@show',
                'permission' => 'sms.logs.show',
            ]);

            Route::match(['GET', 'POST'], '', ['as' => 'index', 'uses' => 'SmsController@index', 'permission' => 'sms.index']);
            Route::get('create', ['as' => 'create', 'uses' => 'SmsController@create', 'permission' => 'sms.create']);
            Route::post('create', ['as' => 'store', 'uses' => 'SmsController@store', 'permission' => 'sms.create']);
            Route::get('{sms}/edit', ['as' => 'edit', 'uses' => 'SmsController@edit', 'permission' => 'sms.edit']);
            Route::match(['POST', 'PUT', 'PATCH'], '{sms}/edit', ['as' => 'edit.post', 'uses' => 'SmsController@update', 'permission' => 'sms.edit']);
            Route::match(['PUT', 'PATCH'], '{sms}', ['as' => 'update', 'uses' => 'SmsController@update', 'permission' => 'sms.edit']);
            Route::delete('{sms}', ['as' => 'destroy', 'uses' => 'SmsController@destroy', 'permission' => 'sms.destroy']);
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
        Route::post('register/phone-availability', 'RegistrationPhoneController@check')
            ->middleware('throttle:10,1')
            ->name('register.phone-availability');
        Route::get('register/check-email', 'SmsOtpRegisterController@showEmailPending')->name('register.email-pending');
        Route::get('otp/verify', 'OtpController@otp')->name('otp');
        Route::get('otp/{id}', function (string $id) {
            abort_unless((string) app(\Botble\Sms\Supports\OtpChallenge::class)->customerId('registration') === $id, 404);

            return redirect()->route('customer.otp');
        })->whereNumber('id');
        Route::post('otp/resend', 'OtpController@resend')->middleware('throttle:3,10')->name('resend');
        Route::post('otp', 'OtpController@verifyotp')->middleware('throttle:10,1')->name('otp.post');
        Route::post('changePhone', 'OtpController@changePhone')->middleware('throttle:3,10')->name('otp.changePhone');
        Route::get('login/otp', 'LoginOtpController@showRequestForm')->name('login.otp');
        Route::post('login/otp', 'LoginOtpController@sendOtp')->middleware('throttle:5,1')->name('login.otp.send');
        Route::get('login/otp/verify', 'LoginOtpController@showVerifyForm')->name('login.otp.verify');
        Route::get('login/otp/verify/{id}', function (string $id) {
            abort_unless((string) app(\Botble\Sms\Supports\OtpChallenge::class)->customerId('login') === $id, 404);

            return redirect()->route('customer.login.otp.verify');
        })->whereNumber('id');
        Route::post('login/otp/verify', 'LoginOtpController@verify')->middleware('throttle:10,1')->name('login.otp.verify.post');
        
        // Override register POST route to intercept and redirect to OTP screen
        if (\Botble\Ecommerce\Facades\EcommerceHelper::isCustomerRegistrationEnabled()) {
             Route::post('register', 'SmsOtpRegisterController@register')->middleware('throttle:5,1')->name('register.post');
        }
    });
});
