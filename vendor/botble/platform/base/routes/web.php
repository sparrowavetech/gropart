<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Base\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Base\Http\Controllers'], function () {
    AdminHelper::registerRoutes(function () {
        Route::group(['prefix' => 'system'], function () {
            Route::get('', [
                'as' => 'system.index',
                'uses' => 'SystemController@getIndex',
                'permission' => 'core.system',
            ]);
        });

        Route::group(['prefix' => 'system/info'], function () {
            Route::match(['GET', 'POST'], '', [
                'as' => 'system.info',
                'uses' => 'SystemController@getInfo',
                'permission' => 'superuser',
            ]);
        });

        Route::group(['prefix' => 'system/cache'], function () {
            Route::get('', [
                'as' => 'system.cache',
                'uses' => 'SystemController@getCacheManagement',
                'permission' => 'superuser',
            ]);

            Route::post('clear', [
                'as' => 'system.cache.clear',
                'uses' => 'SystemController@postClearCache',
                'permission' => 'superuser',
                'middleware' => 'preventDemo',
            ]);
        });

        Route::post('membership/authorize', [
            'as' => 'membership.authorize',
            'uses' => 'SystemController@postAuthorize',
            'permission' => false,
        ]);

        Route::get('menu-items-count', [
            'as' => 'menu-items-count',
            'uses' => 'SystemController@getMenuItemsCount',
            'permission' => false,
        ]);

        Route::get('system/check-update', [
            'as' => 'system.check-update',
            'uses' => 'SystemController@getCheckUpdate',
            'permission' => 'superuser',
        ]);

        Route::get('system/updater', [
            'as' => 'system.updater',
            'uses' => 'SystemController@getUpdater',
            'permission' => 'superuser',
        ]);

        Route::post('system/updater', [
            'as' => 'system.updater.post',
            'uses' => 'SystemController@postUpdater',
            'permission' => 'superuser',
            'middleware' => 'preventDemo',
        ]);

        Route::get('system/cleanup', [
            'as' => 'system.cleanup',
            'uses' => 'SystemController@getCleanup',
            'permission' => 'superuser',
        ]);

        Route::post('system/cleanup', [
            'as' => 'system.cleanup.process',
            'uses' => 'SystemController@getCleanup',
            'permission' => 'superuser',
            'middleware' => 'preventDemo',
        ]);

        Route::post('system/debug-mode/turn-off', [
            'as' => 'system.debug-mode.turn-off',
            'uses' => 'DebugModeController@postTurnOff',
            'permission' => 'superuser',
            'middleware' => 'preventDemo',
        ]);

        Route::get('unlicensed', [
            'as' => 'unlicensed',
            'uses' => 'UnlicensedController@index',
            'permission' => false,
        ]);

        Route::post('unlicensed', [
            'as' => 'unlicensed.skip',
            'uses' => 'UnlicensedController@postSkip',
            'permission' => false,
        ]);

        Route::group(['prefix' => 'notifications', 'as' => 'notifications.', 'permission' => false], function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'NotificationController@index',
            ]);

            Route::delete('{id}', [
                'as' => 'destroy',
                'uses' => 'NotificationController@destroy',
            ])->wherePrimaryKey();

            Route::get('read-notification/{id}', [
                'as' => 'read-notification',
                'uses' => 'NotificationController@read',
            ])->wherePrimaryKey();

            Route::put('read-all-notification', [
                'as' => 'read-all-notification',
                'uses' => 'NotificationController@readAll',
            ]);

            Route::delete('destroy-all-notification', [
                'as' => 'destroy-all-notification',
                'uses' => 'NotificationController@deleteAll',
            ]);

            Route::get('count-unread', [
                'as' => 'count-unread',
                'uses' => 'NotificationController@countUnread',
            ]);
        });

        Route::get('toggle-theme-mode', [
            'as' => 'toggle-theme-mode',
            'uses' => 'ToggleThemeModeController@__invoke',
            'permission' => false,
        ]);

        Route::get('search', [SearchController::class, '__invoke'])->name('core.global-search');
    });
});
