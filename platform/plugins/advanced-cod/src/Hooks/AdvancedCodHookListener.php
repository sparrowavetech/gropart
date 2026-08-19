<?php

namespace SparroWave\AdvancedCod\Hooks;

use Botble\Base\Facades\MetaBox;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;

class AdvancedCodHookListener
{
    public static function addProductMetaBox($priority, $model): void
    {
        if (! $model instanceof Product) {
            return;
        }

        if (! is_plugin_active('payment') || get_payment_setting('status', 'cod') == 0) {
            return;
        }

        MetaBox::addMetaBox(
            'advanced_cod_meta_box',
            'COD Eligibility',
            function () use ($model) {
                return view('plugins/advanced-cod::product-cod-config', compact('model'))->render();
            },
            get_class($model),
            'advanced',
            'high'
        );
    }

    public static function saveProductCodEligibility(string $screen, Request $request, $model): void
    {
        if (! $model instanceof Product) {
            return;
        }

        $model->is_cod_eligible = $request->input('is_cod_eligible', 0);
        $model->save();
    }

    public static function addCodLabelToProductPage(?string $content, Product $product): ?string
    {
        if (! $product->is_cod_eligible) {
            return $content;
        }

        $label = '<div class="product-cod-label mt-2 mb-2"><span class="badge bg-success" style="background-color: #28a745 !important; color: white; padding: 5px 10px; font-size: 11px; border-radius: 4px; font-weight: 500;"><i class="ti ti-truck"></i> ' . __('COD Available') . '</span></div>';
        
        return $label . $content;
    }

    public static function addCodLabelToProductCard(?string $content, mixed $product = null): ?string
    {
        if (! $product || ! ($product->is_cod_eligible ?? false)) {
            return $content;
        }

        $label = '<span class="badge bg-success" style="background-color: #28a745 !important; color: white !important; padding: 2px 5px; font-size: 10px; border-radius: 4px; font-weight: 500; display: inline-flex; align-items: center; gap: 3px; vertical-align: middle;">'
            . '<i class="ti ti-truck"></i> ' . __('COD Available')
            . '</span>';

        return ($content ?? '') . $label;
    }

    public static function addCodSettings(?string $settings, string $paymentMethod): ?string
    {
        if ($paymentMethod !== \Botble\Payment\Enums\PaymentMethodEnum::COD) {
            return $settings;
        }

        $percentage = get_payment_setting('prepayment_percentage', $paymentMethod, 30);

        return $settings . view('plugins/advanced-cod::settings', compact('percentage'))->render();
    }
}
