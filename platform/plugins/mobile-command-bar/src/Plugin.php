<?php

namespace Botble\MobileCommandBar;

use Botble\MobileCommandBar\Supports\MobileCommandBarHelper;
use Throwable;

/**
 * Botble's PluginService calls a lifecycle method on this class right
 * after activation/deactivation/removal via call_user_func(), with NO
 * method_exists() guard - so it must exist or the request throws a
 * TypeError and the whole action fails with a generic "Server Error".
 *
 * Confirmed from production logs that the exact method name Botble looks
 * for is NOT consistent across core versions:
 *   - some versions call Plugin::activated() / deactivated() / removed()
 *     (past tense)
 *   - other versions call Plugin::activate() / deactivate() / remove()
 *     (present tense)
 *
 * Rather than guessing which one a given install uses, every variant is
 * implemented here so the plugin activates cleanly regardless of which
 * Botble core version a particular host is running.
 */
class Plugin
{
    public static function activate(): void
    {
        self::onActivate();
    }

    public static function activated(): void
    {
        self::onActivate();
    }

    public static function deactivate(): void
    {
        self::onDeactivate();
    }

    public static function deactivated(): void
    {
        self::onDeactivate();
    }

    public static function remove(): void
    {
        self::onRemove();
    }

    public static function removed(): void
    {
        self::onRemove();
    }

    protected static function onActivate(): void
    {
        // Settings already fall back to safe defaults via
        // MobileCommandBarHelper::settings(), so nothing needs to be
        // seeded here.
    }

    protected static function onDeactivate(): void
    {
    }

    protected static function onRemove(): void
    {
        try {
            if (! class_exists(\Botble\Setting\Facades\Setting::class)) {
                return;
            }

            foreach (array_keys(MobileCommandBarHelper::defaults()) as $key) {
                \Botble\Setting\Facades\Setting::delete(MobileCommandBarHelper::SETTING_PREFIX . $key);
            }

            \Botble\Setting\Facades\Setting::save();
        } catch (Throwable) {
        }
    }
}
