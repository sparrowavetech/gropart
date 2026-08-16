<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/india-sms',
    'middleware' => ['api'],
    'namespace' => 'Ashikul\\IndiaSmsGateway\\Http\\Controllers',
], function (): void {
    Route::post('otp/request', 'OtpApiController@request')->name('india-sms.api.otp.request');
    Route::post('otp/verify', 'OtpApiController@verify')->name('india-sms.api.otp.verify');
    Route::post('webhooks/{gateway}', 'WebhookController@handle')->name('india-sms.api.webhooks');
});
