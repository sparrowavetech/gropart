<?php

namespace Botble\Shortcode;

use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ShortcodeField
{
    public function tabs(array $fields, array $attributes = [], int $max = 20): string
    {
        $current = (int) Arr::get($attributes, 'quantity') ?: 6;

        $selector = 'quantity_' . Str::random(20);

        $choices = range(1, $max);
        $choices = array_combine($choices, $choices);

        return view('packages/shortcode::fields.tabs', compact('fields', 'attributes', 'current', 'selector', 'choices', 'max'))->render();
    }

    public function getTabsData(array $fields, ShortcodeCompiler $shortcode): array
    {
        $quantity = min((int) $shortcode->quantity, 20);

        if (empty($shortcode->toArray()) || empty($fields) || ! $quantity) {
            return [];
        }

        $tabs = [];

        for ($i = 1; $i <= $quantity; $i++) {
            $tab = [];
            foreach ($fields as $field) {
                $tab[$field] = $shortcode->{$field . '_' . $i};
            }

            $tabs[] = $tab;
        }

        return $tabs;
    }
}
