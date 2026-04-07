<?php

use Shaqi\LlmsOptimizer\Facades\LlmsOptimizerHelper;
use Illuminate\Support\Str;
use Botble\Slug\Facades\SlugHelper;

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

if (! function_exists('llms_optimizer_legacy_model_setting_key')) {
    function llms_optimizer_legacy_model_setting_key(string $modelClass): string
    {
        return 'enable_' . Str::snake(Str::plural(class_basename($modelClass)));
    }
}

if (! function_exists('llms_optimizer_model_namespace_hint')) {
    function llms_optimizer_model_namespace_hint(string $modelClass): string
    {
        $parts = explode('\\', trim($modelClass, '\\'));
        $modelsIndex = array_search('Models', $parts, true);

        if ($modelsIndex !== false && $modelsIndex > 0) {
            $hint = $parts[$modelsIndex - 1];
        } elseif (count($parts) > 1) {
            $hint = $parts[count($parts) - 2];
        } else {
            $hint = $parts[0] ?? 'model';
        }

        $hint = Str::snake($hint);

        return $hint !== '' ? $hint : 'model';
    }
}

if (! function_exists('llms_optimizer_model_setting_keys')) {
    /**
     * @param  array<string, mixed>  $supportedModels
     * @return array<string, string>
     */
    function llms_optimizer_model_setting_keys(array $supportedModels): array
    {
        $groups = [];
        $modelClasses = array_keys($supportedModels);
        sort($modelClasses, SORT_STRING);

        foreach ($modelClasses as $modelClass) {
            $legacyKey = llms_optimizer_legacy_model_setting_key($modelClass);
            $groups[$legacyKey][] = $modelClass;
        }

        ksort($groups, SORT_STRING);

        $resolvedKeys = [];
        $usedKeys = [];

        foreach ($groups as $legacyKey => $classes) {
            sort($classes, SORT_STRING);

            if (count($classes) === 1) {
                $resolvedKeys[$classes[0]] = $legacyKey;
                $usedKeys[$legacyKey] = true;

                continue;
            }

            foreach ($classes as $modelClass) {
                $candidateKey = $legacyKey . '_' . llms_optimizer_model_namespace_hint($modelClass);

                if (isset($usedKeys[$candidateKey])) {
                    $candidateKey .= '_' . substr(md5($modelClass), 0, 6);
                }

                while (isset($usedKeys[$candidateKey])) {
                    $candidateKey .= 'x';
                }

                $resolvedKeys[$modelClass] = $candidateKey;
                $usedKeys[$candidateKey] = true;
            }
        }

        return $resolvedKeys;
    }
}

if (! function_exists('llms_optimizer_model_setting_key')) {
    /**
     * @param  array<string, mixed>|null  $supportedModels
     */
    function llms_optimizer_model_setting_key(string $modelClass, ?array $supportedModels = null): string
    {
        $supportedModels ??= SlugHelper::supportedModels();

        return llms_optimizer_model_setting_keys($supportedModels)[$modelClass]
            ?? llms_optimizer_legacy_model_setting_key($modelClass);
    }
}
