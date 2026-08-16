<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;
use Ashikul\IndiaSmsGateway\Http\Middleware\RequireLicenseActivation;

Route::group([
    'namespace' => 'Ashikul\\IndiaSmsGateway\\Http\\Controllers',
    'middleware' => ['web', 'core'],
], function (): void {
    Route::group([
        'prefix' => BaseHelper::getAdminPrefix(),
        'middleware' => 'auth',
    ], function (): void {
        Route::group(['prefix' => 'india-sms', 'as' => 'india-sms.'], function (): void {
            Route::get('activation', [
                'as' => 'activation.index',
                'uses' => 'ActivationController@index',
                'permission' => 'india-sms.settings',
            ]);
            Route::post('activation', [
                'as' => 'activation.store',
                'uses' => 'ActivationController@store',
                'permission' => 'india-sms.settings',
            ]);
            Route::delete('activation', [
                'as' => 'activation.destroy',
                'uses' => 'ActivationController@destroy',
                'permission' => 'india-sms.settings',
            ]);
            Route::post('activation/refresh', [
                'as' => 'activation.refresh',
                'uses' => 'ActivationController@refresh',
                'permission' => 'india-sms.settings',
            ]);

            Route::group(['middleware' => RequireLicenseActivation::class], function (): void {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'OverviewController@index',
                'permission' => 'india-sms.index',
            ]);
            Route::get('health/{check}', [
                'as' => 'health.show',
                'uses' => 'OverviewController@health',
                'permission' => 'india-sms.index',
            ]);

            Route::get('logs', [
                'as' => 'logs.index',
                'uses' => 'LogsController@index',
                'permission' => 'india-sms.logs.index',
            ]);
            Route::get('logs/{log}', [
                'as' => 'logs.show',
                'uses' => 'LogsController@show',
                'permission' => 'india-sms.logs.index',
            ]);
            Route::delete('logs/{log}', [
                'as' => 'logs.destroy',
                'uses' => 'LogsController@destroy',
                'permission' => 'india-sms.logs.destroy',
            ]);

            Route::get('templates', [
                'as' => 'templates.index',
                'uses' => 'TemplatesController@index',
                'permission' => 'india-sms.templates.index',
            ]);
            Route::get('templates/create', [
                'as' => 'templates.create',
                'uses' => 'TemplatesController@create',
                'permission' => 'india-sms.templates.create',
            ]);
            Route::post('templates', [
                'as' => 'templates.store',
                'uses' => 'TemplatesController@store',
                'permission' => 'india-sms.templates.create',
            ]);
            Route::get('templates/{template}/edit', [
                'as' => 'templates.edit',
                'uses' => 'TemplatesController@edit',
                'permission' => 'india-sms.templates.edit',
            ]);
            Route::put('templates/{template}', [
                'as' => 'templates.update',
                'uses' => 'TemplatesController@update',
                'permission' => 'india-sms.templates.edit',
            ]);
            Route::delete('templates/{template}', [
                'as' => 'templates.destroy',
                'uses' => 'TemplatesController@destroy',
                'permission' => 'india-sms.templates.destroy',
            ]);

            Route::get('gateways', [
                'as' => 'gateways.index',
                'uses' => 'GatewaysController@index',
                'permission' => 'india-sms.gateways.index',
            ]);
            Route::get('gateways/{gateway}', [
                'as' => 'gateways.edit',
                'uses' => 'GatewaysController@edit',
                'permission' => 'india-sms.gateways.edit',
            ]);
            Route::put('gateways/{gateway}', [
                'as' => 'gateways.update',
                'uses' => 'GatewaysController@update',
                'permission' => 'india-sms.gateways.edit',
            ]);
            Route::post('gateways/test/send', [
                'as' => 'gateways.test',
                'uses' => 'GatewaysController@test',
                'permission' => 'india-sms.gateways.test',
            ]);

            Route::get('otp-logs', [
                'as' => 'otps.index',
                'uses' => 'OtpLogsController@index',
                'permission' => 'india-sms.otps.index',
            ]);
            Route::get('admin-notifications', [
                'as' => 'admin-notifications',
                'uses' => 'AdminNotificationsController@index',
                'permission' => 'india-sms.settings',
            ]);
            Route::put('admin-notifications', [
                'as' => 'admin-notifications.update',
                'uses' => 'AdminNotificationsController@update',
                'permission' => 'india-sms.settings',
            ]);

            Route::get('settings', [
                'as' => 'settings',
                'uses' => 'SettingsController@index',
                'permission' => 'india-sms.settings',
            ]);
            Route::put('settings', [
                'as' => 'settings.update',
                'uses' => 'SettingsController@update',
                'permission' => 'india-sms.settings',
            ]);
            });
        });
    });
});

if (class_exists(\Botble\Theme\Facades\Theme::class)) {
    \Botble\Theme\Facades\Theme::registerRoutes(function (): void {
        Route::group([
            'prefix' => 'india-sms',
            'as' => 'india-sms.frontend.',
            'namespace' => 'Ashikul\\IndiaSmsGateway\\Http\\Controllers',
            'middleware' => ['web'],
        ], function (): void {
            Route::post('otp/request', [
                'as' => 'otp.request',
                'uses' => 'FrontendOtpController@request',
            ]);
            Route::post('otp/verify', [
                'as' => 'otp.verify',
                'uses' => 'FrontendOtpController@verify',
            ]);
            Route::post('otp/login', [
                'as' => 'otp.login',
                'uses' => 'FrontendOtpController@login',
            ]);
            Route::post('otp/reset-password', [
                'as' => 'otp.reset-password',
                'uses' => 'FrontendOtpController@resetPassword',
            ]);
        });
    });
}
