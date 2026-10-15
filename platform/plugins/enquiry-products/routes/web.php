<?php
use Botble\Base\Facades\AdminHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;
use SparroWave\EnquiryProducts\Http\Controllers\CatalogController;
use SparroWave\EnquiryProducts\Http\Controllers\EnquiryController;

Theme::registerRoutes(function (): void {
    $base = setting('ep_catalog_slug', 'equipments-enquiry-products');
    if (is_plugin_active('marketplace')) {
    Route::get($base . '/stores/{storeSlug}/search/suggestions', [CatalogController::class, 'suggestions'])->middleware('throttle:60,1')->name('public.enquiry-products.store-suggestions');
    Route::get($base . '/stores/{storeSlug}', [CatalogController::class, 'index'])->name('public.enquiry-products.store');
    }
    Route::get($base, [CatalogController::class, 'index'])->name('public.enquiry-products.index');
    Route::get($base . '/product/{id}', [CatalogController::class, 'legacyShow'])->whereNumber('id')->name('public.enquiry-products.legacy');
    Route::get($base . '/search/suggestions', [CatalogController::class, 'suggestions'])->middleware('throttle:60,1')->name('public.enquiry-products.suggestions');
    Route::get($base . '/{slug}/enquiry', [CatalogController::class, 'enquiryForm'])->name('public.enquiry-products.form');
    Route::get($base . '/{slug}', [CatalogController::class, 'show'])->name('public.enquiry-products.show');
    Route::post($base . '/request/{id}', [CatalogController::class, 'submit'])->whereNumber('id')->middleware('throttle:6,1')->name('public.enquiry-products.submit');
    Route::get($base . '/success/{token}', [CatalogController::class, 'success'])->where('token', '[a-f0-9]{64}')->name('public.enquiry-products.success');
    foreach (array_diff(array_unique(array_merge(['equipments-enquiry-products'], json_decode(setting('ep_previous_slugs', '[]'), true) ?: [])), [$base]) as $previous) {
        Route::get($previous . '/{path?}', function (\Illuminate\Http\Request $request, ?string $path = null) use ($base) {
            $url = url($base . ($path ? '/' . $path : ''));
            return redirect($url . ($request->getQueryString() ? '?' . $request->getQueryString() : ''), 301);
        })->where('path', '.*');
        Route::post($previous . '/request/{id}', [CatalogController::class, 'submit'])->whereNumber('id')->middleware('throttle:6,1');
    }
    if (is_plugin_active('marketplace')) {
        Route::middleware(['customer', 'vendor'])->prefix('customer/equipment-enquiries')->name('enquiry-products.vendor.')->group(function (): void {
            Route::match(['GET', 'POST'], '/', [EnquiryController::class, 'vendorIndex'])->name('index');
            Route::get('{id}', [EnquiryController::class, 'vendorShow'])->whereNumber('id')->name('show');
            Route::get('{id}/attachment', [EnquiryController::class, 'vendorAttachment'])->whereNumber('id')->name('attachment');
        });
    }
});
AdminHelper::registerRoutes(function (): void {
    Route::prefix('enquiry-products')->name('enquiry-products.')->group(function (): void {
        if (is_plugin_active('marketplace')) {
            Route::match(['GET', 'POST'], 'sellers', [EnquiryController::class, 'sellers'])->name('sellers')->permission('enquiry-products.index');
        }
        Route::get('settings', [\SparroWave\EnquiryProducts\Http\Controllers\SettingsController::class, 'edit'])->name('settings')->permission('enquiry-products.settings');
        Route::put('settings', [\SparroWave\EnquiryProducts\Http\Controllers\SettingsController::class, 'update'])->name('settings.update')->permission('enquiry-products.settings');
        Route::match(['GET', 'POST'], '/', [EnquiryController::class, 'index'])->name('index')->permission('enquiry-products.index');
        Route::delete('{id}', [EnquiryController::class, 'destroy'])->whereNumber('id')->name('destroy')->permission('enquiry-products.destroy');
        Route::get('{id}', [EnquiryController::class, 'show'])->whereNumber('id')->name('show')->permission('enquiry-products.index');
        Route::post('{id}', [EnquiryController::class, 'update'])->whereNumber('id')->name('update')->permission('enquiry-products.edit');
        Route::get('{id}/attachment', [EnquiryController::class, 'attachment'])->whereNumber('id')->name('attachment')->permission('enquiry-products.index');
    });
});
