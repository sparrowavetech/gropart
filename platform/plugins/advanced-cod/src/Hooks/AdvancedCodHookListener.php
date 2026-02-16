<?php

namespace SparroWave\AdvancedCod\Hooks;

use Botble\Base\Facades\MetaBox;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextField;

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
            'low'
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

        $label = '<div class="product-cod-label mt-2 mb-2"><span class="badge bg-success" style="background-color: #28a745 !important; color: white; padding: 5px 10px; border-radius: 4px;"><i class="ti ti-truck"></i> COD Available</span></div>';
        
        return $label . $content;
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
