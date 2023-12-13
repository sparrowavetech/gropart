<?php

use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers\Fronts'], function () {
    Theme::registerRoutes(function () {
        Route::get('wishlist', [
            'as' => 'public.wishlist',
            'uses' => 'WishlistController@index',
        ]);

        Route::post('wishlist/{productId}', [
            'as' => 'public.wishlist.add',
            'uses' => 'WishlistController@store',
        ])->wherePrimaryKey('productId');

        Route::delete('wishlist/{productId}', [
            'as' => 'public.wishlist.remove',
            'uses' => 'WishlistController@destroy',
        ])->wherePrimaryKey('productId');
    });
});
