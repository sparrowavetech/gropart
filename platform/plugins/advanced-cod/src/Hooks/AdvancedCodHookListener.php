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

        $truckSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 3px;"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>';
        $label = '<div class="product-cod-label mt-2 mb-2"><span class="badge bg-success" style="background-color: #28a745 !important; color: white; padding: 5px 10px; font-size: 11px; border-radius: 4px; font-weight: 500; display: inline-flex; align-items: center;">' . $truckSvg . trans('plugins/advanced-cod::advanced-cod.cod_available') . '</span></div>';
        
        return $label . $content;
    }

    public static function addCodLabelToProductCard(?string $content, mixed $product = null): ?string
    {
        if (! $product || ! ($product->is_cod_eligible ?? false)) {
            return $content;
        }

        $truckSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 2px;"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>';
        $label = '<span class="badge bg-success" style="background-color: #28a745 !important; color: white !important; padding: 2px 5px; font-size: 10px; border-radius: 4px; font-weight: 500; display: inline-flex; align-items: center; vertical-align: middle;">'
            . $truckSvg . trans('plugins/advanced-cod::advanced-cod.cod_available')
            . '</span>';

        return ($content ?? '') . $label;
    }

    public static function addCodSettings(?string $settings, string $paymentMethod): ?string
    {
        if ($paymentMethod !== \Botble\Payment\Enums\PaymentMethodEnum::COD) {
            return $settings;
        }

        $percentage = \SparroWave\AdvancedCod\Supports\CodAmounts::getPrepaymentPercentage();
        $selectedGateway = (string) get_payment_setting('advance_gateway', $paymentMethod, 'auto');

        $allGateways = array_values(\Botble\Payment\Enums\PaymentMethodEnum::toArray());
        $onlineGateways = [];
        foreach ($allGateways as $gateway) {
            if (!in_array($gateway, ['cod', 'bank_transfer', 'auto'], true) && get_payment_setting('status', $gateway) == 1) {
                $onlineGateways[$gateway] = (string) get_payment_setting('name', $gateway, ucfirst($gateway));
            }
        }

        return $settings . view('plugins/advanced-cod::settings', compact('percentage', 'selectedGateway', 'onlineGateways'))->render();
    }
}
