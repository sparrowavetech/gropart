<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;
use VigStudio\VigAutoTranslations\Http\Controllers\VigAutoTranslationsController;

Route::group(['controller' => VigAutoTranslationsController::class, 'middleware' => ['web', 'core']], function () {
    Route::group([
        'prefix' => BaseHelper::getAdminPrefix() . '/vig-auto-translations',
        'middleware' => 'auth',
        'permission' => 'vig-auto-translations.index',
        'as' => 'vig-auto-translations.',
    ], function () {
        Route::group(['prefix' => 'theme'], function () {
            Route::get('/', [
                'as' => 'theme',
                'uses' => 'getThemeTranslations',
            ]);

            Route::post('/', [
                'as' => 'theme.post',
                'uses' => 'postThemeTranslations',
            ]);

            Route::post('all', [
                'as' => 'theme.post-all',
                'uses' => 'postThemeAllTranslations',
            ]);
        });

        Route::group(['prefix' => 'plugin'], function () {
            Route::get('', [
                'as' => 'plugin',
                'uses' => 'getPluginsTranslations',
            ]);

            Route::post('', [
                'as' => 'plugin.post',
                'uses' => 'postPluginsTranslations',
            ]);

            Route::post('all', [
                'as' => 'plugin.all',
                'uses' => 'postAllPluginsTranslations',
            ]);
        });

        Route::get('auto-translate', [
            'as' => 'auto-translate',
            'uses' => 'getAutoTranslate',
        ]);
    });
});
