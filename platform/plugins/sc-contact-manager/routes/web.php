<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Skillcraft\ContactManager\Http\Controllers', 'middleware' => ['web', 'core']], function () {

    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {

        Route::group(['prefix' => 'contact-managers', 'as' => 'contact-manager.'], function () {
            Route::resource('', 'ContactManagerController')->parameters(['' => 'contact-manager']);
        });

        Route::group(['prefix' => 'contact-tags', 'as' => 'contact-tag.'], function () {
            Route::resource('', 'ContactTagController')->parameters(['' => 'contact-tag']);
        
            Route::get('json', [
                'as'         => 'all',
                'uses'       => 'ContactTagController@getAllTags',
                'permission' => 'tags.index',
            ]);
        });
        
        Route::group(['prefix' => 'contact-groups', 'as' => 'contact-group.'], function () {
            Route::resource('', 'ContactGroupController')->parameters(['' => 'contact-group']);
        });

        Route::group(['prefix' => 'settings'], function () {
            Route::get('contact-manager', [
                'as' => 'contact-manager.settings',
                'uses' => 'Settings\ContactManagerSettingController@edit',
                'permission' => 'contact-manager.settings',
            ]);

            Route::put('contact-manager', [
                'as' => 'contact.settings.update',
                'uses' => 'Settings\ContactManagerSettingController@update',
                'permission' => 'contact-manager.settings',
            ]);
        });
    });
});
