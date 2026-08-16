<?php

use Botble\MobileCommandBar\Http\Controllers\MobileCommandBarAssetController;
use Botble\MobileCommandBar\Http\Controllers\Settings\MobileCommandBarSettingController;
use Illuminate\Support\Facades\Route;

/**
 * Public, unauthenticated route that streams this plugin's own CSS/JS/image
 * files directly from its `public/` folder. This is used instead of relying
 * only on copying those files into the application's public webroot, which
 * can silently fail on hosts with restrictive file permissions (shared
 * hosting panels, read-only deployments, etc.) and would otherwise leave
 * the admin screen and the front-end bar completely unstyled/non-functional.
 */
try {
    Route::get('mobile-command-bar/assets/{path}', [MobileCommandBarAssetController::class, 'show'])
        ->where('path', '.*')
        ->name('mobile-command-bar.assets');
} catch (\Throwable) {
}

/**
 * Apply Botble's `permission()` route macro when available; otherwise
 * leave the route as-is so registration never fails outright. Access
 * to the settings page is additionally guarded inside the controller
 * via the standard admin authentication middleware.
 */
$withPermission = static function ($route, string $flag) {
    if ($route && method_exists($route, 'permission')) {
        return $route->permission($flag);
    }

    return $route;
};

$registerRoutes = function () use ($withPermission) {
    Route::group([
        'namespace' => 'Botble\MobileCommandBar\Http\Controllers\Settings',
        'prefix' => 'mobile-command-bar',
        'as' => 'mobile-command-bar.',
    ], function () use ($withPermission) {
        $withPermission(
            Route::get('settings', [MobileCommandBarSettingController::class, 'edit'])->name('settings'),
            'mobile-command-bar.settings'
        );

        $withPermission(
            Route::put('settings', [MobileCommandBarSettingController::class, 'update'])->name('settings.update'),
            'mobile-command-bar.settings'
        );

        $withPermission(
            Route::post('settings/reset', [MobileCommandBarSettingController::class, 'reset'])->name('settings.reset'),
            'mobile-command-bar.settings'
        );
    });
};

if (class_exists(\Botble\Base\Facades\AdminHelper::class)) {
    \Botble\Base\Facades\AdminHelper::registerRoutes($registerRoutes);
} else {
    // Fallback for setups where the AdminHelper route wrapper is not
    // available: register the same routes under the standard admin
    // prefix with the default auth middleware.
    Route::group([
        'middleware' => ['web', 'auth'],
        'prefix' => config('core.base.general.admin_dir', 'admin'),
    ], $registerRoutes);
}
