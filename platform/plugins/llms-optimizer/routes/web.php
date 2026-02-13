<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Shaqi\LlmsOptimizer\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'settings/llms-optimizer', 'as' => 'llms-optimizer.settings.'], function (): void {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'Settings\LlmsOptimizerController@edit',
            ]);

            Route::put('/', [
                'as' => 'update',
                'uses' => 'Settings\LlmsOptimizerController@update',
                'permission' => 'llms-optimizer.settings',
            ]);

            Route::post('preview', [
                'as' => 'preview',
                'uses' => 'Settings\LlmsOptimizerController@preview',
                'permission' => 'llms-optimizer.settings',
            ]);

            Route::post('regenerate', [
                'as' => 'regenerate',
                'uses' => 'Settings\LlmsOptimizerController@regenerate',
                'permission' => 'llms-optimizer.settings',
            ]);

            Route::post('clear-cache', [
                'as' => 'clear-cache',
                'uses' => 'Settings\LlmsOptimizerController@clearCache',
                'permission' => 'llms-optimizer.settings',
            ]);
        });
    });

    if (defined('THEME_MODULE_SCREEN_NAME')) {
        Theme::registerRoutes(function (): void {
            Route::get('llms.txt', [
                'as' => 'public.llms-txt',
                'uses' => 'LlmsTextController@index',
            ]);
        });
    }
});

