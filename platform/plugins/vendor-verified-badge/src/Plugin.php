<?php

namespace SparroWave\VendorVerifiedBadge;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use RuntimeException;

class Plugin extends PluginOperationAbstract
{
    public static function activate(): void
    {
        if (! is_plugin_active('ecommerce')) {
            throw new RuntimeException('The "Ecommerce" plugin is required to be installed and activated before enabling Vendor Verified & Type Badge.');
        }

        if (! is_plugin_active('marketplace')) {
            throw new RuntimeException('The "Marketplace" plugin is required to be installed and activated before enabling Vendor Verified & Type Badge.');
        }
    }

    public static function remove(): void
    {
        Setting::delete([
            'vendor_verified_badge_icon',
            'vendor_verified_application_url',
            'vendor_verified_menu_gating_enabled',
            'vendor_verified_completion_threshold',
            'vendor_verified_tooltip_text',
        ]);
    }
}
