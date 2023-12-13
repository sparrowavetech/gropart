<?php

use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers\Fronts'], function () {
    Theme::registerRoutes(function () {
        Route::get('compare', [
            'as' => 'public.compare',
            'uses' => 'CompareController@index',
        ]);

        Route::post('compare/{productId}', [
            'as' => 'public.compare.add',
            'uses' => 'CompareController@store',
        ])->wherePrimaryKey('productId');

        Route::delete('compare/{productId}', [
            'as' => 'public.compare.remove',
            'uses' => 'CompareController@destroy',
        ])->wherePrimaryKey('productId');
    });
});
