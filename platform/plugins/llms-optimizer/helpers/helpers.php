<?php

use Shaqi\LlmsOptimizer\Facades\LlmsOptimizerHelper;

if (! function_exists('get_llms_optimizer_setting')) {
    function get_llms_optimizer_setting(string $key, bool|int|string|null $default = ''): array|int|string|null
    {
        return setting(LlmsOptimizerHelper::getSettingPrefix() . $key, $default);
    }
}

if (! function_exists('llms_optimizer_path')) {
    function llms_optimizer_path(string $path = ''): string
    {
        return plugin_path('llms-optimizer' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (! function_exists('llms_optimizer_config')) {
    function llms_optimizer_config(string $key, mixed $default = null): mixed
    {
        return config('plugins.llms-optimizer.general.' . $key, $default);
    }
}

if (! function_exists('llms_optimizer_is_enabled')) {
    function llms_optimizer_is_enabled(): bool
    {
        return (bool) get_llms_optimizer_setting('enabled', llms_optimizer_config('defaults.enabled', true));
    }
}

