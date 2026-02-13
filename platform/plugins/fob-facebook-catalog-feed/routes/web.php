<?php

use Botble\Base\Facades\AdminHelper;
use FriendsOfBotble\FacebookCatalogFeed\Http\Controllers\FacebookCatalogFeedController;
use Illuminate\Support\Facades\Route;

Route::prefix('facebook-catalog-feed')->name('facebook-catalog-feed.')->group(function () {
    Route::get('feed.xml', [FacebookCatalogFeedController::class, 'index'])->name('feed');
});

AdminHelper::registerRoutes(function () {
    Route::group(['namespace' => 'FriendsOfBotble\FacebookCatalogFeed\Http\Controllers'], function () {
        Route::group(['prefix' => 'ecommerce'], function () {
            Route::prefix('settings')->group(function () {
                Route::get('facebook-catalog-feed', [
                    'as' => 'fob-facebook-catalog-feed.settings',
                    'uses' => 'Settings\FacebookCatalogFeedSettingController@edit',
                ]);

                Route::put('facebook-catalog-feed', [
                    'as' => 'fob-facebook-catalog-feed.settings.update',
                    'uses' => 'Settings\FacebookCatalogFeedSettingController@update',
                    'permission' => 'fob-facebook-catalog-feed.settings',
                ]);
            });
        });
    });
});