<?php

namespace ArchiElite\IpBlocker;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function removed(): void
    {
        Schema::dropIfExists('ip_blocker_logs');

        Setting::delete([
            'ip_blocker_addresses',
            'ip_blocker_addresses_range',
            'ip_blocker_available_countries',
            'ip_blocker_secret_key',
            'ip_blocker_rate_limits_at',
        ]);
    }
}
