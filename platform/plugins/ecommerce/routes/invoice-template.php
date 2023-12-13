<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers'], function () {
    AdminHelper::registerRoutes(function () {
        Route::prefix('invoice-template')->name('invoice-template.')->group(function () {
            Route::post('reset', [
                'as' => 'reset',
                'uses' => 'InvoiceTemplateController@reset',
                'permission' => 'ecommerce.invoice-template.index',
                'middleware' => 'preventDemo',
            ]);

            Route::get('preview', [
                'as' => 'preview',
                'uses' => 'InvoiceTemplateController@preview',
                'permission' => 'ecommerce.invoice-template.index',
            ]);
        });
    });
});
