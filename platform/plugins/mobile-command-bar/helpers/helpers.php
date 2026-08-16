<?php

use Botble\MobileCommandBar\Supports\MobileCommandBarHelper;

if (! function_exists('mcb_defaults')) {
    /**
     * Default values for every setting the plugin exposes.
     * Kept as a single source of truth used by the settings form,
     * the sanitizer and the "reset to defaults" action.
     */
    function mcb_defaults(): array
    {
        return MobileCommandBarHelper::defaults();
    }
}

if (! function_exists('mcb_settings')) {
    /**
     * Current settings merged with defaults (never returns a missing key).
     */
    function mcb_settings(): array
    {
        return MobileCommandBarHelper::settings();
    }
}

if (! function_exists('mcb_setting')) {
    /**
     * Read a single setting value.
     */
    function mcb_setting(string $key, mixed $default = null): mixed
    {
        $settings = mcb_settings();

        return $settings[$key] ?? $default;
    }
}

if (! function_exists('mcb_asset')) {
    /**
     * URL for one of this plugin's own CSS/JS/image files. Resolves
     * through the plugin's own streaming route so the admin screen and
     * front-end bar keep working even when copying files into the public
     * webroot fails (restrictive hosting permissions, read-only
     * deployments, etc.). Falls back to a plain public-path URL only if
     * the route itself cannot be resolved for some reason.
     */
    function mcb_asset(string $path): string
    {
        $path = ltrim($path, '/');

        try {
            if (app('router')->has('mobile-command-bar.assets')) {
                return route('mobile-command-bar.assets', ['path' => $path]);
            }
        } catch (\Throwable) {
        }

        return asset('vendor/core/plugins/mobile-command-bar/' . $path);
    }
}
