<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Ashikul\IndiaSmsGateway\Models\SmsTemplate;

class TemplateRenderer
{
    public function render(string $key, array $variables = [], ?string $fallback = null): string
    {
        $template = SmsTemplate::query()->where('key', $key)->where('is_active', true)->first();
        $content = $template?->content ?? $fallback ?? '';

        $variables = array_merge(['site_name' => setting('site_title', config('app.name'))], $variables);

        foreach ($variables as $name => $value) {
            $content = str_replace(['{{ ' . $name . ' }}', '{{' . $name . '}}'], (string) $value, $content);
        }

        // Indian DLT portals commonly provide approved message text using
        // sequential {#VAR#} placeholders instead of named variables.
        if (str_contains($content, '{#VAR#}')) {
            $ordered = [];
            foreach (['customer_name', 'code', 'expires_in', 'phone', 'site_name', 'order_id', 'amount', 'status'] as $key) {
                if (array_key_exists($key, $variables)) {
                    $ordered[] = (string) $variables[$key];
                }
            }
            foreach ($ordered as $value) {
                $content = preg_replace('/\{#VAR#\}/', $value, $content, 1);
            }
        }

        return trim($content);
    }
}
