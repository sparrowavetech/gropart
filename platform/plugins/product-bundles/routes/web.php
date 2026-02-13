<?php

use Botble\Base\Facades\BaseHelper;
use Botble\ProductBundles\Http\Controllers\ExportBundleController;
use Botble\ProductBundles\Http\Controllers\ImportBundleController;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Botble\\ProductBundles\\Http\\Controllers',
    'middleware' => ['web'],
], function () {
    // Bundle detail page (treat bundle as a product-like entity)
    // Bind bundle by slug for public pages.
    Route::get('bundles/{bundle:slug}', [
        'as' => 'product-bundles.show',
        'uses' => 'BundlePublicController@show',
    ]);

    // Frontend
    Route::get('product-bundles/render/{productId}', [
        'as' => 'product-bundles.render',
        'uses' => 'BundlePublicController@renderForProduct',
    ]);

    Route::post('product-bundles/add-to-cart', [
        'as' => 'product-bundles.add_to_cart',
        'uses' => 'BundlePublicController@addToCart',
    ]);
});

Route::group([
    'namespace' => 'Botble\\ProductBundles\\Http\\Controllers',
    'middleware' => ['web', 'core', 'auth'],
    'prefix' => BaseHelper::getAdminPrefix(),
], function () {
    Route::group(['prefix' => 'ecommerce/bundles'], function () {
        Route::get('ajax/products', [
            'as' => 'product-bundles.ajax.products',
            'uses' => 'BundleController@ajaxProducts',
            'permission' => 'product-bundles.index',
        ]);
        Route::post('ajax/price-preview', [
            'as' => 'product-bundles.ajax.price-preview',
            'uses' => 'BundleController@ajaxPricePreview',
            'permission' => 'product-bundles.index',
        ]);

        Route::match(['GET', 'POST'], '', [
            'as' => 'product-bundles.index',
            'uses' => 'BundleController@index',
            'permission' => 'product-bundles.index',
        ]);

        Route::get('create', [
            'as' => 'product-bundles.create',
            'uses' => 'BundleController@create',
            'permission' => 'product-bundles.create',
        ]);

        Route::post('create', [
            'as' => 'product-bundles.store',
            'uses' => 'BundleController@store',
            'permission' => 'product-bundles.create',
        ]);

        Route::get('{bundle}/edit', [
            'as' => 'product-bundles.edit',
            'uses' => 'BundleController@edit',
            'permission' => 'product-bundles.edit',
        ]);

        Route::post('{bundle}/edit', [
            'as' => 'product-bundles.update',
            'uses' => 'BundleController@update',
            'permission' => 'product-bundles.edit',
        ]);

        Route::delete('{bundle}/delete', [
            'as' => 'product-bundles.destroy',
            'uses' => 'BundleController@destroy',
            'permission' => 'product-bundles.destroy',
        ]);
    });

    Route::prefix('tools/data-synchronize')->name('tools.data-synchronize.')->group(function (): void {
        Route::prefix('export')->name('export.')->group(function (): void {
            Route::group(['prefix' => 'bundles', 'as' => 'bundles.', 'permission' => 'product-bundles.export'], function (): void {
                Route::get('/', [ExportBundleController::class, 'index'])->name('index');
                Route::post('/', [ExportBundleController::class, 'store'])->name('store');
            });
        });

        Route::prefix('import')->name('import.')->group(function (): void {
            Route::group(['prefix' => 'bundles', 'as' => 'bundles.', 'permission' => 'product-bundles.import'], function (): void {
                Route::get('/', [ImportBundleController::class, 'index'])->name('index');
                Route::post('/', [ImportBundleController::class, 'import'])->name('store');
                Route::post('validate', [ImportBundleController::class, 'validateData'])->name('validate');
                Route::post('download-example', [ImportBundleController::class, 'downloadExample'])->name('download-example');
            });
        });
    });
});
