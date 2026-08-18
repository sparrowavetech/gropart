<?php

namespace SparroWave\AdvancedCod\Hooks;

use Botble\Ecommerce\Events\ProductVariationCreated;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;

class ProductHsnHookListener
{
    public static function renderHsnField(?string $html, ?Product $product = null): ?string
    {
        $value = old('hsn_code', $product?->hsn_code);

        return $html . view('plugins/advanced-cod::product-hsn-field', compact('value'))->render();
    }

    public static function saveProductHsnCode(string $screen, Request $request, $model): void
    {
        if (! $model instanceof Product || ! $request->has('hsn_code')) {
            return;
        }

        $model->forceFill([
            'hsn_code' => self::normalizeHsnCode($request->input('hsn_code')),
        ])->saveQuietly();
    }

    public static function saveVariationHsnCode(ProductVariationCreated $event): void
    {
        $request = request();

        if (! $request->has('hsn_code')) {
            return;
        }

        $event->product->forceFill([
            'hsn_code' => self::normalizeHsnCode($request->input('hsn_code')),
        ])->saveQuietly();
    }

    private static function normalizeHsnCode(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
