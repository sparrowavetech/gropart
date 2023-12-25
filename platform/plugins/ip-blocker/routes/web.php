<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'ArchiElite\IpBlocker\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::resource('settings/ip-blocker', 'IpBlockerController', ['names' => 'ip-blocker'])->only(['destroy']);

        Route::group(['prefix' => 'settings/ip-blocker'], function () {
            Route::match(['GET', 'POST'], '', [
                'as' => 'ip-blocker.settings',
                'uses' => 'IpBlockerController@settings',
            ]);

            Route::post('update', [
                'as' => 'ip-blocker.settings.update',
                'uses' => 'IpBlockerController@updateSettings',
                'permission' => 'ip-blocker.settings',
            ]);

            Route::delete('deletes', [
                'as' => 'ip-blocker.deletes',
                'uses' => 'IpBlockerController@deletes',
                'permission' => 'ip-blocker.destroy',
            ]);

            Route::get('empty', [
                'as' => 'ip-blocker.empty',
                'uses' => 'IpBlockerController@deleteAll',
                'permission' => 'ip-blocker.destroy',
            ]);

            Route::post('check-secret-key', [
                'as' => 'ip-blocker.settings.check-secret-key',
                'uses' => 'IpBlockerController@checkSecretKey',
                'permission' => 'ip-blocker.settings',
            ]);

            Route::post('update-available-countries', [
                'as' => 'ip-blocker.settings.available-countries',
                'uses' => 'IpBlockerController@availableCountries',
                'permission' => 'ip-blocker.settings',
            ]);
        });
    });
});