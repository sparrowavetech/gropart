<?php

use Botble\Base\Facades\AdminHelper;
use Botble\ContentInjector\Http\Controllers\ContentInjectorController;
use Illuminate\Support\Facades\Route;
use Botble\Theme\Facades\Theme;

AdminHelper::registerRoutes(function () {
    Route::group(['prefix' => 'contentinjectors', 'as' => 'contentinjector.'], function () {
        Route::resource('', ContentInjectorController::class)->parameters(['' => 'contentinjector']);
    });
});