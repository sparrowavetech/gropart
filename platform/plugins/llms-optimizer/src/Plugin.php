<?php

namespace Shaqi\LlmsOptimizer;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\File;

class Plugin extends PluginOperationAbstract
{
    public static function activate(): void
    {
        $prefix = llms_optimizer_config('prefix', 'llms_optimizer_');
        $defaults = llms_optimizer_config('defaults', []);
        
        foreach ($defaults as $key => $value) {
            if (! Setting::has($prefix . $key)) {
                Setting::set($prefix . $key, $value);
            }
        }
        
        Setting::save();
    }

    public static function deactivate(): void
    {
        $publicPath = public_path('llms.txt');
        
        if (File::exists($publicPath)) {
            File::delete($publicPath);
        }
    }

    public static function remove(): void
    {
        $prefix = llms_optimizer_config('prefix', 'llms_optimizer_');
        
        $settingsToRemove = [
            $prefix . 'enabled',
            $prefix . 'enable_pages',
            $prefix . 'enable_blog_posts',
            $prefix . 'enable_products',
            $prefix . 'include_site_tagline',
            $prefix . 'include_plugin_header',
            $prefix . 'include_sitemap_reference',
            $prefix . 'output_mode',
            $prefix . 'sorting_order',
            $prefix . 'max_items_per_type',
            $prefix . 'link_format',
            $prefix . 'include_token_count',
            $prefix . 'cache_duration',
        ];
        
        Setting::delete($settingsToRemove);
        
        $publicPath = public_path('llms.txt');
        
        if (File::exists($publicPath)) {
            File::delete($publicPath);
        }
    }
}

