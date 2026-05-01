<?php

namespace Botble\EcommerceWholesale;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public const VERSION = '1.0.9';

    public static function activate(): void
    {
        self::clearCache();
    }

    public static function deactivate(): void
    {
        self::clearCache();
    }

    public static function remove(): void
    {
        Schema::dropIfExists('ws_group_pricing_rules');
        Schema::dropIfExists('ws_product_group_access');
        Schema::dropIfExists('ws_product_visibility');
        Schema::dropIfExists('ws_product_moq');
        Schema::dropIfExists('ws_wholesale_applications');
        Schema::dropIfExists('ws_customer_group_assignments');
        Schema::dropIfExists('ws_customer_groups');

        Setting::delete([
            'wholesale_enabled',
            'wholesale_require_approval',
            'wholesale_show_prices_to_guests',
            'wholesale_allow_multiple_groups',
            'wholesale_discount_resolution',
            'wholesale_enable_for_guests',
            'wholesale_default_group',
        ]);

        self::clearCache();
    }

    protected static function clearCache(): void
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
        } catch (\Exception) {
            $cachePaths = [
                base_path('bootstrap/cache/config.php'),
                base_path('bootstrap/cache/routes-v7.php'),
                base_path('bootstrap/cache/services.php'),
                base_path('bootstrap/cache/packages.php'),
            ];

            foreach ($cachePaths as $path) {
                if (File::exists($path)) {
                    File::delete($path);
                }
            }

            $storageDirs = [
                storage_path('framework/cache/data'),
                storage_path('framework/views'),
            ];

            foreach ($storageDirs as $dir) {
                if (File::isDirectory($dir)) {
                    File::cleanDirectory($dir);
                }
            }
        }
    }
}
